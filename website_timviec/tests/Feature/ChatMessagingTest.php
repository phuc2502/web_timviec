<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatMessagingTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $employer;
    private Listing $listing;

    protected function setUp(): void
    {
        parent::setUp();

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
}
