<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-glass-primary']) }}>
    {{ $slot }}
</button>
