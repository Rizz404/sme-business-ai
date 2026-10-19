<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResolveBusiness;
use App\Models\Business;
use App\Models\BusinessKnowledgeItem;
use App\Models\CatalogItem;
use App\Models\ServiceArea;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Queries\BuildStage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The guest-facing stage: one page per scene, all built from the same shell
 * data. The business and the language are resolved by the ResolveBusiness middleware.
 */
class BusinessPageController extends Controller
{
    public function __construct(private readonly BuildStage $stage) {}

    public function index(Request $request): View
    {
        $businesses = Business::published()->orderBy('name')->get();

        $locale = in_array($request->query('lang'), config('business.locales'), true)
            ? $request->query('lang')
            : ($businesses->first()?->default_locale ?? config('business.default_locale'));

        return view('opening', [
            'businesses' => $businesses,
            'locale' => $locale,
            'supportedLocales' => config('business.locales'),
            'opening' => StageCopy::opening($locale),
            'avatarImage' => StageBackdrop::avatar(),
            'backdrop' => StageBackdrop::for('home'),
        ]);
    }

    public function show(Request $request): View
    {
        return view('business.show', $this->stageData($request, 'home'));
    }

    public function catalog(Request $request): View
    {
        return view('business.catalog-index', $this->stageData($request, 'catalog'));
    }

    public function catalogItem(Request $request, string $businessSlug, string $itemSlug): View
    {
        $data = $this->stageData($request, 'catalog-item');
        $items = $data['catalogItems'];
        $index = $items->search(fn (CatalogItem $item) => $item->slug === $itemSlug);

        abort_if($index === false, 404);

        return view('business.catalog-detail', [
            ...$data,
            'catalogItem' => $items[$index],
            'itemIndex' => $index,
            ...$this->neighbours($items, $index, 'Item'),
        ]);
    }

    public function service(Request $request): View
    {
        return view('business.service-index', $this->stageData($request, 'service'));
    }

    public function serviceArea(Request $request, string $businessSlug, string $areaSlug): View
    {
        $data = $this->stageData($request, 'service-area');
        $areas = $data['serviceAreas'];
        $index = $areas->search(fn (ServiceArea $area) => $area->slug === $areaSlug);

        abort_if($index === false, 404);

        return view('business.service-detail', [
            ...$data,
            'serviceArea' => $areas[$index],
            'areaIndex' => $index,
            ...$this->neighbours($areas, $index, 'Area'),
        ]);
    }

    public function facilities(Request $request): View
    {
        return view('business.facilities-index', $this->stageData($request, 'facilities'));
    }

    public function facility(Request $request, string $businessSlug, int $facilityId): View
    {
        $data = $this->stageData($request, 'facility');
        $facilities = $data['facilities'];
        $index = $facilities->search(fn (BusinessKnowledgeItem $item) => $item->id === $facilityId);

        abort_if($index === false, 404);

        return view('business.facility-detail', [
            ...$data,
            'facility' => $facilities[$index],
            'facilityIndex' => $index,
            ...$this->neighbours($facilities, $index, 'Facility'),
        ]);
    }

    public function info(Request $request): View
    {
        return view('business.info-index', $this->stageData($request, 'info'));
    }

    public function staff(Request $request): View
    {
        return view('business.staff-index', $this->stageData($request, 'staff'));
    }

    public function bookingScene(Request $request): View
    {
        return view('business.booking-index', [
            ...$this->stageData($request, 'booking'),
            'preselectedArea' => (string) $request->query('area', ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stageData(Request $request, string $scene): array
    {
        return $this->stage->handle(ResolveBusiness::business($request), ResolveBusiness::locale($request), $scene);
    }

    /**
     * The previous and next entry around an index, wrapping at both ends.
     *
     * @param  Collection<int, mixed>  $items
     * @return array<string, mixed>
     */
    private function neighbours(Collection $items, int $index, string $suffix): array
    {
        return [
            'previous'.$suffix => $items[($index - 1 + $items->count()) % $items->count()],
            'next'.$suffix => $items[($index + 1) % $items->count()],
        ];
    }
}
