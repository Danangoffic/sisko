<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SchoolClassController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('school-class/index', [
            'classes' => SchoolClass::withCount('students')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:school_classes',
            'homeroom_teacher' => 'required|string|max:100',
        ]);

        SchoolClass::create($validated);

        return back();
    }

    public function destroy(SchoolClass $school_class): RedirectResponse
    {
        $school_class->delete();

        return back();
    }
}
