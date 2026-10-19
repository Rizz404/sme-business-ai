<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentBusiness;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CatalogItemRequest;
use App\Models\CatalogItem;
use App\Modules\Catalog\Actions\SaveCatalogItem;
use App\Modules\Catalog\Enums\CatalogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CatalogItemController extends Controller
{
    use ResolvesCurrentBusiness;

    public function index(Request $request): View
    {
        $business = $this->currentBusiness($request);

        $catalogItems = $business->catalogItems()->orderBy('category')->orderBy('sort_order')->get();

        return view('admin.catalog-items.index', compact('business', 'catalogItems'));
    }

    public function create(Request $request): View
    {
        $business = $this->currentBusiness($request);

        return view('admin.catalog-items.form', [
            'business' => $business,
            'catalogItem' => new CatalogItem(['category' => CatalogItem::CATEGORY_PRODUCT, 'is_active' => true]),
            'categories' => CatalogCategory::values(),
        ]);
    }

    public function store(CatalogItemRequest $request, SaveCatalogItem $save): RedirectResponse
    {
        $catalogItem = $save->handle($this->currentBusiness($request), $request->catalogItemAttributes());

        return redirect()->route('admin.catalog-items.index')->with('status', "Catalog item \"{$catalogItem->name}\" created.");
    }

    public function edit(Request $request, CatalogItem $catalogItem): View
    {
        $business = $this->currentBusiness($request);
        Gate::authorize('update', $catalogItem);

        return view('admin.catalog-items.form', [
            'business' => $business,
            'catalogItem' => $catalogItem,
            'categories' => CatalogCategory::values(),
        ]);
    }

    public function update(CatalogItemRequest $request, CatalogItem $catalogItem, SaveCatalogItem $save): RedirectResponse
    {
        $save->handle($this->currentBusiness($request), $request->catalogItemAttributes(), $catalogItem);

        return redirect()->route('admin.catalog-items.index')->with('status', "Catalog item \"{$catalogItem->name}\" updated.");
    }

    public function destroy(Request $request, CatalogItem $catalogItem): RedirectResponse
    {
        $this->currentBusiness($request);
        Gate::authorize('delete', $catalogItem);

        $catalogItem->delete();

        return redirect()->route('admin.catalog-items.index')->with('status', 'Catalog item deleted.');
    }
}
