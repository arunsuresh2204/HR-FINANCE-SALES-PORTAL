<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-glass-danger']) }}>
    {{ $slot }}
</button>
