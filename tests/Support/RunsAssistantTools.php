<?php

namespace Tests\Support;

use App\Models\Business;
use App\Models\Conversation;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolExecutor;

/**
 * Runs one AI Business Assistant tool the way the model would, against real business data.
 */
trait RunsAssistantTools
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{text: string, ui: ?array<string, mixed>}
     */
    protected function runTool(Business $business, Conversation $conversation, string $name, array $input): array
    {
        $result = app(ToolExecutor::class)->execute(new ToolContext($business, $conversation, $conversation->locale), $name, $input);

        return ['text' => $result->text, 'ui' => $result->ui];
    }
}
