<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentBusiness;
use App\Http\Controllers\Controller;
use App\Modules\Operations\Queries\DashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesCurrentBusiness;

    public function index(Request $request, DashboardMetrics $metrics): View
    {
        $business = $this->currentBusiness($request);

        return view('admin.dashboard', [
            'business' => $business,
            'stats' => $metrics->counts($business),
            'recentBookings' => $metrics->recentBookings($business),
            'openHandovers' => $metrics->latestOpenHandovers($business),
        ]);
    }
}
