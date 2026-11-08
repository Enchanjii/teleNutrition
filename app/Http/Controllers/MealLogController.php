<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMealLogRequest;
use App\Models\Food;
use App\Models\MealLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MealLogController extends Controller
{
    private const MEAL_ORDER = ['breakfast', 'lunch', 'dinner', 'snack'];

    public function index(): View
    {
        $foods = Food::orderBy('name')
            ->get(['id', 'name', 'calories', 'protein_g', 'carbs_g', 'fat_g'])
            ->map(fn(Food $food) => [
                'id' => $food->id,
                'name' => $food->name,
                'calories' => (float) $food->calories,
                'protein' => (float) $food->protein_g,
                'carbs' => (float) $food->carbs_g,
                'fat' => (float) $food->fat_g,
            ])
            ->values();

        $logs = MealLog::with('food')
            ->where('user_id', auth()->id())
            ->whereDate('logged_at', today())
            ->orderBy('logged_at')
            ->get();

        $groups = [];
        foreach (self::MEAL_ORDER as $type) {
            $items = $logs->where('meal_type', $type)->values();

            if ($items->isEmpty()) {
                continue;
            }

            $groups[] = [
                'type' => $type,
                'calories' => round($items->sum(fn(MealLog $log) => $log->food->calories * $log->quantity)),
                'items' => $items->map(fn(MealLog $log) => [
                    'name' => $log->food->name,
                    'quantity' => rtrim(rtrim(number_format((float) $log->quantity, 2, '.', ''), '0'), '.'),
                    'time' => $log->logged_at->format('g:i A'),
                    'calories' => round($log->food->calories * $log->quantity),
                ])->all(),
            ];
        }

        return view('patient.meals.index', [
            'foods' => $foods,
            'groups' => $groups,
            'totalCalories' => collect($groups)->sum('calories'),
            'defaultMealType' => $this->defaultMealType(),
        ]);
    }

    public function store(StoreMealLogRequest $request): RedirectResponse
    {
        MealLog::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('patient.meals.index')->with('status', 'Meal logged.');
    }

    private function defaultMealType(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour < 10 => 'breakfast',
            $hour < 14 => 'lunch',
            $hour < 17 => 'snack',
            $hour < 21 => 'dinner',
            default => 'snack',
        };
    }
}