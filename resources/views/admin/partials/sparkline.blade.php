@php
    $values = $values ?? [0];
    if ($values === []) {
        $values = [0];
    }
    $max = max(1, ...$values);
    $width = 78;
    $height = 28;
    $last = max(1, count($values) - 1);
    $points = [];
    foreach (array_values($values) as $i => $value) {
        $x = count($values) === 1 ? 0 : ($i / $last) * $width;
        $y = $height - (((float) $value / $max) * ($height - 6)) - 3;
        $points[] = round($x, 1) . ',' . round($y, 1);
    }
@endphp
<svg viewBox="0 0 {{ $width }} {{ $height }}" width="78" height="28" aria-hidden="true">
    <polyline fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="{{ implode(' ', $points) }}"></polyline>
</svg>
