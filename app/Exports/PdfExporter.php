<?php

namespace App\Exports;

use App\Models\Lecture;

class PdfExporter
{
    public function download(Lecture $lecture)
    {
        abort(501, 'PDF export not yet implemented');
    }
}
