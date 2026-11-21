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
            abort(403, 'حساب مالك المنشأة موقوف.');
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

        if ($applicationId !== null) {
            $admin->applications()->findOrFail($applicationId);
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
            'required' => 'يرجى تعبئة حقل :attribute.',
            'string' => 'يجب أن تكون قيمة حقل :attribute نصًا.',
            'array' => 'يرجى اختيار :attribute من الخيارات المتاحة.',
            'integer' => 'يجب أن يكون حقل :attribute عددًا صحيحًا.',
            'max' => 'يجب ألّا يزيد طول حقل :attribute عن :max حرفًا.',
            'courts_count.min' => 'يجب أن يكون عدد الملاعب واحدًا على الأقل.',
            'courts_count.max' => 'يجب ألّا يزيد عدد الملاعب عن :max.',
            'terms_accepted.accepted' => 'يجب الموافقة على شروط وأحكام ملاك الملاعب قبل إرسال الطلب.',
            'terms_version.*' => 'يرجى تحديث الصفحة ومراجعة النسخة الحالية من الشروط.',
            'sports.required' => 'اختر رياضة واحدة على الأقل.',
            'sports.min' => 'اختر رياضة واحدة على الأقل.',
            'sports.*.distinct' => 'لا يمكن اختيار الرياضة نفسها أكثر من مرة.',
            'sports.*.exists' => 'إحدى الرياضات المختارة غير متاحة.',
            'attachments.max' => 'يمكنك إرفاق :max ملفات كحد أقصى.',
            'attachments.*.file' => 'يجب أن يكون كل مرفق ملفًا صالحًا.',
            'attachments.*.uploaded' => 'تعذّر رفع المرفق. تحقّق من حجمه وحاول مرة أخرى.',
            'attachments.*.mimes' => 'اختر صورة بصيغة JPG أو PNG أو WEBP، أو مستند PDF.',
            'attachments.*.max' => 'يجب ألّا يزيد حجم المرفق عن ٥ ميغابايت.',
        ], [
            'facility_name' => 'اسم المنشأة',
            'address' => 'عنوان المنشأة',
            'courts_count' => 'عدد الملاعب',
            'facility_description' => 'وصف المنشأة',
            'application_message' => 'رسالة إلى فريق جانغ',
            'sports' => 'الرياضات',
            'sports.*' => 'الرياضة',
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
    }
}
