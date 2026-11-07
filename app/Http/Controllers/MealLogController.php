<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMealLogRequest;
use App\Models\Food;
use App\Models\MealLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MealLogController extends Controller
{
    public function index(): View
    {
        $foods = Food::orderBy('name')->get();

        $logs = MealLog::with('food')
            ->where('user_id', auth()->id())
            ->whereDate('logged_at', today())
            ->orderBy('logged_at')
            ->get();

        return view('patient.meals.index', compact('foods', 'logs'));
    }

    public function store(StoreMealLogRequest $request): RedirectResponse
    {
        MealLog::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('patient.meals.index')->with('status', 'Meal logged.');
    }
}