<?php

namespace App\Http\Controllers;

use App\Ai\Agents\CvAgent;
use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\SystemPromptService;
use Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Streaming\Events\TextDelta;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function store(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'session_id' => ['required', 'uuid'],
        ]);

        $conversation = Conversation::query()->firstOrCreate(
            ['session_id' => $validated['session_id']],
            ['ip_address' => $request->ip()],
        );

        $conversation->messages()->create([
            'role' => MessageRole::User,
            'content' => $validated['message'],
        ]);

        $agent = new CvAgent($conversation, app(SystemPromptService::class));

        $stream = $agent->stream($validated['message']);

        return response()->stream(function () use ($stream, $conversation): Generator {
            $fullText = '';

            foreach ($stream as $event) {
                if ($event instanceof TextDelta) {
                    $fullText .= $event->delta;

                    yield $event->delta;
                }
            }

            if ($fullText !== '') {
                $conversation->messages()->create([
                    'role' => MessageRole::Assistant,
                    'content' => $fullText,
                ]);
            }
        });
    }

    public function messages(string $sessionId): JsonResponse
    {
        $conversation = Conversation::query()
            ->where('session_id', $sessionId)
            ->first();

        if (! $conversation) {
            return response()->json([]);
        }

        return response()->json(
            $conversation->messages()
                ->oldest()
                ->get()
                ->map(fn (Message $message) => [
                    'id' => (string) $message->id,
                    'role' => $message->role->value,
                    'content' => $message->content,
                ])
                ->values()
        );
    }
}
