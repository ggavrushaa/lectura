<?php

namespace App\Http\Controllers;

use App\Enums\DetailLevel;
use App\Enums\LectureStatus;
use App\Http\Requests\StoreLectureRequest;
use App\Jobs\ProcessLectureJob;
use App\Models\Lecture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LectureController extends Controller
{
    public function index(Request $request): View
    {
        $lectures = $request->user()->lectures()->latest()->get();

        return view('dashboard', ['lectures' => $lectures]);
    }

    public function create(): View
    {
        return view('lectures.create');
    }

    public function store(StoreLectureRequest $request): RedirectResponse
    {
        $user = $request->user();

        $active = $user->lectures()
            ->whereIn('status', ['pending', 'transcribing', 'summarizing', 'rendering'])
            ->count();
        if ($active >= config('lectura.max_active_per_user')) {
            return back()->withErrors(['audio' => 'Слишком много лекций в обработке. Дождитесь завершения.']);
        }

        $file = $request->file('audio');
        $original = $file->getClientOriginalName();

        $lecture = $user->lectures()->create([
            'title' => pathinfo($original, PATHINFO_FILENAME),
            'original_filename' => $original,
            'status' => LectureStatus::Pending,
            'language' => 'ru',
            'with_diagrams' => $request->boolean('with_diagrams', true),
            'detail_level' => DetailLevel::from($request->input('detail_level', 'medium')),
        ]);

        $path = $file->storeAs("lectures/{$lecture->id}", 'source.'.$file->getClientOriginalExtension(), 'local');
        $lecture->update(['audio_path' => $path]);

        ProcessLectureJob::dispatch($lecture->id);

        return redirect("/lectures/{$lecture->id}");
    }

    public function show(Lecture $lecture): View
    {
        $this->authorize('view', $lecture);

        return view('lectures.show', ['lecture' => $lecture]);
    }

    public function status(Lecture $lecture)
    {
        $this->authorize('view', $lecture);

        return response()->json([
            'status' => $lecture->status->value,
            'label' => $lecture->status->label(),
            'progress' => $lecture->progress,
            'error' => $lecture->error_message,
        ]);
    }

    public function destroy(Lecture $lecture): RedirectResponse
    {
        $this->authorize('delete', $lecture);
        $lecture->delete();

        return redirect('/dashboard');
    }

    public function retry(Lecture $lecture): RedirectResponse
    {
        $this->authorize('view', $lecture);
        $lecture->update([
            'status' => LectureStatus::Pending,
            'progress' => 0,
            'error_message' => null,
        ]);
        ProcessLectureJob::dispatch($lecture->id);

        return redirect("/lectures/{$lecture->id}");
    }
}
