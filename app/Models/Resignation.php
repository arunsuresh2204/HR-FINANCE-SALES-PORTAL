<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resignation extends Model
{
    protected $fillable = [
        'user_id', 'notice_date', 'last_working_date', 'reason', 'cc_email', 'status', 'reviewed_by', 'reviewed_at',
        'experience_certificate_content', 'experience_certificate_published_at',
        'relieving_letter_content', 'relieving_letter_published_at',
        'form16_path', 'uan_pf_path',
    ];

    protected function casts(): array
    {
        return [
            'notice_date' => 'date',
            'last_working_date' => 'date',
            'reviewed_at' => 'datetime',
            'experience_certificate_published_at' => 'datetime',
            'relieving_letter_published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function offboardingTasks(): HasMany
    {
        return $this->hasMany(OffboardingTask::class);
    }

    /**
     * A starting draft HR can edit before publishing — not a final,
     * ready-to-send letter. Avoids assuming a pronoun for the employee.
     */
    public function draftExperienceCertificateContent(): string
    {
        $name = e($this->user->name);
        $designation = e($this->user->designation ?: 'the role assigned to them');
        $department = $this->user->department ? ' in the '.e($this->user->department).' department' : '';
        $joined = $this->user->date_of_joining?->format('F j, Y') ?? '[date of joining]';
        $lastDay = $this->last_working_date->format('F j, Y');

        return <<<HTML
        <p>TO WHOMSOEVER IT MAY CONCERN</p>
        <p>This is to certify that <strong>{$name}</strong> (Employee Code: {$this->user->employee_code}) was employed with Nexstarc Technologies as <strong>{$designation}</strong>{$department} from <strong>{$joined}</strong> to <strong>{$lastDay}</strong>.</p>
        <p>During this period, we found {$name} to be sincere, hardworking, and professional in conduct. We wish them all the best in their future endeavors.</p>
        HTML;
    }

    public function draftRelievingLetterContent(): string
    {
        $name = e($this->user->name);
        $noticeDate = $this->notice_date->format('F j, Y');
        $lastDay = $this->last_working_date->format('F j, Y');

        return <<<HTML
        <p>Dear {$name},</p>
        <p>This is with reference to your resignation letter dated <strong>{$noticeDate}</strong>. We would like to inform you that you are relieved from the services of Nexstarc Technologies with effect from the close of business hours on <strong>{$lastDay}</strong>.</p>
        <p>Your full and final settlement will be processed as per company policy. We thank you for your contribution during your tenure with us and wish you success in your future endeavors.</p>
        HTML;
    }
}
