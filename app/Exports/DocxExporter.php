<?php

namespace App\Exports;

use App\Models\Lecture;

class DocxExporter
{
    public function download(Lecture $lecture)
    {
        abort(501, 'DOCX export not yet implemented');
    }
}
