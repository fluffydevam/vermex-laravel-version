<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __invoke(Request $request, ReportService $reports)
    {
        Gate::authorize('view-any-payment');

        $filters = $reports->filtersFrom($request);

        return view('reports.index', [
            'filters' => $filters,
            'financial' => $reports->financial($filters),
            'operations' => $reports->operations($filters),
            'routeBase' => 'admin.reports',
            'showOperations' => true,
        ]);
    }
}
