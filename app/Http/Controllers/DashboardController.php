<?php

namespace App\Http\Controllers;

use App\Services\MonthlyDueService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, MonthlyDueService $monthlyDueService): View
    {
        $dueSnapshot = $monthlyDueService->memberSnapshot($request->user()->id);

        return view('app.dashboard', [
            'dueSnapshot' => $dueSnapshot,
        ]);
    }
}
