<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentBusiness;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Models\Booking;
use App\Modules\Booking\Actions\ChangeBookingStatus;
use App\Modules\Booking\Enums\BookingStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    use ResolvesCurrentBusiness;

    public function index(Request $request): View
    {
        $business = $this->currentBusiness($request);

        $status = in_array($request->query('status'), BookingStatus::values(), true)
            ? $request->query('status')
            : null;

        $bookings = $business->bookings()
            ->with('serviceArea')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.bookings.index', compact('business', 'bookings', 'status'));
    }

    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking, ChangeBookingStatus $changeStatus): RedirectResponse
    {
        $this->currentBusiness($request);

        $status = $request->validated('status');

        $changeStatus->handle($booking, $status);

        return back()->with('status', "Booking {$booking->reference} marked as {$status}.");
    }
}
