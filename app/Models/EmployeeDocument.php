<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = ['user_id', 'title', 'type', 'file_path'];

    public const CATALOG = [
        'identity' => [
            'label' => 'Identity & Address Proof',
            'items' => [
                'aadhaar_card' => ['label' => 'Aadhaar Card', 'required' => true],
                'pan_card' => ['label' => 'PAN Card', 'required' => true],
                'photograph' => ['label' => 'Passport-size Photograph', 'required' => true],
            ],
        ],
        'education' => [
            'label' => 'Educational Qualifications',
            'items' => [
                'degree_certificate' => ['label' => 'Degree / Diploma Certificate', 'required' => true],
                'marksheets' => ['label' => 'Mark Sheets / Transcripts', 'required' => true],
                'professional_certifications' => ['label' => 'Professional Certifications', 'required' => false],
            ],
        ],
        'employment_history' => [
            'label' => 'Employment History',
            'items' => [
                'relieving_letter' => ['label' => 'Relieving Letter / Experience Certificate', 'required' => false],
                'salary_slips' => ['label' => 'Last Drawn Salary Slips (3 months)', 'required' => false],
                'form_16' => ['label' => 'Form 16 / Last Employer Tax Documents', 'required' => false],
            ],
        ],
        'banking_statutory' => [
            'label' => 'Banking & Statutory',
            'items' => [
                'bank_details' => ['label' => 'Bank Account Details', 'required' => true],
                'pf_uan' => ['label' => 'PF Account Number / UAN', 'required' => false],
                'esi_details' => ['label' => 'ESI Details', 'required' => false],
            ],
        ],
        'hr_forms' => [
            'label' => 'Company-specific / HR Forms',
            'items' => [
                'offer_letter_acceptance' => ['label' => 'Offer Letter Acceptance', 'required' => true],
                'background_verification_consent' => ['label' => 'Background Verification Consent Form', 'required' => false],
                'emergency_contact_details' => ['label' => 'Emergency Contact Details', 'required' => true],
                'nominee_details' => ['label' => 'Nominee Details (PF / Gratuity / Insurance)', 'required' => false],
            ],
        ],
    ];

    public static function flatCatalog(): array
    {
        $flat = [];

        foreach (self::CATALOG as $items) {
            foreach ($items['items'] as $key => $item) {
                $flat[$key] = $item;
            }
        }

        return $flat;
    }

    public static function requiredKeys(): array
    {
        return collect(self::flatCatalog())->filter(fn ($item) => $item['required'])->keys()->all();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
