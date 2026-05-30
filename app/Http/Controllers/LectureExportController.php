<?php

namespace App\Http\Controllers;

use App\Exports\DocxExporter;
use App\Exports\MarkdownExporter;
use App\Exports\PdfExporter;
use App\Models\Lecture;

class LectureExportController extends Controller
{
    public function __invoke(Lecture $lecture, string $format)
    {
        $this->authorize('view', $lecture);

        return match ($format) {
            'md' => app(MarkdownExporter::class)->download($lecture),
            'pdf' => app(PdfExporter::class)->download($lecture),
            'docx' => app(DocxExporter::class)->download($lecture),
            default => abort(404),
        };
    }
}
