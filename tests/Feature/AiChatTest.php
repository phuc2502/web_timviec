<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $employer;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::factory()->create([
            'user_type' => 'employee',
        ]);

        $this->employer = User::factory()->create([
            'user_type' => 'employer',
        ]);

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        // Mock the Gemini API call dynamically
        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) {
                $bodyData = json_decode($request->body(), true);
                $promptText = $bodyData['contents'][0]['parts'][0]['text'] ?? '';
                if (str_contains($promptText, 'Dựa vào tin nhắn sau của người dùng')) {
                    return Http::response([
                        'candidates' => [
                            [
                                'content' => [
                                    'parts' => [
                                        ['text' => 'Viết CV IT đẹp']
                                    ]
                                ]
                            ]
                        ]
                    ], 200);
                }
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Đây là phản hồi giả lập từ trợ lý tuyển dụng AI.']
                                ]
                            ]
                        ]
                    ]
                ], 200);
            }
        ]);
    }

    /** @test */
    public function guests_cannot_access_ai_chat_routes()
    {
        $this->get(route('ai-chat.index'))->assertRedirect(route('login'));
        $this->post(route('ai-chat.create'))->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_index_and_create_conversation()
    {
        $response = $this->actingAs($this->employee)->get(route('ai-chat.index'));
        $response->assertStatus(200);

        $createResponse = $this->actingAs($this->employee)->post(route('ai-chat.create'));
        
        $conversation = AiConversation::first();
        $this->assertNotNull($conversation);
        $this->assertEquals($this->employee->id, $conversation->user_id);
        $this->assertEquals('Cuộc trò chuyện mới', $conversation->title);
        $this->assertEmpty($conversation->messages);

        $createResponse->assertRedirect(route('ai-chat.show', $conversation->id));
    }

    /** @test */
    public function user_can_view_their_own_conversation_but_not_others()
    {
        $conversation = AiConversation::create([
            'user_id' => $this->employee->id,
            'title' => 'Cuộc trò chuyện của employee',
            'messages' => [],
        ]);

        // Own user can view
        $this->actingAs($this->employee)
            ->get(route('ai-chat.show', $conversation->id))
            ->assertStatus(200);

        // Other user is forbidden
        $this->actingAs($this->employer)
            ->get(route('ai-chat.show', $conversation->id))
            ->assertStatus(403);
    }

    /** @test */
    public function user_can_send_message_and_receive_ai_response()
    {
        $conversation = AiConversation::create([
            'user_id' => $this->employee->id,
            'title' => 'Cuộc trò chuyện mới',
            'messages' => [],
        ]);



        $response = $this->actingAs($this->employee)
            ->from(route('ai-chat.show', $conversation->id))
            ->post(route('ai-chat.send', $conversation->id), [
                'message' => 'Làm sao viết CV IT đẹp?',
            ]);

        $response->assertRedirect();
        
        $conversation->refresh();
        $this->assertEquals('Viết CV IT đẹp', $conversation->title);
        $this->assertCount(2, $conversation->messages);
        
        $this->assertEquals('user', $conversation->messages[0]['role']);
        $this->assertEquals('Làm sao viết CV IT đẹp?', $conversation->messages[0]['content']);
        
        $this->assertEquals('assistant', $conversation->messages[1]['role']);
        $this->assertEquals('Đây là phản hồi giả lập từ trợ lý tuyển dụng AI.', $conversation->messages[1]['content']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }

    /** @test */
    public function user_can_delete_their_own_conversation()
    {
        $conversation = AiConversation::create([
            'user_id' => $this->employee->id,
            'title' => 'Cuộc trò chuyện',
            'messages' => [],
        ]);

        $this->actingAs($this->employee)
            ->delete(route('ai-chat.destroy', $conversation->id))
            ->assertRedirect(route('ai-chat.index'));

        $this->assertEquals(0, AiConversation::count());
    }

    /** @test */
    public function non_admins_cannot_access_admin_ai_chat_routes()
    {
        $conversation = AiConversation::create([
            'user_id' => $this->employee->id,
            'title' => 'Cuộc trò chuyện',
            'messages' => [],
        ]);

        // Employee cannot view index or show or delete
        $this->actingAs($this->employee)->get(route('admin.ai-chat.index'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('admin.ai-chat.show', $conversation->id))->assertStatus(403);
        $this->actingAs($this->employee)->delete(route('admin.ai-chat.destroy', $conversation->id))->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_index_details_and_delete_ai_conversations()
    {
        $conversation = AiConversation::create([
            'user_id' => $this->employee->id,
            'title' => 'Cuộc trò chuyện nhạy cảm',
            'messages' => [['role' => 'user', 'content' => 'Nhạy cảm', 'created_at' => now()->toDateTimeString()]],
        ]);

        // Admin view list
        $response = $this->actingAs($this->admin)->get(route('admin.ai-chat.index'));
        $response->assertStatus(200);
        $response->assertSee('Cuộc trò chuyện nhạy cảm');

        // Admin view details
        $detailResponse = $this->actingAs($this->admin)->get(route('admin.ai-chat.show', $conversation->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Chế độ Giám sát');
        $detailResponse->assertSee('Nhạy cảm');

        // Admin delete conversation
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.ai-chat.destroy', $conversation->id));
        $deleteResponse->assertRedirect(route('admin.ai-chat.index'));

        $this->assertEquals(0, AiConversation::count());
    }
}
