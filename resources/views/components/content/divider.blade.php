@props(['anchorId' => null, 'color' => null, 'size' => 'large'])
<div @if ($anchorId) id="{{ $anchorId }}" @endif class="not-prose float-none">
    <hr style="border-color: {{ $color ?: 'rgba(0, 0, 0, 0)' }}" @class([
        'border-t-2',
        'my-16' => $size === 'large',
        'my-8' => $size === 'medium',
        'my-4' => ! in_array($size, ['large', 'medium']),
    ])>
</div>
