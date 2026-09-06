<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportingService $reporting): View
    {
        return view('dashboard', [
            'kpis' => $reporting->kpis(),
            'mensuel' => $reporting->creditsVsRemboursementsParMois(),
            'evolution' => $reporting->evolutionEncours(),
            'repartition' => $reporting->repartitionEncours(),
        ]);
    }
}
