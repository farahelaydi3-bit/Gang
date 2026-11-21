<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\OwnerApplication;
use App\Models\Sports;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class OwnerApplicationController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        if ($admin->status === 'active') {
            return redirect()->route('admin.dashboard');
        }

        if ($admin->status === 'suspended') {
            abort(403, 'حساب المنشأة موقوف حاليًا. تواصل مع إدارة Gang للمساعدة.');
        }

        $application = $admin->applications()
            ->with(['sports', 'attachments'])
            ->orderByDesc('id')
            ->first();

        $sports = Sports::orderBy('name')->get();
        $editing = $request->boolean('edit') && $application?->status === 'submitted';

        return view('admins.pending', compact('admin', 'application', 'sports', 'editing'));
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, string $application)
    {
        return $this->save($request, $application);
    }

    private function save(Request $request, ?string $applicationId = null)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        // Allow pending owners and older rejected accounts to resubmit
        if (!in_array($admin->status, ['pending', 'rejected'])) {
            return redirect()->route('admin.pending')
                ->with('error', 'ما بتقدر تبعت طلب بهالحالة.');
        }

        // Only one application can be under review
        if ($admin->applications()->where('status', 'submitted')->exists()) {
            return redirect()->route('admin.pending')
                ->with('error', 'عندك طلب قيد المراجعة بالفعل، استنى لحد ما يوصلك الرد.');
        }

        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'courts_count' => 'required|integer|min:1|max:1000',
            'facility_description' => 'required|string|max:3000',
            'application_message' => 'required|string|max:5000',
            'terms_accepted' => 'accepted',
            'terms_version' => ['required', 'string', 'in:' . config('owner.terms_version')],

            'sports' => 'required|array|min:1',
            'sports.*' => 'required|integer|distinct|exists:sports,id',

            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ], [
            'facility_name.required' => 'اكتب اسم المنشأة.',
            'facility_name.max' => 'اسم المنشأة طويل، اختصره شوي.',
            'address.required' => 'اكتب عنوان المنشأة.',
            'address.max' => 'العنوان طويل، اختصره شوي.',
            'courts_count.required' => 'اكتب عدد الملاعب.',
            'courts_count.integer' => 'عدد الملاعب لازم يكون رقم صحيح.',
            'courts_count.min' => 'لازم يكون عندك ملعب واحد عالأقل.',
            'courts_count.max' => 'عدد الملاعب أكبر من الحد المسموح.',
            'facility_description.required' => 'اكتب وصف بسيط عن منشأتك.',
            'facility_description.max' => 'وصف المنشأة طويل، اختصره شوي.',
            'application_message.required' => 'اكتب رسالة لفريق Gang.',
            'application_message.max' => 'الرسالة طويلة، اختصرها شوي.',
            'terms_accepted.accepted' => 'لازم توافق على الشروط والأحكام عشان تبعت طلبك.',
            'terms_version.required' => 'حدّث الصفحة واقرأ الشروط من جديد.',
            'terms_version.in' => 'نسخة الشروط تغيّرت، حدّث الصفحة ووافق عليها من جديد.',
            'sports.required' => 'اختار الرياضات اللي بتوفّرها منشأتك.',
            'sports.array' => 'اختيارات الرياضات مش صحيحة.',
            'sports.min' => 'اختار رياضة وحدة عالأقل.',
            'sports.*.required' => 'اختيار الرياضة مش صحيح.',
            'sports.*.integer' => 'اختيار الرياضة مش صحيح.',
            'sports.*.distinct' => 'ما تختار نفس الرياضة أكثر من مرة.',
            'sports.*.exists' => 'واحدة من الرياضات المختارة مش متاحة، حدّث الصفحة وجرب كمان مرة.',
            'attachments.max' => 'بتقدر ترفق لحد ٥ ملفات بس.',
            'attachments.*.file' => 'واحد من المرفقات مش ملف صالح.',
            'attachments.*.mimes' => 'ارفع الملفات بصيغة JPG أو PNG أو WEBP أو PDF.',
            'attachments.*.max' => 'حجم كل ملف لازم يكون ٥ ميغابايت أو أقل.',
        ], [
            'facility_name' => 'اسم المنشأة',
            'address' => 'العنوان',
            'courts_count' => 'عدد الملاعب',
            'facility_description' => 'وصف المنشأة',
            'application_message' => 'الرسالة',
            'sports' => 'الرياضات',
            'attachments' => 'المرفقات',
        ]);

        $files = $request->file('attachments', []);
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($admin, $applicationId, $validated, $files, &$storedPaths) {
                // Review decisions also update the application row before the owner account.
                $application = $applicationId === null
                    ? new OwnerApplication(['admin_id' => $admin->id, 'status' => 'submitted', 'submitted_at' => now()])
                    : $admin->applications()->lockForUpdate()->findOrFail($applicationId);
                $admin = Admin::query()->lockForUpdate()->findOrFail($admin->id);

                if (! in_array($admin->status, ['pending', 'rejected'], true)) {
                    return redirect()->route('admin.pending')
                        ->with('error', 'لا يمكنك إرسال طلب انضمام أو تعديله بحالة حسابك الحالية.');
                }

                if ($application->status !== 'submitted') {
                    return redirect()->route('admin.pending')
                        ->with('error', 'انتهت مراجعة الطلب. لم يعد متاحًا للتعديل.');
                }

                if ($applicationId === null && $admin->applications()->where('status', 'submitted')->exists()) {
                    return redirect()->route('admin.pending')
                        ->with('error', 'لديك طلب انضمام قيد المراجعة بالفعل.');
                }

                if ($applicationId !== null && $files !== [] && $application->attachments()->count() + count($files) > 5) {
                    throw ValidationException::withMessages([
                        'attachments' => 'يجب ألّا يتجاوز مجموع المرفقات الحالية والجديدة ٥ ملفات.',
                    ]);
                }

                $application->fill(Arr::only($validated, [
                    'facility_name', 'address', 'courts_count', 'facility_description', 'application_message',
                ]))->fill([
                    'terms_accepted_at' => now(),
                    'terms_version' => config('owner.terms_version'),
                ])->save();
                $application->sports()->sync($validated['sports']);
                $admin->update(['status' => 'pending']);

                foreach ($files as $file) {
                    $path = $file->store('owner-applications/' . $application->id, 'local');
                    if ($path === false) {
                        throw new RuntimeException('Could not store owner application attachment.');
                    }
                    $storedPaths[] = $path;

                    $application->attachments()->create([
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }

                return redirect()->route('admin.pending')->with('success', $applicationId === null
                    ? 'تم إرسال طلب انضمام منشأتك للمراجعة.'
                    : 'تم حفظ تعديلاتك. طلبك ما زال قيد المراجعة.');
            });
        } catch (Throwable $error) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }
            throw $error;
        }

        return redirect()->route('admin.pending')
            ->with('success', 'وصلنا طلبك! فريق Gang رح يراجعه وبنرجعلك بالرد.');
    }
}
