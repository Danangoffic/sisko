<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Announcement::with('author:id,name')
            ->whereNotNull('published_at');

        if ($user->role->value !== 'admin') {
            $query->where(function ($q) use ($user): void {
                $q->where('target_role', 'all')
                    ->orWhere('target_role', $user->role->value);
            });
        }

        $announcements = $query
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate(15);

        return Inertia::render('announcements/index', [
            'announcements' => $announcements,
            'canManage' => $user->role->value === 'admin',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'target_role' => ['required', Rule::in(['all', 'guru', 'siswa'])],
            'is_pinned' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $validated['author_id'] = $request->user()->id;
        $validated['published_at'] ??= now();

        Announcement::create($validated);

        return back()->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'target_role' => ['required', Rule::in(['all', 'guru', 'siswa'])],
            'is_pinned' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $announcement->update($validated);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
