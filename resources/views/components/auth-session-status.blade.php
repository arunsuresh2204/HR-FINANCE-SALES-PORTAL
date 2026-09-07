@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-emerald-400/25 bg-emerald-400/10 px-4 py-2.5 text-sm font-medium text-emerald-300']) }}>
        {{ $status }}
    </div>
@endif
