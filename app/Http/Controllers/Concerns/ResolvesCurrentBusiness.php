<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Business;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ResolvesCurrentBusiness
{
    protected function currentBusiness(Request $request): Business
    {
        $business = $request->user()->currentBusiness();

        if (! $business) {
            throw new HttpException(403, 'No business is linked to this account yet.');
        }

        return $business;
    }
}
