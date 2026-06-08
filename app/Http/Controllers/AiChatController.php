<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{

    /**
     * GET /ai-chat — Danh sách cuộc trò chuyện.
     */
    public function index()
    {
        $conversations = auth()->user()->aiConversations()
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('ai-chat.index', compact('conversations'));
    }

    /**
     * POST /ai-chat/new — Tạo cuộc trò chuyện mới.
     */
    public function create()
    {
        $conversation = AiConversation::create([
            'user_id' => auth()->id(),
            'title' => 'Cuộc trò chuyện mới',
            'messages' => [],
        ]);

        return redirect()->route('ai-chat.show', $conversation->id);
    }

    /**
     * GET /ai-chat/{id} — Chi tiết cuộc trò chuyện.
     */
    public function show($id)
    {
        $conversation = AiConversation::findOrFail($id);

        if ($conversation->user_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền truy cập cuộc trò chuyện này.');
        }

        $conversations = auth()->user()->aiConversations()
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('ai-chat.show', compact('conversation', 'conversations'));
    }

    /**
     * POST /ai-chat/{id}/send — Gửi tin nhắn và nhận phản hồi từ AI.
     */
    public function send(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $conversation = AiConversation::findOrFail($id);

        if ($conversation->user_id !== auth()->id()) {
            abort(403);
        }

        $message = trim($request->input('message'));
        $messages = $conversation->messages ?? [];

        // 1. Thêm tin nhắn của user
        $messages[] = [
            'role' => 'user',
            'content' => $message,
            'created_at' => now()->toDateTimeString(),
        ];

        // 2. Chuẩn bị prompt hệ thống dựa trên loại user
        $userTypeStr = auth()->user()->user_type === 'employer' ? 'Nhà tuyển dụng' : 'Ứng viên';
        $systemPrompt = "Bạn là trợ lý tuyển dụng IT chuyên nghiệp của website ITWorks Việt Nam. "
            . "Vai trò người dùng: " . $userTypeStr . " (" . auth()->user()->user_type . "). "
            . "Chỉ trả lời câu hỏi liên quan đến: tuyển dụng IT, việc làm, kỹ năng lập trình, "
            . "CV, phỏng vấn, lương thưởng, lộ trình học IT, đánh giá ứng viên. "
            . "Nếu người dùng gửi tin nhắn hoặc câu hỏi không liên quan đến tuyển dụng IT hoặc ngoài các chủ đề trên, "
            . "bạn bắt buộc phải từ chối và phản hồi chính xác câu sau: 'Tôi là trợ lý tuyển dụng IT của ITWorks. Bạn không nên hỏi những tin nhắn không liên quan đến nghiệp vụ.' "
            . "Trả lời bằng tiếng Việt, ngắn gọn, thực tế.";

        // Chuyển đổi lịch sử sang định dạng Gemini API
        $history = collect($messages)->map(fn($msg) => [
            'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $msg['content']]],
        ])->toArray();

        $aiResponse = 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại.';

        // 3. Gọi Gemini API
        try {
            $apiKey = env('GEMINI_API_KEY');
            if (empty($apiKey)) {
                Log::error('Gemini API key is not configured in .env');
                $aiResponse = 'Xin lỗi, dịch vụ AI chưa được cấu hình. Vui lòng quay lại sau.';
            } else {
                $response = Http::timeout(15)->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey,
                    [
                        'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                        'contents'           => $history,
                        'generationConfig'   => ['temperature' => 0.7, 'maxOutputTokens' => 2048],
                    ]
                );

                if ($response->successful()) {
                    $aiResponse = $response->json('candidates.0.content.parts.0.text')
                        ?? 'Xin lỗi, tôi không thể trả lời lúc này. Vui lòng thử lại.';
                } else {
                    Log::error('Gemini API request failed: ' . $response->status() . ' - ' . $response->body());
                    $aiResponse = 'Xin lỗi, có lỗi xảy ra từ máy chủ AI. Vui lòng thử lại.';
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini API connection error: ' . $e->getMessage());
            $aiResponse = 'Xin lỗi, kết nối tới dịch vụ AI bị gián đoạn. Vui lòng thử lại.';
        }

        // 4. Thêm tin nhắn của AI vào lịch sử
        $messages[] = [
            'role' => 'assistant',
            'content' => $aiResponse,
            'created_at' => now()->toDateTimeString(),
        ];

        // 5. Cập nhật tiêu đề tự động bằng AI nếu cuộc hội thoại vẫn giữ tiêu đề mặc định
        if ($conversation->title === 'Cuộc trò chuyện mới') {
            $suggestedTitle = null;
            try {
                $apiKey = env('GEMINI_API_KEY');
                if (!empty($apiKey)) {
                    $titlePrompt = "Dựa vào tin nhắn sau của người dùng, hãy tạo một tiêu đề cực ngắn (từ 3 đến 6 từ) tóm tắt chủ đề cuộc trò chuyện bằng tiếng Việt. Không để trong dấu ngoặc kép, không dùng từ thừa như 'Tiêu đề:', chỉ trả về duy nhất tiêu đề đó. Tin nhắn: \"" . $message . "\"";
                    
                    $titleResponse = Http::timeout(10)->post(
                        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey,
                        [
                            'contents' => [['parts' => [['text' => $titlePrompt]]]],
                            'generationConfig' => ['temperature' => 0.5, 'maxOutputTokens' => 100],
                        ]
                    );
                    
                    if ($titleResponse->successful()) {
                        $suggestedTitle = trim($titleResponse->json('candidates.0.content.parts.0.text') ?? '');
                        $suggestedTitle = trim($suggestedTitle, '"\'“”‘’ ');
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate AI conversation title: ' . $e->getMessage());
            }

            if (empty($suggestedTitle)) {
                $words = explode(' ', $message);
                $suggestedTitle = implode(' ', array_slice($words, 0, 8));
                if (count($words) > 8) {
                    $suggestedTitle .= '...';
                }
            }
            
            $conversation->title = $suggestedTitle;
        }

        $conversation->messages = $messages;
        $conversation->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'user_message' => [
                    'content' => $message,
                    'created_at' => now()->format('H:i, d/m/Y'),
                ],
                'ai_message' => [
                    'content' => $aiResponse,
                    'content_markdown' => \Illuminate\Support\Str::markdown($aiResponse),
                    'created_at' => now()->format('H:i, d/m/Y'),
                ],
                'conversation_title' => $conversation->title,
            ]);
        }

        return redirect()->back();
    }

    /**
     * DELETE /ai-chat/{id} — Xóa cuộc trò chuyện.
     */
    public function destroy($id)
    {
        $conversation = AiConversation::findOrFail($id);

        if ($conversation->user_id !== auth()->id()) {
            abort(403);
        }

        $conversation->delete();

        return redirect()->route('ai-chat.index')->with('success', 'Đã xóa cuộc trò chuyện.');
    }
}
