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

            if ($conversation) {
                // Khôi phục nếu từng bị xóa/ẩn
                if ($conversation->employer_id === $me->id) {
                    $conversation->employer_deleted_at = null;
                } else {
                    $conversation->employee_deleted_at = null;
                }
                $conversation->save();
            } else {
                $conversation = Conversation::create([
                    'employer_id' => $employerId,
                    'employee_id' => $employeeId,
                    'listing_id'  => null,
                ]);
            }

            return redirect()->route('messages.show', $conversation->id);
        }

        $conversations = Conversation::forUserActive($userId)
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

        // Khôi phục hội thoại cho người dùng khi họ xem trực tiếp
        if ($conversation->employer_id === $userId) {
            if ($conversation->employer_deleted_at !== null) {
                $conversation->employer_deleted_at = null;
                $conversation->save();
            }
        } else {
            if ($conversation->employee_deleted_at !== null) {
                $conversation->employee_deleted_at = null;
                $conversation->save();
            }
        }

        $conversations = Conversation::forUserActive($userId)
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

        // Khôi phục cuộc trò chuyện cho cả hai bên khi có tin nhắn mới
        $conversation->employer_deleted_at = null;
        $conversation->employee_deleted_at = null;
        $conversation->save();

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
       $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
       if (!$apiKey) {
           return true;
       }

       // Cache theo nội dung — sử dụng v3 để bypass cache cũ
       $cacheKey = 'msg_mod_v3_' . md5(mb_strtolower(trim($message)));
       $cached   = Cache::get($cacheKey);
       if ($cached !== null) {
           return $cached === 'ALLOWED';
       }

       try {
           $prompt = "Bạn là AI kiểm duyệt tin nhắn cho website tuyển dụng IT Việt Nam.\n"
               . "Vị trí tuyển dụng: {$jobTitle}\n"
               . "Tin nhắn người dùng gửi: \"{$message}\"\n\n"
               . "QUY TẮC PHÂN LOẠI:\n"
               . "1. ALLOWED (HỢP LỆ): Chỉ cho phép các tin nhắn liên quan trực tiếp đến tuyển dụng IT, bao gồm:\n"
               . "   - Chào hỏi lịch sự, xã giao phù hợp ngữ cảnh đi làm/phỏng vấn (ví dụ: Chào anh/chị, Cảm ơn bạn...)\n"
               . "   - Hỏi/trả lời về vị trí công việc, nhiệm vụ, dự án, công nghệ sử dụng, cấu trúc phòng ban\n"
               . "   - Hỏi/trao đổi về mức lương, thưởng, chế độ bảo hiểm, thời gian làm việc, chính sách công ty\n"
               . "   - Trao đổi về hồ sơ ứng tuyển, CV, kinh nghiệm, kỹ năng của ứng viên\n"
               . "   - Hẹn lịch phỏng vấn, liên hệ trao đổi công việc, gửi bài test chuyên môn\n\n"
               . "2. BLOCKED (KHÔNG HỢP LỆ): Chặn tất cả các trường hợp còn lại, bao gồm:\n"
               . "   - Các từ ngữ ngắn, cộc lốc, không có ngữ cảnh công việc rõ ràng (ví dụ: \"đi tù\", \"đói quá\", \"ăn cơm chưa\", \"đi chơi\", \"ừ\", \"ok\" đứng một mình mà không kèm nội dung công việc)\n"
               . "   - Hỏi thô tục, hỏi thăm đời tư cá nhân, tán tỉnh, làm quen tình cảm (ví dụ: Bạn có người yêu chưa?, Tối nay đi chơi không?)\n"
               . "   - Rao bán hàng hóa, quảng cáo dịch vụ không liên quan, spam link\n"
               . "   - Từ ngữ tiêu cực, phạm pháp, tù tội, bạo lực, nhạy cảm, chửi thề, thô tục\n"
               . "   - Nội dung vay mượn tiền bạc, đời sống riêng tư khác\n\n"
               . "LƯU Ý QUAN TRỌNG:\n"
               . "- Khi nghi ngờ hoặc không chắc chắn tin nhắn có liên quan đến công việc IT/tuyển dụng hay không -> Hãy chọn BLOCKED.\n"
               . "- Một tin nhắn cụt lủn hoặc chứa nội dung nhạy cảm như \"đi tù\" hoàn toàn không liên quan đến công việc tuyển dụng và PHẢI BỊ BLOCKED.\n\n"
               . "Chỉ trả lời duy nhất 1 từ: ALLOWED hoặc BLOCKED (không viết thêm gì khác).";

           $response = Http::timeout(8)->post(
               'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey,
               [
                   'contents'         => [['parts' => [['text' => $prompt]]]],
                   'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 5],
               ]
           );

           if ($response->successful()) {
               $result = strtoupper(trim($response->json('candidates.0.content.parts.0.text') ?? ''));
               $result = str_contains($result, 'BLOCKED') ? 'BLOCKED' : 'ALLOWED';
               Cache::put($cacheKey, $result, 3600);
               Log::info("Gemini 2.0: [{$message}] => [{$result}]");
               return $result === 'ALLOWED';
           }

           Log::warning('Gemini API error: ' . $response->status());
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

        $conversation = Conversation::where('listing_id', $listing->id)
            ->where('employer_id', $employerId)
            ->where('employee_id', $employeeId)
            ->first();

        if ($conversation) {
            // Khôi phục hội thoại nếu từng bị xóa
            if ($conversation->employer_id === $user->id) {
                $conversation->employer_deleted_at = null;
            } else {
                $conversation->employee_deleted_at = null;
            }
            $conversation->save();
        } else {
            $conversation = Conversation::create([
                'listing_id'  => $listing->id,
                'employer_id' => $employerId,
                'employee_id' => $employeeId,
            ]);
        }

        return redirect()->route('messages.show', $conversation->id);
    }

    // ─── Xóa/Ẩn cuộc hội thoại ──────────────────────────────────────────
    public function destroy($id)
    {
        $userId = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        if ($conversation->employer_id === $userId) {
            $conversation->employer_deleted_at = now();
        } else {
            $conversation->employee_deleted_at = now();
        }
        $conversation->save();

        return redirect()->route('messages.index')->with('success', 'Đã xóa đoạn chat thành công.');
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