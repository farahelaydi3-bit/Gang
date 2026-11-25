<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Services\AiCourtService;
use App\Services\GeminiAssistantService;
use App\Services\GangGeminiRequestException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AiAssistantController extends Controller
{
    public function index(Request $request)
    {
        $current = null;
        $conversations = collect();
        $messages = [];

        if ($request->user()) {
            $conversations = AiConversation::query()->where('user_id', $request->user()->id)
                ->latest('updated_at')->limit(30)->get();
            if ($request->filled('conversation')) {
                $current = AiConversation::query()
                    ->where('user_id', $request->user()->id)
                    ->findOrFail((int) $request->query('conversation'));
                $messages = $current->messages()->latest('id')->limit(100)
                    ->get(['role', 'content'])->reverse()->values()->toArray();
            }
        } else {
            $messages = array_values((array) $request->session()->get('gang_ai_guest_messages', []));
        }

        return view('pages.assistant', compact('messages', 'conversations', 'current'));
    }

    private function prepare(Request $request, GeminiAssistantService $gemini, AiCourtService $courts): array
    {
        $validated=$request->validate([
            'message'=>['required','string','min:1','max:1500'],
            'conversation_id'=>['nullable','integer','min:1'],
        ]);
        $text=trim($validated['message']);
        if ($text==='') throw ValidationException::withMessages(['message'=>__('Enter a message first.')]);
        $conversation=null;
        if ($request->user() && !empty($validated['conversation_id'])) {
            $conversation=AiConversation::query()->where('user_id',$request->user()->id)->findOrFail($validated['conversation_id']);
        } elseif (!$request->user() && !empty($validated['conversation_id'])) abort(403);
        $history=$conversation
            ? $conversation->messages()->latest('id')->limit(10)->get(['role','content'])->reverse()->values()->toArray()
            : ($request->user()?[]:array_slice((array)$request->session()->get('gang_ai_guest_messages',[]),-10));
        $plan = $gemini->plan($text, $history);
        $facts=isset($plan['filters']) ? $this->buildReply($plan['filters'],$request,$courts)
            : ['reply'=>'','courts'=>[]];
        return compact('text','conversation','history','plan','facts');
    }

    private function saveExchange(Request $request, string $text, string $reply, array $history, ?AiConversation &$conversation): array
    {
        if ($request->user()) {
            DB::transaction(function () use ($request,$text,$reply,&$conversation): void {
                if (!$conversation) $conversation=AiConversation::create([
                    'user_id'=>$request->user()->id,'title'=>Str::limit($text,90,''),
                ]);
                $conversation->messages()->createMany([
                    ['role'=>'user','content'=>$text],['role'=>'assistant','content'=>$reply],
                ]);
                $conversation->touch();
            });
        } else {
            $messages=array_merge($history,[
                ['role'=>'user','content'=>$text],['role'=>'assistant','content'=>$reply],
            ]);
            $request->session()->put('gang_ai_guest_messages',array_slice($messages,-20));
        }
        return [
            'conversation_id'=>$conversation?->id,
            'conversation_url'=>$conversation ? route('courtbook.assistant',['conversation'=>$conversation->id]):null,
            'conversation_title'=>$conversation?->title,
        ];
    }

    public function send(Request $request, GeminiAssistantService $gemini, AiCourtService $courts): JsonResponse
    {
        try {
            $ctx=$this->prepare($request,$gemini,$courts);
            $reply=isset($ctx['plan']['direct']) ? $ctx['plan']['direct'] : $gemini->answer($ctx['plan'],$ctx['facts']);
            $conversation=$ctx['conversation'];
            $meta=$this->saveExchange($request,$ctx['text'],$reply,$ctx['history'],$conversation);
            return response()->json(array_merge($ctx['facts'],['reply'=>$reply],$meta));
        } catch (\Illuminate\Validation\ValidationException $e) { throw $e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) { throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message'=>$this->aiErrorMessage($e)],503);
        }
    }

    public function sendStream(Request $request, GeminiAssistantService $gemini, AiCourtService $courts): StreamedResponse
    {
        // Resolve tools before streaming; Laravel's session must be writable before headers are sent.
        try { $ctx=$this->prepare($request,$gemini,$courts); }
        catch (\Illuminate\Validation\ValidationException $e) { throw $e; }
        catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) { throw $e; }
        catch (Throwable $e) {
            report($e);
            $message = $this->aiErrorMessage($e);
            return response()->stream(static function () use ($message): void {
                echo json_encode(['type'=>'error','message'=>$message],JSON_UNESCAPED_UNICODE)."\n";
                flush();
            },503,['Content-Type'=>'application/x-ndjson; charset=utf-8', 'Cache-Control'=>'no-store']);
        }
        // Streaming the model itself; not splitting a pre-generated response.
        return response()->stream(function () use ($request,$gemini,$ctx): void {
            $emit=static function(array $event): void {
                echo json_encode($event,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE)."\n";
                if (ob_get_level()>0) @ob_flush();
                flush();
            };
            $facts=$ctx['facts'];
            // Show verified court cards early.
            $initialFacts = $facts;
            $emit(['type'=>'meta','data'=>$initialFacts]);
            $reply='';
            try {
                if (isset($ctx['plan']['direct'])) {
                    $reply=$ctx['plan']['direct'];
                    $emit(['type'=>'delta','text'=>$reply]);
                } else {
                    $reply=$gemini->streamAnswer($ctx['plan'],$facts,static function(string $delta) use ($emit): void {
                        $emit(['type'=>'delta','text'=>$delta]);
                    });
                }
                $conversation=$ctx['conversation'];
                $saved=$this->saveExchange($request,$ctx['text'],$reply,$ctx['history'],$conversation);
                if (!$request->user()) $request->session()->save(); // Streamed callbacks run after session middleware.
                $emit(['type'=>'meta','data'=>$saved]);
                $emit(['type'=>'done']);
            } catch (Throwable $e) {
                report($e);
                $request->session()->save();
                $emit(['type'=>'error','message'=>$this->aiErrorMessage($e)]);
            }
        },200,[
            'Content-Type'=>'application/x-ndjson; charset=utf-8',
            'Cache-Control'=>'no-cache, no-transform',
            'X-Accel-Buffering'=>'no',
        ]);
    }

    private function aiErrorMessage(Throwable $e): string
    {
        if ($e instanceof GangGeminiRequestException) {
            return match ($e->httpStatus) {
                429 => __('Gemini has reached its usage limit. Try again later.'),
                503, 502, 504 => __('Gemini is temporarily busy. Try again shortly.'),
                401, 403 => __('Gemini could not authenticate. Check the API configuration.'),
                default => $e->httpStatus === 0
                    ? __('The Gemini connection timed out. Try again shortly.')
                    : __('Could not get a response from Gemini. Try again shortly.'),
            };
        }
        return __('Could not complete the Gemini response. Try again.');
    }

    private function buildReply(array $filters, Request $request, AiCourtService $courts): array
    {
        $intent = $filters['intent'];
        $date = $this->validDate($filters['date'] ?? null);
        $start = $this->validTime($filters['start_time'] ?? null);
        $end = $this->validTime($filters['end_time'] ?? null);
        $minutes = $filters['duration_minutes'] ?? null;
        if ($start && !$end && is_int($minutes) && $minutes >= 60 && $minutes <= 480 && $minutes % 30 === 0) {
            $end = Carbon::parse("2000-01-01 {$start}")->addMinutes($minutes)->format('H:i');
        }
        $matches = $courts->matches($filters);
        $maxTotal = isset($filters['max_total_price']) ? (float) $filters['max_total_price'] : null;
        $maxHourly = isset($filters['max_hourly_price']) ? (float) $filters['max_hourly_price'] : null;
        $durationForBudget = is_int($minutes) && $minutes >= 60 ? $minutes : null;
        $cards = [];

        foreach ($matches as $court) {
            if (count($cards) >= 6) break;
            $card = [
                'id' => $court->id,
                'name' => $court->name,
                'sport' => $court->sports?->name ? __($court->sports->name) : null,
                'address' => $court->address,
                'weekday_price' => number_format((float) $court->weekday_price, 2, '.', ''),
                'weekend_price' => number_format((float) $court->weekend_price, 2, '.', ''),
                'evening_price' => number_format((float) $court->evening_price, 2, '.', ''),
                'opening_time' => substr((string) $court->opening_time, 0, 5),
                'closing_time' => substr((string) $court->closing_time, 0, 5),
                'url' => route('courtbook.court', $court->id),
                                'availability' => null,
            ];
            if ($date && $start) {
                $checkEnd = $end ?: Carbon::parse("2000-01-01 {$start}")
                    ->addMinutes(max(60, (int) (ceil($court->min_booking_minutes / 30) * 30)))
                    ->format('H:i');
                $preview = $courts->preview($court, $date, $start, $checkEnd);
                $card['availability'] = $preview['available'] ? 'available' : 'unavailable';
                if ($preview['available']) {
                    $card['total_price'] = $preview['total_price'];
                    $card['deposit_amount'] = $preview['deposit_amount'];
                }
                if ($preview['available'] && $maxHourly !== null
                    && (float) $preview['total_price'] * 60 / $preview['duration_minutes'] > $maxHourly) continue;
                if ($preview['available'] && $maxTotal !== null
                    && (float) $preview['total_price'] > $maxTotal) continue;
                if (!$preview['available'] && ($maxHourly !== null || $maxTotal !== null)) continue;
            } elseif (!$date && ($maxHourly !== null || $maxTotal !== null)) {
                $lowest = min((float) $court->weekday_price, (float) $court->weekend_price, (float) $court->evening_price);
                if ($maxHourly !== null && $lowest > $maxHourly) continue;
                if ($maxTotal !== null && $durationForBudget !== null && $lowest * $durationForBudget / 60 > $maxTotal) continue;
                $card['estimated_minimum_hourly_price'] = $lowest;
            } elseif ($date && in_array($intent, ['availability'], true) && count($cards) < 4) {
                $card['sample_slots'] = $courts->firstOpenHours($court, $date, 3);
            }
            $cards[] = $card;
        }

        if ($intent === 'general' && empty($filters['sport']) && empty($filters['court_id'])
            && empty($filters['court_name']) && empty($filters['area']) && empty($filters['date'])
            && empty($filters['max_hourly_price']) && empty($filters['max_total_price'])) {
            return ['reply' => $this->generalReply($filters), 'courts' => []];
        }

        if ($matches->isEmpty() || $cards === []) {
            return ['reply' => __('No approved courts match those details right now. Try changing the area, sport, or budget.'), 'courts' => []];
        }

        $reply = $intent === 'recommend'
            ? __('These Gang courts match your request. See prices below. Give me the date, time, and duration to check availability and the total price.')
            : __('Here are the approved courts matching your request. Prices are in Jordanian dinars per hour and vary by day and time.');
        if ($maxTotal !== null) {
            $reply .= "\n" . __('Your total budget is :amount JOD.', ['amount' => $maxTotal]) . ' ' . ($date && $start && $end
                ? __('I compared it with the calculated price for the requested time.')
                : __('Prices are hourly. Give me the date, time, and duration to check the total.'));
        } elseif ($maxHourly !== null) {
            $reply .= "\n" . __('I used the budget as the hourly price limit.');
        }
        if ($date && $start) {
            $reply .= "\n" . ($end
                ? __('Availability for :date from :start to :end is subject to change.', compact('date', 'start', 'end'))
                : __('Availability for :date from :start for the shortest allowed duration is subject to change.', compact('date', 'start')));
        } elseif ($date && $intent === 'availability') {
            $reply .= "\n" . __('I included some times that currently look available for :date.', compact('date'));
        }
        if (count($cards) > 1) {
            $reply .= "\n" . __('Choose a court by name or number for more details.');
        }

        if ($intent === 'booking') {
            $reply .= "\n" . __('Open the court page from its card to complete your booking.');
        }
        return ['reply' => $reply, 'courts' => $cards];
    }

    private function generalReply(array $filters): string
    {
        $reply = trim((string) ($filters['conversational_reply'] ?? ''));
        // Gemini may phrase general answers, but Laravel remains the authority for
        // all court data, booking state, pricing and availability.
        if ($reply !== '' && !preg_match('/(?:api[_ -]?key|password|كلمة السر|تم (?:تأكيد|إنشاء) الحجز)/iu', $reply)) {
            return $reply;
        }

        return __('Welcome to Gang! I can help you find a court, compare prices, check availability, or get to the court page to book. What sport or area are you looking for?');
    }

    private function validDate(?string $date): ?string
    {
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }
        try {
            $actual = Carbon::createFromFormat('!Y-m-d', $date);
            return $actual && $actual->format('Y-m-d') === $date && $actual->endOfDay()->gte(now()) ? $date : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function validTime(?string $time): ?string
    {
        return $time && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time) ? $time : null;
    }

    public function new(Request $request): RedirectResponse
    {
        $request->session()->forget(['gang_ai_guest_messages']);
        return redirect()->route('courtbook.assistant');
    }

    public function rename(Request $request, AiConversation $conversation): RedirectResponse
    {
        abort_unless($request->user() && $conversation->user_id === $request->user()->id, 403);
        $validated = $request->validate(['title' => ['required', 'string', 'max:100']]);
        $conversation->update(['title' => trim($validated['title'])]);
        return redirect()->route('courtbook.assistant', ['conversation' => $conversation->id]);
    }

    public function destroy(Request $request, AiConversation $conversation): RedirectResponse
    {
        abort_unless($request->user() && $conversation->user_id === $request->user()->id, 403);
        $conversation->delete();
        return redirect()->route('courtbook.assistant');
    }
}
