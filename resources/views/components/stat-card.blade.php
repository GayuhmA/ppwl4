@props(['label', 'value', 'color' => 'text-primary'])
<div class="card shadow-sm">
    <div class="card-body">
        <div class="text-muted small text-uppercase fw-semibold">{{ $label }}</div>
        <div class="fs-2 fw-bold {{ $color }} mt-1">{{ $value }}</div>
    </div>
</div>