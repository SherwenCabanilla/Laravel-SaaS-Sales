<?php

namespace App\Http\Controllers;

use App\Services\OwnerReportService;
use Illuminate\Http\Request;

class OwnerReportController extends Controller
{
    public function index(Request $request, OwnerReportService $reports)
    {
        $tenant = $request->user()->tenant?->loadMissing('defaultPayoutAccount');
        abort_if(! $tenant, 404);

        return view('reports.owner', [
            'report' => $reports->build($tenant, $request->only(['date_from', 'date_to', 'funnel_id'])),
        ]);
    }

    public function export(Request $request, OwnerReportService $reports)
    {
        $tenant = $request->user()->tenant;
        abort_if(! $tenant, 404);

        return $reports->export($tenant, $request->only(['date_from', 'date_to', 'funnel_id']));
    }
}
