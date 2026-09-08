@props(['status'])

@php
    $map = [
        'pending' => 'bg-amber-400/15 text-amber-300 border-amber-400/20',
        'approved' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'accepted' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'completed' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'paid' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'won' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'processed' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'invoiced' => 'bg-sky-400/15 text-sky-300 border-sky-400/20',
        'sent' => 'bg-sky-400/15 text-sky-300 border-sky-400/20',
        'partially_paid' => 'bg-sky-400/15 text-sky-300 border-sky-400/20',
        'new' => 'bg-sky-400/15 text-sky-300 border-sky-400/20',
        'contacted' => 'bg-violet-400/15 text-violet-300 border-violet-400/20',
        'proposal_sent' => 'bg-violet-400/15 text-violet-300 border-violet-400/20',
        'negotiation' => 'bg-gold-400/15 text-gold-300 border-gold-400/20',
        'positive' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'negative' => 'bg-rose-400/15 text-rose-300 border-rose-400/20',
        'in_progress' => 'bg-sky-400/15 text-sky-300 border-sky-400/20',
        'draft' => 'bg-white/10 text-white/60 border-white/15',
        'rejected' => 'bg-rose-400/15 text-rose-300 border-rose-400/20',
        'lost' => 'bg-rose-400/15 text-rose-300 border-rose-400/20',
        'overdue' => 'bg-rose-400/15 text-rose-300 border-rose-400/20',
        'blocked' => 'bg-rose-400/15 text-rose-300 border-rose-400/20',
        'cancelled' => 'bg-white/10 text-white/50 border-white/15',
        'offboarded' => 'bg-white/10 text-white/50 border-white/15',
        'resigned' => 'bg-rose-400/15 text-rose-300 border-rose-400/20',
        'on_notice' => 'bg-amber-400/15 text-amber-300 border-amber-400/20',
        'active' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'assigned' => 'bg-sky-400/15 text-sky-300 border-sky-400/20',
        'returned' => 'bg-white/10 text-white/60 border-white/15',
        'present' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'late' => 'bg-amber-400/15 text-amber-300 border-amber-400/20',
        'absent' => 'bg-rose-400/15 text-rose-300 border-rose-400/20',
        'on_leave' => 'bg-violet-400/15 text-violet-300 border-violet-400/20',
    ];
    $classes = $map[$status] ?? 'bg-white/10 text-white/60 border-white/15';
    $label = ucwords(str_replace('_', ' ', $status));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold $classes"]) }}>
    {{ $label }}
</span>
