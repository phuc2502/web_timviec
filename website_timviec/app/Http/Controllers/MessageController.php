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

        $tab = $request->query('tab', 'active');

        if ($tab === 'archive') {
            $conversations = Conversation::forUser($userId)
                ->where(function ($q) use ($userId) {
                    $q->where(function ($sq) use ($userId) {
                        $sq->where('employer_id', $userId)->whereNotNull('employer_deleted_at');
                    })->orWhere(function ($sq) use ($userId) {
                        $sq->where('employee_id', $userId)->whereNotNull('employee_deleted_at');
                    });
                })
                ->with(['employer', 'employee', 'listing', 'latestMessage'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $conversations = Conversation::forUserActive($userId)
                ->with(['employer', 'employee', 'listing', 'latestMessage'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return view('messages.index', compact('conversations', 'tab'));
    }

    // ─── Chi tiết hội thoại ───────────────────────────────────────────────
    public function show($id)
    {
        $userId       = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền truy cập cuộc hội thoại này.');
        }

        $tab = request()->query('tab', 'active');

        // Khôi phục hội thoại cho người dùng khi họ xem trực tiếp (ngoại trừ khi họ đang xem ở mục Đã ẩn)
        if ($tab !== 'archive') {
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
        }

        if ($tab === 'archive') {
            $conversations = Conversation::forUser($userId)
                ->where(function ($q) use ($userId) {
                    $q->where(function ($sq) use ($userId) {
                        $sq->where('employer_id', $userId)->whereNotNull('employer_deleted_at');
                    })->orWhere(function ($sq) use ($userId) {
                        $sq->where('employee_id', $userId)->whereNotNull('employee_deleted_at');
                    });
                })
                ->with(['employer', 'employee', 'listing', 'latestMessage'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $conversations = Conversation::forUserActive($userId)
                ->with(['employer', 'employee', 'listing', 'latestMessage'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', compact('conversation', 'conversations', 'messages', 'tab'));
    }

    // ─── Gửi tin nhắn (có kiểm duyệt 2 lớp và đính kèm file) ───────────
    public function store(Request $request, $id)
    {
        $userId       = Auth::id();
        $conversation = Conversation::with('listing')->findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền gửi tin nhắn trong cuộc hội thoại này.');
        }

        $validated = $request->validate([
            'body' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'is_interview' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'location'     => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $messageBody = $validated['body'] ?? '';
        $attachmentPath = null;
        $attachmentName = null;

        if (empty($messageBody) && !$request->hasFile('file') && !$request->boolean('is_interview')) {
            return redirect()->back()->with('error', '⚠️ Vui lòng nhập nội dung tin nhắn hoặc đính kèm tệp.');
        }

        // Xử lý đính kèm file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('attachments', 'public');
            if (empty($messageBody)) {
                $messageBody = '📎 Đã gửi tệp đính kèm: ' . $attachmentName;
            }
        }

        // Xử lý tạo lịch phỏng vấn
        $isInterview = $request->boolean('is_interview');
        if ($isInterview) {
            $request->validate([
                'scheduled_at' => 'required|date|after:now',
                'location'     => 'required|string|max:255',
                'notes'        => 'nullable|string|max:1000',
            ], [
                'scheduled_at.required' => 'Vui lòng chọn thời gian phỏng vấn.',
                'scheduled_at.after' => 'Thời gian phỏng vấn không được nhỏ hơn thời gian hiện tại.',
            ]);
            $scheduledAt = \Carbon\Carbon::parse($request->input('scheduled_at'));
            $messageBody = '📅 Thư mời phỏng vấn: Lịch hẹn vào lúc ' . $scheduledAt->format('H:i d/m/Y') . ' tại ' . $request->input('location');
        }

        // ── Kiểm duyệt 2 lớp ─────────────────────────────────────────────
        // Chỉ chạy AI moderation nếu gửi tin nhắn văn bản thuần túy (không đính kèm file, không phải thư mời phỏng vấn)
        if (!$request->hasFile('file') && !$isInterview && !empty($validated['body'])) {
            $jobTitle   = $conversation->listing->title ?? 'tuyển dụng';
            $moderation = $this->checkMessage($messageBody, $jobTitle);

            if ($moderation['status'] === 'BLOCKED') {
                return redirect()->back()
                    ->with('error', '⚠️ Tin nhắn không phù hợp với nội dung tuyển dụng. Vui lòng chỉ trao đổi về vị trí "' . $jobTitle . '".')
                    ->withInput();
            }

            if ($moderation['status'] === 'ERROR') {
                $errorMsg = '⚠️ Hệ thống kiểm duyệt đang bận hoặc gặp sự cố. Vui lòng thử lại sau giây lát.';
                if (isset($moderation['code'])) {
                    if ($moderation['code'] === 429) {
                        $errorMsg = '⚠️ Bạn đang gửi tin nhắn quá nhanh. Hệ thống kiểm duyệt cần vài giây để xử lý, vui lòng gửi lại sau.';
                    } elseif ($moderation['code'] === 'QUOTA_EXCEEDED') {
                        $errorMsg = '⚠️ API Key của hệ thống đã hết hạn mức sử dụng (Quota Exceeded). Vui lòng cấu hình lại hoặc liên hệ quản trị viên.';
                    }
                }
                return redirect()->back()
                    ->with('error', $errorMsg)
                    ->withInput();
            }
        }

        // Khôi phục cuộc trò chuyện cho cả hai bên khi có tin nhắn mới
        $conversation->employer_deleted_at = null;
        $conversation->employee_deleted_at = null;
        $conversation->save();

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $userId,
            'body'            => $messageBody,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        // Nếu là lời mời phỏng vấn, tạo bản ghi tương ứng
        if ($isInterview) {
            \App\Models\InterviewInvitation::create([
                'message_id'   => $message->id,
                'scheduled_at' => $request->input('scheduled_at'),
                'location'     => $request->input('location'),
                'notes'        => $request->input('notes'),
                'status'       => 'pending',
            ]);
        }

        $conversation->touch();

        return redirect()->back();
    }

    // ─── Kiểm duyệt 2 lớp ────────────────────────────────────────────────
   private function checkMessage(string $message, string $jobTitle): array
   {
       // 1. Kiểm tra danh sách đen cục bộ để chặn phản hồi ngay lập tức (tiết kiệm API Quota và giảm trễ)
       if ($this->checkLocalBlacklist($message)) {
           return ['status' => 'BLOCKED'];
       }

       $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
       if (!$apiKey) {
           return ['status' => 'ALLOWED'];
       }

       // Cache theo nội dung — sử dụng v5 để bypass cache cũ và lưu cấu trúc trạng thái mới
       $cacheKey = 'msg_mod_v5_' . md5(mb_strtolower(trim($message)));
       $cached   = Cache::get($cacheKey);
       if ($cached !== null) {
           return ['status' => $cached];
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
               . "   - Hỏi thô tục, hỏi thăm đời tư cá nhân, tán tỉnh, làm quen tình cảm (ví dụ: Bạn có người yêu chưa?, Tối nay đi chơi không?, tại sao em không nhớ anh)\n"
               . "   - Rao bán hàng hóa, quảng cáo dịch vụ không liên quan, spam link\n"
               . "   - Từ ngữ tiêu cực, phạm pháp, tù tội, bạo lực, nhạy cảm, chửi thề, thô tục\n"
               . "   - Nội dung vay mượn tiền bạc, đời sống riêng tư khác, hoặc các bài hát trẻ em, ca từ không liên quan (ví dụ: múa bài bé lên bốn, ăn cơm chauw)\n\n"
               . "LƯU Ý QUAN TRỌNG:\n"
               . "- Khi nghi ngờ hoặc không chắc chắn tin nhắn có liên quan đến công việc IT/tuyển dụng hay không -> Hãy chọn BLOCKED.\n"
               . "- Một tin nhắn cụt lủn hoặc chứa nội dung nhạy cảm như \"đi tù\" hoàn toàn không liên quan đến công việc tuyển dụng và PHẢI BỊ BLOCKED.\n\n"
               . "Chỉ trả lời duy nhất 1 từ: ALLOWED hoặc BLOCKED (không viết thêm gì khác).";

           $response = Http::retry(3, 1000, function ($exception, $request) {
               if ($exception instanceof \Illuminate\Http\Client\RequestException && $exception->response) {
                   $body = $exception->response->body();
                   if (str_contains(strtolower($body), 'quota')) {
                       return false; // Không thử lại nếu hết quota (tránh đơ UI)
                   }
               }
               return true;
           })->timeout(8)->post(
               'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey,
               [
                   'contents'         => [['parts' => [['text' => $prompt]]]],
                   'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 1024],
               ]
           );

           if ($response->successful()) {
               $result = strtoupper(trim($response->json('candidates.0.content.parts.0.text') ?? ''));
               $result = str_contains($result, 'BLOCKED') ? 'BLOCKED' : 'ALLOWED';
               Cache::put($cacheKey, $result, 3600);
               Log::info("Gemini 2.0: [{$message}] => [{$result}]");
               return ['status' => $result];
           }

           $body = $response->body();
           $code = $response->status();
           if (str_contains(strtolower($body), 'quota')) {
               $code = 'QUOTA_EXCEEDED';
           }
           Log::warning('Gemini API error: ' . $response->status() . ' - ' . $body);
           return ['status' => 'ERROR', 'code' => $code];

        } catch (\Throwable $e) {
            Log::error('Gemini exception: ' . $e->getMessage());
            $code = 500;
            if ($e instanceof \Illuminate\Http\Client\RequestException && $e->response) {
                $body = $e->response->body();
                if (str_contains(strtolower($body), 'quota')) {
                    $code = 'QUOTA_EXCEEDED';
                } else {
                    $code = $e->response->status();
                }
            }
            return ['status' => 'ERROR', 'code' => $code];
        }
   }

   private function checkLocalBlacklist(string $message): bool
   {
       $messageLower = mb_strtolower(trim($message));

       $blacklistPatterns = [
           '/đi tù/u',
           '/ăn cơm/u',
           '/ăn thịt/u',
           '/đói quá/u',
           '/đi chơi/u',
           '/uống thuốc chuột/u',
           '/địt mẹ/u',
           '/chịch/u',
           '/tán tỉnh/u',
           '/có người yêu chưa/u',
           '/bé lên bốn/u',
           '/nhớ anh/u',
           '/nhớ em/u',
           '/tom va jerry/u',
           '/ăn dép/u',
       ];

       foreach ($blacklistPatterns as $pattern) {
           if (preg_match($pattern, $messageLower)) {
               return true;
           }
       }

       // Cũng chặn các từ cộc lốc quá ngắn không có ngữ cảnh rõ ràng
       if ($messageLower === 'ừ' || $messageLower === 'uh' || $messageLower === 'aye') {
           return true;
       }

       return false;
   }

    // ─── Phản hồi lịch phỏng vấn ──────────────────────────────────────────
    public function respondToInterview(Request $request, $id)
    {
        $userId = Auth::id();
        $invitation = \App\Models\InterviewInvitation::with('message.conversation')->findOrFail($id);
        $conversation = $invitation->message->conversation;

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $invitation->status = $validated['status'];
        $invitation->save();

        $statusText = $validated['status'] === 'accepted' ? 'ĐỒNG Ý' : 'TỪ CHỐI';
        $senderName = Auth::user()->name;
        
        $messageBody = "📢 {$senderName} đã {$statusText} thư mời phỏng vấn vào lúc " . $invitation->scheduled_at->format('H:i d/m/Y');
        
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $userId,
            'body'            => $messageBody,
        ]);

        $conversation->touch();

        return response()->json([
            'success' => true,
            'status' => $invitation->status,
        ]);
    }

    // ─── Danh sách trả lời nhanh ──────────────────────────────────────────
    public function getQuickReplies()
    {
        $user = Auth::user();
        $replies = \App\Models\QuickReply::where('user_id', $user->id)->get();

        if ($replies->isEmpty()) {
            $defaults = [];
            if ($user->isEmployer()) {
                $defaults = [
                    ['title' => 'Chào ứng viên', 'body' => 'Chào bạn, chúng tôi đã nhận được hồ sơ ứng tuyển của bạn. Chúng tôi thấy hồ sơ rất tiềm năng và muốn trao đổi thêm.'],
                    ['title' => 'Hẹn phỏng vấn', 'body' => 'Chào bạn, công ty muốn mời bạn tham gia một buổi phỏng vấn trực tuyến để trao đổi chi tiết hơn về công việc.'],
                    ['title' => 'Hỏi thời gian', 'body' => 'Bạn có thể tham gia phỏng vấn vào thời gian nào trong tuần này tiện nhất?'],
                ];
            } else {
                $defaults = [
                    ['title' => 'Chào nhà tuyển dụng', 'body' => 'Xin chào quý công ty, em là ứng viên đang quan tâm đến vị trí tuyển dụng này ạ. Em gửi CV để anh/chị xem qua.'],
                    ['title' => 'Xác nhận lịch phỏng vấn', 'body' => 'Em xin xác nhận lịch phỏng vấn này ạ. Hẹn gặp anh/chị vào buổi phỏng vấn.'],
                    ['title' => 'Cảm ơn', 'body' => 'Em xin cảm ơn công ty đã phản hồi hồ sơ của em. Rất mong có cơ hội đồng hành cùng quý công ty.'],
                ];
            }

            foreach ($defaults as $d) {
                \App\Models\QuickReply::create([
                    'user_id' => $user->id,
                    'title'   => $d['title'],
                    'body'    => $d['body'],
                ]);
            }

            $replies = \App\Models\QuickReply::where('user_id', $user->id)->get();
        }

        return response()->json([
            'success' => true,
            'replies' => $replies,
        ]);
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

        $readIds = $conversation->messages()
            ->where('sender_id', $userId)
            ->whereNotNull('read_at')
            ->pluck('id');

        return response()->json([
            'messages' => $messages->map(fn($msg) => [
                'id'         => $msg->id,
                'body'       => $msg->body,
                'sender_id'  => $msg->sender_id,
                'created_at' => $msg->created_at->format('H:i · d/m/Y'),
            ]),
            'read_ids' => $readIds,
        ]);
    }

    // ─── Khôi phục cuộc hội thoại đã ẩn/xóa ────────────────────────────────
    public function restore($id)
    {
        $userId       = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        if ($conversation->employer_id === $userId) {
            $conversation->employer_deleted_at = null;
        } else {
            $conversation->employee_deleted_at = null;
        }
        $conversation->save();

        return redirect()->route('messages.show', $conversation->id)->with('success', 'Đã khôi phục cuộc trò chuyện.');
    }
}