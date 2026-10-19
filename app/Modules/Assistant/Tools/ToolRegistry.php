<?php

namespace App\Modules\Assistant\Tools;

use App\Modules\Assistant\Tools\Handlers\CheckAvailabilityTool;
use App\Modules\Assistant\Tools\Handlers\CreateBookingRequestTool;
use App\Modules\Assistant\Tools\Handlers\GetCatalogItemTool;
use App\Modules\Assistant\Tools\Handlers\RequestHumanHandoverTool;
use App\Modules\Assistant\Tools\Handlers\SearchCatalogTool;
use App\Modules\Assistant\Tools\Handlers\SearchKnowledgeTool;
use App\Modules\Assistant\Tools\Handlers\SearchServiceAreaTool;
use Illuminate\Contracts\Container\Container;

/**
 * The whitelist of tools the model may call. A model-supplied name is looked
 * up here and never turned into a class name.
 */
class ToolRegistry
{
    /**
     * @var list<class-string<Tool>>
     */
    private const HANDLERS = [
        SearchKnowledgeTool::class,
        SearchCatalogTool::class,
        GetCatalogItemTool::class,
        SearchServiceAreaTool::class,
        CheckAvailabilityTool::class,
        CreateBookingRequestTool::class,
        RequestHumanHandoverTool::class,
    ];

    /**
     * @var array<string, Tool>|null
     */
    private ?array $tools = null;

    public function __construct(private readonly Container $container) {}

    /**
     * OpenAI-compatible function-calling schema sent with every model request.
     *
     * @return list<array<string, mixed>>
     */
    public function definitions(): array
    {
        return array_values(array_map(fn (Tool $tool) => $tool->definition(), $this->all()));
    }

    public function find(string $name): ?Tool
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * @return array<string, Tool>
     */
    private function all(): array
    {
        return $this->tools ??= collect(self::HANDLERS)
            ->map(fn (string $class) => $this->container->make($class))
            ->keyBy(fn (Tool $tool) => $tool->name())
            ->all();
    }
}
