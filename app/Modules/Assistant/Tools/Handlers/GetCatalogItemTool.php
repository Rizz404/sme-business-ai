<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;

final class GetCatalogItemTool implements Tool
{
    public function name(): string
    {
        return 'get_catalog_item';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Get the full details of one catalog item by its slug (from a previous search_catalog result): description, price, requirements, delivery style, duration and whether it is unavailable. Use this when the customer asks about a specific product or service.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'catalog_item_slug' => ['type' => 'string'],
                    ],
                    'required' => ['catalog_item_slug'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $item = CatalogSummaries::catalogItemBySlug($context->business, (string) ($arguments['catalog_item_slug'] ?? ''));

        if (! $item) {
            return new ToolResult('Catalog item not found.');
        }

        $detail = [
            ...CatalogSummaries::catalogItem($item, $context->business, $context->locale),
            'description' => $item->translatedDescription($context->locale),
            'duration_minutes' => $item->duration_minutes,
            'note' => 'price is the current catalog price; requirements are guidance only; the team confirms the final scope and any special condition.',
        ];

        return ToolResult::json($detail, ['type' => 'catalog_detail', 'item' => $detail]);
    }
}
