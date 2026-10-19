<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\CatalogItem;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Catalog\Queries\SearchCatalog;

final class SearchCatalogTool implements Tool
{
    public function __construct(private readonly SearchCatalog $search) {}

    public function name(): string
    {
        return 'search_catalog';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Search the business catalog by keyword, category, tag or budget. Returns matching products and services with their real price, requirements and whether they are unavailable. Use this whenever a customer asks what the business offers, wants a recommendation, or describes a need or budget rather than naming one item.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Keywords such as "consultation", "starter package", "delivery" — optional'],
                        'category' => [
                            'type' => 'string',
                            'enum' => CatalogItem::categories(),
                            'description' => 'Optional category filter',
                        ],
                        'tag' => [
                            'type' => 'string',
                            'enum' => CatalogItem::FILTER_TAGS,
                            'description' => 'Optional tag filter',
                        ],
                        'max_price' => ['type' => 'number', 'description' => 'Optional budget per item, in the business currency'],
                    ],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $items = $this->search->handle(
            $context->business,
            (string) ($arguments['query'] ?? ''),
            $arguments['category'] ?? null,
            $arguments['tag'] ?? null,
            isset($arguments['max_price']) ? (float) $arguments['max_price'] : null,
        );

        if ($items->isEmpty()) {
            return new ToolResult('No catalog item matches that request. Tell the customer honestly and suggest a nearby alternative from another search_catalog call, or call request_human_handover for custom requests.');
        }

        $results = $items->map(fn (CatalogItem $item) => CatalogSummaries::catalogItem($item, $context->business, $context->locale))->values()->all();

        return ToolResult::json($results, ['type' => 'catalog_results', 'items' => $results]);
    }
}
