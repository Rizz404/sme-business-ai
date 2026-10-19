<?php

namespace App\Modules\Knowledge\Queries;

use App\Models\Business;
use App\Models\BusinessKnowledgeItem;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The business's approved knowledge base, searched by keyword. The only source the
 * AI Business Assistant may state business facts from.
 */
class SearchKnowledge
{
    /**
     * @return Collection<int, BusinessKnowledgeItem>
     */
    public function handle(Business $business, string $query, ?string $category = null, int $limit = 5): Collection
    {
        $query = SearchText::normalize($query);

        return $business->knowledgeItems()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->get()
            ->filter(fn (BusinessKnowledgeItem $item) => SearchText::matches(
                $item->title.' '.$item->body.' '.implode(' ', $item->tags ?? []),
                $query,
            ))
            ->take($limit);
    }
}
