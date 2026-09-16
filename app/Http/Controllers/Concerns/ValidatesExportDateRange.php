<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidatesExportDateRange
{
    protected function dateRange(Request $request): array
    {
        $validated = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ])->validate();

        return [
            Carbon::parse($validated['from'])->startOfDay(),
            Carbon::parse($validated['to'])->endOfDay(),
        ];
    }
}
