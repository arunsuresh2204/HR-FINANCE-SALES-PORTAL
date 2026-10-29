<?php

namespace App\Http\Controllers;

use App\Models\FinanceSetting;
use App\Models\Resignation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ExperienceCertificatePdfController extends Controller
{
    public function __invoke(Resignation $resignation)
    {
        $user = Auth::user();

        abort_unless($user->id === $resignation->user_id || $user->can('access_hr_admin'), 403);
        abort_unless($resignation->experience_certificate_published_at, 404);

        $pdf = Pdf::loadView('pdf.exit-document', [
            'title' => 'Experience Certificate',
            'content' => $resignation->experience_certificate_content,
            'financeSetting' => FinanceSetting::current(),
        ]);

        return $pdf->stream("Experience-Certificate-{$resignation->user->employee_code}.pdf");
    }
}
