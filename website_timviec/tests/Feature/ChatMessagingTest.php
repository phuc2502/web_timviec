<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatMessagingTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $employer;
    private Listing $listing;
    private $geminiResponse = null;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => 'dummy-api-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) {
                if ($this->geminiResponse !== null) {
                    return $this->geminiResponse;
                }
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'ALLOWED']
                                ]
                            ]
                        ]
                    ]
                ], 200);
            }
        ]);

        $this->employee = User::factory()->create([
            'user_type' => 'employee',
        ]);

        $this->employer = User::factory()->create([
            'user_type' => 'employer',
        ]);

        $this->listing = Listing::create([
            'user_id' => $this->employer->id,
            'title' => 'Senior PHP Engineer',
            'slug' => 'senior-php-engineer-' . Str::random(5),
            'application_close_date' => now()->addDays(30),
        ]);
    }

    /** @test */
    public function candidate_can_start_conversation_and_is_redirected()
    {
        $response = $this->actingAs($this->employee)
            ->post(route('messages.start'), [
                'listing_id' => $this->listing->id,
            ]);

        $conversation = Conversation::first();
        $this->assertNotNull($conversation);
        $this->assertEquals($this->listing->id, $conversation->listing_id);
        $this->assertEquals($this->employer->id, $conversation->employer_id);
        $this->assertEquals($this->employee->id, $conversation->employee_id);

        $response->assertRedirect(route('messages.show', $conversation->id));
    }

    /** @test */
    public function starting_conversation_multiple_times_returns_the_same_conversation()
    {
        $this->actingAs($this->employee)
            ->post(route('messages.start'), [
                'listing_id' => $this->listing->id,
            ]);

        $this->actingAs($this->employee)
            ->post(route('messages.start'), [
                'listing_id' => $this->listing->id,
            ]);

        $this->assertEquals(1, Conversation::count());
    }

    /** @test */
    public function non_participant_is_forbidden_from_viewing_conversation()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $otherUser = User::factory()->create(['user_type' => 'employee']);

        $response = $this->actingAs($otherUser)
            ->get(route('messages.show', $conversation->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function non_participant_is_forbidden_from_sending_messages()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $otherUser = User::factory()->create(['user_type' => 'employee']);

        $response = $this->actingAs($otherUser)
            ->post(route('messages.store', $conversation->id), [
                'body' => 'Hello there',
            ]);

        $response->assertStatus(403);
        $this->assertEquals(0, Message::count());
    }

    /** @test */
    public function sending_message_touches_conversation_updated_at()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        // Sleep to ensure time difference
        $originalUpdatedAt = $conversation->updated_at;
        $this->travel(10)->seconds();

        $this->actingAs($this->employee)
            ->post(route('messages.store', $conversation->id), [
                'body' => 'A valid message',
            ]);

        $conversation->refresh();
        $this->assertNotEquals($originalUpdatedAt->timestamp, $conversation->updated_at->timestamp);
    }

    /** @test */
    public function viewing_conversation_marks_other_party_messages_as_read()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->employer->id,
            'body' => 'Unread message from employer',
            'read_at' => null,
        ]);

        $this->actingAs($this->employee)
            ->get(route('messages.show', $conversation->id));

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    /** @test */
    public function message_body_validation_max_characters()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('messages.store', $conversation->id), [
                'body' => str_repeat('a', 2001),
            ]);

        $response->assertSessionHasErrors('body');
        $this->assertEquals(0, Message::count());
    }

    /** @test */
    public function listing_deletion_keeps_conversation_but_sets_listing_id_null()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $this->listing->delete();

        $conversation->refresh();
        $this->assertNull($conversation->listing_id);
    }

    /** @test */
    public function non_participant_is_forbidden_from_polling_conversation()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $otherUser = User::factory()->create(['user_type' => 'employee']);

        $response = $this->actingAs($otherUser)
            ->get(route('messages.poll', $conversation->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function participant_can_poll_new_messages()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $msg1 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->employer->id,
            'body' => 'Message 1',
        ]);

        $msg2 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->employee->id,
            'body' => 'Message 2',
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('messages.poll', ['id' => $conversation->id, 'after' => $msg1->id]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'messages')
            ->assertJsonFragment([
                'id' => $msg2->id,
                'body' => 'Message 2',
            ]);
    }

    /** @test */
    public function participant_can_delete_conversation()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        // Employee deletes conversation
        $response = $this->actingAs($this->employee)
            ->delete(route('messages.destroy', $conversation->id));

        $response->assertRedirect(route('messages.index'));
        $conversation->refresh();
        $this->assertNotNull($conversation->employee_deleted_at);
        $this->assertNull($conversation->employer_deleted_at);

        // Check it doesn't show in the active scope for Employee
        $activeConversations = Conversation::forUserActive($this->employee->id)->get();
        $this->assertFalse($activeConversations->contains($conversation));

        // But still shows for Employer
        $activeConversationsEmployer = Conversation::forUserActive($this->employer->id)->get();
        $this->assertTrue($activeConversationsEmployer->contains($conversation));
    }

    /** @test */
    public function new_message_restores_conversation_for_both_users()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
            'employee_deleted_at' => now(),
            'employer_deleted_at' => now(),
        ]);

        $this->actingAs($this->employee)
            ->post(route('messages.store', $conversation->id), [
                'body' => 'Hello there, is this position still open?',
            ]);

        $conversation->refresh();
        $this->assertNull($conversation->employee_deleted_at);
        $this->assertNull($conversation->employer_deleted_at);
    }

    /** @test */
    public function viewing_or_starting_conversation_restores_it()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
            'employee_deleted_at' => now(),
        ]);

        // Viewing directly restores it
        $this->actingAs($this->employee)
            ->get(route('messages.show', $conversation->id));

        $conversation->refresh();
        $this->assertNull($conversation->employee_deleted_at);

        // Hide it again
        $conversation->update(['employee_deleted_at' => now()]);

        // Starting it again restores it
        $this->actingAs($this->employee)
            ->post(route('messages.start'), [
                'listing_id' => $this->listing->id,
            ]);

        $conversation->refresh();
        $this->assertNull($conversation->employee_deleted_at);
    }

    /** @test */
    public function sending_invalid_message_is_blocked_and_redirects_back_with_error()
    {
        $this->geminiResponse = Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'BLOCKED']
                        ]
                    ]
                ]
            ]
        ], 200);

        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->from(route('messages.show', $conversation->id))
            ->post(route('messages.store', $conversation->id), [
                'body' => 'ăn cơm chưa',
            ]);

        $response->assertRedirect(route('messages.show', $conversation->id));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Tin nhắn không phù hợp với nội dung tuyển dụng', session('error'));
        $this->assertEquals(0, Message::count());
    }

    /** @test */
    public function sending_message_handles_gemini_api_rate_limit()
    {
        $this->geminiResponse = Http::response([], 429);

        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->from(route('messages.show', $conversation->id))
            ->post(route('messages.store', $conversation->id), [
                'body' => 'Lương bao nhiêu?',
            ]);

        $response->assertRedirect(route('messages.show', $conversation->id));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Bạn đang gửi tin nhắn quá nhanh', session('error'));
        $this->assertEquals(0, Message::count());
    }

    /** @test */
    public function sending_message_handles_gemini_api_quota_limit()
    {
        $this->geminiResponse = Http::response([
            'error' => [
                'code' => 429,
                'message' => 'You exceeded your current quota, please check your plan and billing details.'
            ]
        ], 429);

        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->from(route('messages.show', $conversation->id))
            ->post(route('messages.store', $conversation->id), [
                'body' => 'Lương bao nhiêu?',
            ]);

        $response->assertRedirect(route('messages.show', $conversation->id));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('API Key của hệ thống đã hết hạn mức sử dụng', session('error'));
        $this->assertEquals(0, Message::count());
    }

    /** @test */
    public function sending_message_with_attachment_saves_file()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->employee)
            ->post(route('messages.store', $conversation->id), [
                'file' => $file,
            ]);

        $response->assertRedirect();
        
        $message = Message::first();
        $this->assertNotNull($message);
        $this->assertNotNull($message->attachment_path);
        $this->assertEquals('cv.pdf', $message->attachment_name);
        $this->assertStringContainsString('📎 Đã gửi tệp đính kèm: cv.pdf', $message->body);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($message->attachment_path);
    }

    /** @test */
    public function sending_interview_invitation_creates_record()
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-06-07 09:00:00'));

        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->employer)
            ->post(route('messages.store', $conversation->id), [
                'is_interview' => 1,
                'scheduled_at' => '2026-06-10T14:00',
                'location' => 'Phòng họp 202, ABC Tech',
                'notes' => 'Vui lòng mang theo CV giấy.',
            ]);

        $response->assertRedirect();

        $message = Message::first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('📅 Thư mời phỏng vấn: Lịch hẹn vào lúc 14:00 10/06/2026 tại Phòng họp 202, ABC Tech', $message->body);

        $invitation = \App\Models\InterviewInvitation::first();
        $this->assertNotNull($invitation);
        $this->assertEquals($message->id, $invitation->message_id);
        $this->assertEquals('pending', $invitation->status);
        $this->assertEquals('Phòng họp 202, ABC Tech', $invitation->location);
    }

    /** @test */
    public function sending_interview_invitation_in_past_fails_validation()
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-06-07 09:00:00'));

        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->employer)
            ->post(route('messages.store', $conversation->id), [
                'is_interview' => 1,
                'scheduled_at' => '2026-06-06T14:00', // Past date
                'location' => 'Phòng họp 202, ABC Tech',
            ]);

        $response->assertSessionHasErrors('scheduled_at');
        $this->assertEquals(0, Message::count());
    }

    /** @test */
    public function responding_to_interview_invitation_updates_status_and_adds_notification()
    {
        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->employer->id,
            'body' => '📅 Thư mời phỏng vấn...',
        ]);

        $invitation = \App\Models\InterviewInvitation::create([
            'message_id' => $message->id,
            'scheduled_at' => '2026-06-10 14:00:00',
            'location' => 'Phòng họp 202',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('messages.interview.respond', $invitation->id), [
                'status' => 'accepted',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'status' => 'accepted']);

        $invitation->refresh();
        $this->assertEquals('accepted', $invitation->status);

        // Check notify message
        $notifyMessage = Message::orderBy('id', 'desc')->first();
        $this->assertNotEquals($message->id, $notifyMessage->id);
        $this->assertStringContainsString('đã ĐỒNG Ý thư mời phỏng vấn', $notifyMessage->body);
    }

    /** @test */
    public function getting_quick_replies_seeds_defaults_if_empty()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('messages.quick_replies'));

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'replies');

        $replies = \App\Models\QuickReply::where('user_id', $this->employee->id)->get();
        $this->assertEquals(3, $replies->count());
        $this->assertEquals('Chào nhà tuyển dụng', $replies->first()->title);
    }

    /** @test */
    public function update_user_activity_middleware_updates_last_seen_at()
    {
        $this->assertNull($this->employee->last_seen_at);

        $this->actingAs($this->employee)
            ->get(route('messages.index'));

        $this->employee->refresh();
        $this->assertNotNull($this->employee->last_seen_at);
    }

    /** @test */
    public function offline_notification_artisan_command_sends_email()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $conversation = Conversation::create([
            'listing_id' => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        // Employee (recipient) is offline
        $this->employee->update(['last_seen_at' => now()->subMinutes(30)]);

        // Employer sends an unread message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->employer->id,
            'body' => 'Chào ứng viên, bạn có rảnh không?',
        ]);

        // Cần dùng DB::table để cập nhật created_at vì Eloquent mặc định chặn thay đổi created_at qua fillable
        \Illuminate\Support\Facades\DB::table('messages')
            ->where('id', $message->id)
            ->update(['created_at' => now()->subMinutes(20)]);

        $this->artisan('chat:notify-offline')
            ->assertExitCode(0);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\UnreadMessagesNotification::class, function ($mail) {
            return $mail->hasTo($this->employee->email) &&
                   count($mail->unreadMessagesData) === 1 &&
                   $mail->unreadMessagesData[0]['body'] === 'Chào ứng viên, bạn có rảnh không?';
        });

        $message->refresh();
        $this->assertTrue($message->email_notified);
    }

    /** @test */
    public function participant_can_restore_deleted_conversation()
    {
        $conversation = Conversation::create([
            'listing_id'          => $this->listing->id,
            'employer_id'         => $this->employer->id,
            'employee_id'         => $this->employee->id,
            'employer_deleted_at' => now(),
        ]);

        $response = $this->actingAs($this->employer)
            ->post(route('messages.restore', $conversation->id));

        $response->assertRedirect(route('messages.show', $conversation->id));

        $conversation->refresh();
        $this->assertNull($conversation->employer_deleted_at);
    }

    /** @test */
    public function poll_returns_read_message_ids()
    {
        $conversation = Conversation::create([
            'listing_id'  => $this->listing->id,
            'employer_id' => $this->employer->id,
            'employee_id' => $this->employee->id,
        ]);

        // Employer sends a message that has already been read by the employee
        $readMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $this->employer->id,
            'body'            => 'Xin chào',
            'read_at'         => now(),
        ]);

        // Employer sends another unread message
        $unreadMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $this->employer->id,
            'body'            => 'Bạn có rảnh không?',
            'read_at'         => null,
        ]);

        $response = $this->actingAs($this->employer)
            ->get(route('messages.poll', ['id' => $conversation->id, 'after' => 0]));

        $response->assertStatus(200);
        $response->assertJsonFragment(['read_ids' => [$readMessage->id]]);
        $this->assertNotContains($unreadMessage->id, $response->json('read_ids'));
    }
}
