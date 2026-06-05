<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display a listing of conversations.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Support direct message start via query param (e.g. GET /messages?to=userId)
        if ($request->has('to')) {
            $toId = (int)$request->query('to');
            $me = Auth::user();
            
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
                    'listing_id' => null,
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

    /**
     * Display the specified conversation.
     */
    public function show($id)
    {
        $userId = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền truy cập cuộc hội thoại này.');
        }

        // Fetch conversations for sidebar
        $conversations = Conversation::forUser($userId)
            ->with(['employer', 'employee', 'listing', 'latestMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Fetch messages for this conversation
        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        // Mark unread messages sent by the other party as read
        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', compact('conversation', 'conversations', 'messages'));
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request, $id)
    {
        $userId = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền gửi tin nhắn trong cuộc hội thoại này.');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $validated['body'],
        ]);

        $conversation->touch();

        return redirect()->back();
    }

    /**
     * Find or create a conversation based on listing and user.
     */
    public function findOrCreate(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $listingId = $request->input('listing_id');
        $listing = Listing::findOrFail($listingId);

        if ($user->isCandidate()) {
            $employeeId = $user->id;
            $employerId = $listing->user_id;
        } else {
            $request->validate([
                'employee_id' => 'required|integer',
            ]);
            $employerId = $user->id;
            $employeeId = $request->input('employee_id');
        }

        $conversation = Conversation::firstOrCreate([
            'listing_id' => $listing->id,
            'employer_id' => $employerId,
            'employee_id' => $employeeId,
        ]);

        return redirect()->route('messages.show', $conversation->id);
    }

    /**
     * Poll for new messages.
     */
    public function poll(Request $request, $id)
    {
        $userId = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->employer_id !== $userId && $conversation->employee_id !== $userId) {
            abort(403, 'Bạn không có quyền truy cập cuộc hội thoại này.');
        }

        $afterId = (int)$request->query('after', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($msg) => [
                'id' => $msg->id,
                'body' => $msg->body,
                'sender_id' => $msg->sender_id,
                'created_at' => $msg->created_at->format('H:i · d/m/Y'),
            ]),
        ]);
    }
}
