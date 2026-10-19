<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveBusiness;
use App\Http\Requests\Api\ConversationHistoryRequest;
use App\Http\Requests\Api\SendMessageRequest;
use App\Http\Requests\Api\SpeakMessageRequest;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Modules\Assistant\Speech\SpeechSynthesizer;
use App\Modules\Assistant\Speech\SpeechText;
use App\Modules\Conversation\Actions\RememberUiContext;
use App\Modules\Conversation\Actions\ReplyToGuest;
use App\Modules\Conversation\Actions\StartConversation;
use App\Modules\Conversation\Support\MessagePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AssistantChatController extends Controller
{
    public function start(Request $request, StartConversation $start): JsonResponse
    {
        $conversation = $start->handle(ResolveBusiness::business($request), ResolveBusiness::locale($request));

        return response()->json([
            'guest_token' => $conversation->guest_token,
            'locale' => $conversation->locale,
        ]);
    }

    public function message(SendMessageRequest $request, RememberUiContext $remember, ReplyToGuest $reply): JsonResponse
    {
        $business = ResolveBusiness::business($request);

        $conversation = $this->findConversation($business, $request->validated('guest_token'));

        $remember->handle($business, $conversation, $request->uiContext());

        try {
            $assistantMessage = $reply->handle($business, $conversation, $request->validated('message'));
        } catch (\Throwable $e) {
            Log::error('Assistant reply failed', ['business_id' => $business->id, 'error' => $e->getMessage()]);

            return response()->json([
                'error' => 'assistant_unavailable',
                'message' => 'The AI Business Assistant is temporarily unavailable. Please try again in a moment.',
            ], 503);
        }

        return response()->json([
            'message' => MessagePresenter::format($assistantMessage),
            'status' => $conversation->fresh()->status,
        ]);
    }

    public function history(ConversationHistoryRequest $request): JsonResponse
    {
        $conversation = $this->findConversation(ResolveBusiness::business($request), $request->validated('guest_token'));

        $messages = $conversation->messages()
            ->whereIn('role', [ConversationMessage::ROLE_GUEST, ConversationMessage::ROLE_ASSISTANT, ConversationMessage::ROLE_STAFF, ConversationMessage::ROLE_SYSTEM])
            ->orderBy('created_at')
            ->get()
            ->map(fn (ConversationMessage $m) => MessagePresenter::format($m))
            ->values();

        return response()->json([
            'messages' => $messages,
            'status' => $conversation->status,
        ]);
    }

    /**
     * The assistant's reply as audio. Only an assistant message of this guest's
     * own conversation can be spoken, so the endpoint is not a free voice for
     * arbitrary text. Any failure is a plain 204: the reply is already on
     * screen and the assistant simply stays quiet.
     */
    public function speech(SpeakMessageRequest $request, SpeechSynthesizer $speech): Response
    {
        $conversation = $this->findConversation(ResolveBusiness::business($request), $request->validated('guest_token'));

        $message = $conversation->messages()
            ->where('role', ConversationMessage::ROLE_ASSISTANT)
            ->whereKey($request->validated('message_id'))
            ->firstOrFail();

        $text = SpeechText::from((string) $message->content);

        if ($text === '' || ! $speech->available()) {
            return response()->noContent();
        }

        try {
            $audio = $speech->synthesize($text);
        } catch (\Throwable $e) {
            Log::warning('Assistant speech failed', ['message_id' => $message->id, 'error' => $e->getMessage()]);

            return response()->noContent();
        }

        return response($audio, 200, [
            'Content-Type' => SpeechSynthesizer::MIME_TYPE,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    private function findConversation(Business $business, string $guestToken): Conversation
    {
        $conversation = Conversation::where('business_id', $business->id)
            ->where('guest_token', $guestToken)
            ->first();

        if (! $conversation) {
            throw ValidationException::withMessages([
                'guest_token' => 'This conversation no longer exists. Please start a new one.',
            ]);
        }

        return $conversation;
    }
}
