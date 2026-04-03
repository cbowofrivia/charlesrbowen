<?php

namespace App\Http\Controllers;

use App\Ai\Agents\CvAgent;
use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Services\SystemPromptService;
use Illuminate\Http\Request;
use Laravel\Ai\Streaming\Events\TextDelta;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'session_id' => ['required', 'uuid'],
        ]);

        $conversation = Conversation::firstOrCreate(
            ['session_id' => $validated['session_id']],
            ['ip_address' => $request->ip()],
        );

        $conversation->messages()->create([
            'role' => MessageRole::User,
            'content' => $validated['message'],
        ]);

        $agent = new CvAgent($conversation, app(SystemPromptService::class));

        $stream = $agent->stream($validated['message']);

        return response()->stream(function () use ($stream, $conversation) {
            // Disable output buffering for real-time streaming
            while (ob_get_level()) {
                ob_end_flush();
            }

            $fullText = '';

            foreach ($stream as $event) {
                if ($event instanceof TextDelta) {
                    $fullText .= $event->delta;

                    echo $event->delta;
                    flush();
                }
            }

            if ($fullText !== '') {
                $conversation->messages()->create([
                    'role' => MessageRole::Assistant,
                    'content' => $fullText,
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
