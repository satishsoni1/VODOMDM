@php
$steps = [
    1 => 'Client',
    2 => 'Employees',
    3 => 'Devices',
    4 => 'Done',
];
@endphp
<div class="d-flex align-items-center mb-4 flex-wrap gap-2">
    @foreach($steps as $num => $label)
        <span class="badge rounded-pill {{ $num == $current ? 'bg-primary' : ($num < $current ? 'bg-success' : 'bg-secondary') }} px-3 py-2">
            {{ $num }}. {{ $label }}
        </span>
        @if($num < count($steps))
            <i class="bi bi-arrow-right text-muted"></i>
        @endif
    @endforeach
</div>
