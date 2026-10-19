<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Modules\Conversation\Actions\ReplyToGuest;
use App\Modules\Conversation\Actions\StartConversation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sme:chat {business? : Business slug} {--locale=id : id|en|ja}')]
#[Description('Talk to the AI Business Assistant for a business from the terminal, for testing the tool-calling loop without the web UI.')]
class AssistantChatCommand extends Command
{
    public function handle(StartConversation $start, ReplyToGuest $reply): int
    {
        $business = $this->argument('business')
            ? Business::where('slug', $this->argument('business'))->first()
            : Business::first();

        if (! $business) {
            $this->error('No business found. Seed one first: php artisan db:seed');

            return self::FAILURE;
        }

        if (blank(config('services.local_llm.base_url'))) {
            $this->error('LOCAL_LLM_BASE_URL is not set in .env — the assistant has no model endpoint to call.');

            return self::FAILURE;
        }

        $locale = $this->option('locale');
        $conversation = $start->handle($business, $locale);

        $this->info("Chatting with the AI Business Assistant for {$business->name} ({$locale}). Type 'exit' to quit.");
        $this->newLine();

        while (true) {
            $guestMessage = $this->ask('You');

            if ($guestMessage === null || in_array(trim($guestMessage), ['exit', 'quit'], true)) {
                break;
            }

            $message = $reply->handle($business, $conversation, $guestMessage);

            $this->newLine();
            $this->line('<fg=cyan>Assistant:</> '.($message->content ?? '(no text — see UI payload below)'));

            if ($message->ui_payload) {
                $this->line('<fg=gray>[ui_payload] '.json_encode($message->ui_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).'</>');
            }

            $conversation->refresh();
            if ($conversation->isHandedOver()) {
                $this->warn('Conversation handed over to the business team. Ending session.');
                break;
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }
}
