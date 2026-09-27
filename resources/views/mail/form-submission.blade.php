<x-mail::message>
# {{ config("content.forms.{$form}.subject") }}

@foreach ($fields as $label => $value)
**{{ $label }}**<br>
{{ $value !== '' ? $value : '—' }}

@endforeach
</x-mail::message>
