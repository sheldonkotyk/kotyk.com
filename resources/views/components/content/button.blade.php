@props(['buttonUrl', 'buttonText'])
<div class="not-prose my-12">
    <flux:button variant="primary" href="{{ $buttonUrl }}">{{ $buttonText }}</flux:button>
</div>
