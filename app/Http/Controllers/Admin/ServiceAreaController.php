<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentBusiness;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceAreaRequest;
use App\Models\ServiceArea;
use App\Modules\ServiceArea\Actions\SaveServiceArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceAreaController extends Controller
{
    use ResolvesCurrentBusiness;

    public function index(Request $request): View
    {
        $business = $this->currentBusiness($request);

        $serviceAreas = $business->serviceAreas()->withCount('images')->orderBy('sort_order')->get();

        return view('admin.service-areas.index', compact('business', 'serviceAreas'));
    }

    public function create(Request $request): View
    {
        $business = $this->currentBusiness($request);

        return view('admin.service-areas.form', [
            'business' => $business,
            'serviceArea' => new ServiceArea(['area_type' => ServiceArea::TYPE_STOREFRONT, 'min_attendees' => 1, 'max_attendees' => 4, 'booking_fee' => 0, 'is_active' => true]),
        ]);
    }

    public function store(ServiceAreaRequest $request, SaveServiceArea $save): RedirectResponse
    {
        $serviceArea = $save->handle(
            $this->currentBusiness($request),
            $request->serviceAreaAttributes(),
            $request->imageIdsToDelete(),
            $request->newImages(),
        );

        return redirect()->route('admin.service-areas.index')->with('status', "Service area \"{$serviceArea->name}\" created.");
    }

    public function edit(Request $request, ServiceArea $serviceArea): View
    {
        $business = $this->currentBusiness($request);
        Gate::authorize('update', $serviceArea);

        $serviceArea->load('images');

        return view('admin.service-areas.form', compact('business', 'serviceArea'));
    }

    public function update(ServiceAreaRequest $request, ServiceArea $serviceArea, SaveServiceArea $save): RedirectResponse
    {
        $save->handle($this->currentBusiness($request), $request->serviceAreaAttributes(), $request->imageIdsToDelete(), $request->newImages(), $serviceArea);

        return redirect()->route('admin.service-areas.index')->with('status', "Service area \"{$serviceArea->name}\" updated.");
    }

    public function destroy(Request $request, ServiceArea $serviceArea): RedirectResponse
    {
        $this->currentBusiness($request);
        Gate::authorize('delete', $serviceArea);

        $serviceArea->delete();

        return redirect()->route('admin.service-areas.index')->with('status', 'Service area deleted.');
    }
}
