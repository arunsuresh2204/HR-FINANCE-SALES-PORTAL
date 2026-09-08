<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    public const STATUSES = ['pending', 'positive', 'negative', 'proposal_sent', 'rejected', 'won', 'lost'];

    public const CLOSED_STATUSES = ['won', 'lost', 'rejected'];

    protected $fillable = [
        'sales_person_id', 'client_name', 'company_name', 'country', 'email', 'phone',
        'whatsapp', 'requirement', 'service_type', 'source', 'status', 'budget', 'follow_up_date',
        'contacted_date', 'comment', 'contact_link',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date' => 'date',
            'contacted_date' => 'date',
            'budget' => 'decimal:2',
        ];
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }
}
