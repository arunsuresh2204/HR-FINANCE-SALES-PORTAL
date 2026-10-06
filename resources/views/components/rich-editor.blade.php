@props(['model', 'value' => '', 'placeholder' => ''])

<div wire:ignore x-data="richEditor(@js($model), @js($value), @js($placeholder))" {{ $attributes }}>
    <div x-ref="editor" class="rich-editor-container"></div>
</div>
