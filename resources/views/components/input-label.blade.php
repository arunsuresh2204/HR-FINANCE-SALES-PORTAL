@props(['value'])

<label {{ $attributes->merge(['class' => 'label-glass']) }}>
    {{ $value ?? $slot }}
</label>
