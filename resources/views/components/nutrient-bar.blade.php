@props(['row'])

@php
    $isLimit = $row['label'] === 'Sodium (limit)';
    $over = $isLimit ? $row['percent'] > 100 : $row['percent'] > 110;
    $barColor = $over ? 'bg-amber-600' : 'bg-emerald-600';
    $note = $isLimit
        ? ($over ? 'Over the daily limit' : 'Within the limit')
        : ($over ? 'Above target' : ($row['percent'] >= 90 ? 'Target reached' : 'Below target'));
@endphp

<div>
    <div class="flex justify-between gap-2 text-sm">
        <span class="font-medium text-gray-900">{{ $row['label'] }}</span>
        <span class="text-gray-700">{{ $row['intake'] }} / {{ $row['target'] }} {{ $row['unit'] }}</span>
    </div>

    <div class="mt-2 h-2.5 w-full rounded-full bg-gray-100" role="progressbar" aria-valuemin="0" aria-valuemax="100"
        aria-valuenow="{{ min($row['percent'], 100) }}"
        aria-label="{{ $row['label'] }}: {{ $row['percent'] }}% of daily target">
        <div class="h-2.5 rounded-full {{ $barColor }}" style="width: {{ min($row['percent'], 100) }}%"></div>
    </div>

    <p class="mt-1 text-xs text-gray-700">{{ $row['percent'] }}% · {{ $note }}</p>
</div>