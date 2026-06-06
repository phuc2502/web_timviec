<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MessageController extends Controller
{
    // ─── Danh sách hội thoại ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $userId = Auth::id();

        if ($request->has('to')) {
            $toId = (int)$request->query('to');
            $me   = Auth::user();

            $employerId = $me->user_type === 'employer' ? $me->id : $toId;
            $employeeId = $me->user_type === 'employee' ? $me->id : $toId;

            $conversation = Conversation::where('employer_id', $employerId)
                ->where('employee_id', $employeeId)
                ->whereNull('listing_id')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'employer_id' => $employerId,
                    'employee_id' => $employeeId,
                    'listing_id'  => null,
                ]);
            }

            return redirect()->route('messages.show', $conversation->id);
        }

        $conversations = Conversation::forUser($userId)
            ->with(['employer', 'employee', 'listing', 'latestMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('messages.index', compact('conversations'));
    }

    // ─── Chi tiết hội thoại ───────────────────────────────────────────────
    public function show($id)
    {
        $userId       = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền truy cập cuộc hội thoại này.');
        }

        $conversations = Conversation::forUser($userId)
            ->with(['employer', 'employee', 'listing', 'latestMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', compact('conversation', 'conversations', 'messages'));
    }

    // ─── Gửi tin nhắn (có kiểm duyệt 2 lớp) ─────────────────────────────
    public function store(Request $request, $id)
    {
        $userId       = Auth::id();
        $conversation = Conversation::with('listing')->findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền gửi tin nhắn trong cuộc hội thoại này.');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $jobTitle    = $conversation->listing->title ?? 'tuyển dụng';
        $messageBody = $validated['body'];

        // ── Kiểm duyệt 2 lớp ─────────────────────────────────────────────
        $isAllowed = $this->checkMessage($messageBody, $jobTitle);

        if (!$isAllowed) {
            return redirect()->back()
                ->with('error', '⚠️ Tin nhắn không phù hợp với nội dung tuyển dụng. Vui lòng chỉ trao đổi về vị trí "' . $jobTitle . '".')
                ->withInput();
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $userId,
            'body'            => $messageBody,
        ]);

        $conversation->touch();

        return redirect()->back();
    }

    // ─── Kiểm duyệt 2 lớp ────────────────────────────────────────────────
   private function checkMessage(string $message, string $jobTitle): bool
{
    $apiKey = env('GEMINI_API_KEY');
    if (!$apiKey) {
        return true;
    }

    // Cache theo nội dung — tránh gọi API trùng lặp
    $cacheKey = 'msg_mod_' . md5(mb_strtolower(trim($message)));
    $cached   = Cache::get($cacheKey);
    if ($cached !== null) {
        return $cached === 'ALLOWED';
    }

    try {
        $prompt = "Bạn là AI kiểm duyệt tin nhắn cho website tuyển dụng IT Việt Nam.\n"
            . "Vị trí tuyển dụng: {$jobTitle}\n"
            . "Tin nhắn người dùng gửi: \"{$message}\"\n\n"
            . "Quy tắc:\n"
            . "- ALLOWED: tin nhắn liên quan đến tuyển dụng, công việc, lương, phỏng vấn, kỹ năng, hồ sơ, chào hỏi lịch sự\n"
            . "- BLOCKED: mọi nội dung KHÔNG liên quan đến tuyển dụng, kể cả câu hỏi vô nghĩa, bạo lực, tiền bạc cá nhân, tình cảm, spam\n"
            . "- Khi nghi ngờ → BLOCKED\n\n"
            . "Chỉ trả lời đúng 1 từ: ALLOWED hoặc BLOCKED";

        $response = Http::timeout(8)->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-8b:generateContent?key=' . $apiKey,
            [
                'contents'         => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 5],
            ]
        );

        if ($response->successful()) {
            $result = strtoupper(trim($response->json('candidates.0.content.parts.0.text') ?? ''));
            $result = str_contains($result, 'BLOCKED') ? 'BLOCKED' : 'ALLOWED';
            Cache::put($cacheKey, $result, 3600);
            Log::info("Gemini: [{$message}] => [{$result}]");
            return $result === 'ALLOWED';
        }

        Log::warning('Gemini error: ' . $response->status());
        return true;

    } catch (\Throwable $e) {
        Log::error('Gemini exception: ' . $e->getMessage());
        return true;
    }
}
    // ─── Lớp 2: Gemini AI với cache ──────────────────────────────────────
    private function checkWithGemini(string $message, string $jobTitle): bool
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return true;
        }

        // Cache theo nội dung tin nhắn — cùng nội dung không gọi API lại
        $cacheKey = 'msg_mod_' . md5(mb_strtolower(trim($message)));
        $cached   = Cache::get($cacheKey);
        if ($cached !== null) {
            Log::info("Gemini cached: [{$message}] => [{$cached}]");
            return $cached === 'ALLOWED';
        }

        try {
            $prompt = <<<PROMPT
Bạn là hệ thống kiểm duyệt cho website tuyển dụng IT Việt Nam.
Vị trí tuyển dụng: {$jobTitle}
Tin nhắn: "{$message}"

Chỉ ALLOWED nếu tin nhắn liên quan rõ ràng đến: công việc, lương, phỏng vấn, kỹ năng, hồ sơ, quy trình tuyển dụng, hoặc chào hỏi lịch sự.
BLOCKED nếu: không liên quan tuyển dụng, bạo lực, lừa đảo, nhạy cảm, vô nghĩa.
Khi không chắc chắn → BLOCKED.

Trả lời CHỈ 1 từ: ALLOWED hoặc BLOCKED
PROMPT;

            $response = Http::timeout(8)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey,
                [
                    'contents'          => [['parts' => [['text' => $prompt]]]],
                    'generationConfig'  => ['temperature' => 0.1, 'maxOutputTokens' => 5],
                ]
            );

            if ($response->successful()) {
                $result = strtoupper(trim($response->json('candidates.0.content.parts.0.text') ?? 'ALLOWED'));
                $result = str_contains($result, 'BLOCKED') ? 'BLOCKED' : 'ALLOWED';

                // Cache kết quả 1 tiếng
                Cache::put($cacheKey, $result, 3600);
                Log::info("Gemini: [{$message}] => [{$result}]");

                return $result === 'ALLOWED';
            }

            // API lỗi (rate limit...) → dùng lớp 1 đã lọc rồi, cho phép gửi
            Log::warning('Gemini API error: ' . $response->status() . ' - ' . $response->body());
            return true;

        } catch (\Throwable $e) {
            Log::error('Gemini exception: ' . $e->getMessage());
            return true;
        }
    }

    // ─── Tạo hoặc tìm hội thoại ──────────────────────────────────────────
    public function findOrCreate(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|integer',
        ]);

        $user      = Auth::user();
        $listingId = $request->input('listing_id');
        $listing   = Listing::findOrFail($listingId);

        if ($user->isCandidate()) {
            $employeeId = $user->id;
            $employerId = $listing->user_id;
        } else {
            $request->validate(['employee_id' => 'required|integer']);
            $employerId = $user->id;
            $employeeId = $request->input('employee_id');
        }

        $conversation = Conversation::firstOrCreate([
            'listing_id'  => $listing->id,
            'employer_id' => $employerId,
            'employee_id' => $employeeId,
        ]);

        return redirect()->route('messages.show', $conversation->id);
    }

    // ─── Poll tin nhắn mới ────────────────────────────────────────────────
    public function poll(Request $request, $id)
    {
        $userId       = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403);
        }

        $afterId  = (int)$request->query('after', 0);
        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($msg) => [
                'id'         => $msg->id,
                'body'       => $msg->body,
                'sender_id'  => $msg->sender_id,
                'created_at' => $msg->created_at->format('H:i · d/m/Y'),
            ]),
        ]);
    }
}