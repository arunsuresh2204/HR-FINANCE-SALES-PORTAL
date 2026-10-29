<?php

namespace App\Http\Controllers;

use App\Models\FinanceSetting;
use App\Models\Resignation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class RelievingLetterPdfController extends Controller
{
    public function __invoke(Resignation $resignation)
    {
        $user = Auth::user();

        abort_unless($user->id === $resignation->user_id || $user->can('access_hr_admin'), 403);
        abort_unless($resignation->relieving_letter_published_at, 404);

        $pdf = Pdf::loadView('pdf.exit-document', [
            'title' => 'Relieving Letter',
            'content' => $resignation->relieving_letter_content,
            'financeSetting' => FinanceSetting::current(),
        ]);

        return $pdf->stream("Relieving-Letter-{$resignation->user->employee_code}.pdf");
    }
}
