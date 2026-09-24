<?php

namespace App\Http\Controllers;

use App\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LeadMax\TrackYourStats\System\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('announcements.index', [
            'announcements' => Announcement::query()
                ->orderByDesc('is_pinned')
                ->orderByDesc('created_at')->orderByDesc('id')
                ->get(),
            'pageTitle' => 'Announcements',
        ]);
    }

    public function create()
    {
        return view('announcements.create', [
            'types' => Announcement::TYPES,
            'pageTitle' => 'New Announcement',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateAnnouncement($request);

        $storedPath = null;
        try {
            $announcement = DB::transaction(function () use ($request, $data, &$storedPath) {
                $attributes = $this->attributes($request, $data) + [
                    'author_id' => (int) Session::userID(),
                ];

                if ($request->hasFile('attachment')) {
                    $attributes += $this->storeAttachment($request, $storedPath);
                }

                return Announcement::query()->create($attributes);
            });
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            report($exception);
            return back()->withInput()->withErrors(['announcement' => 'The announcement could not be saved. Please try again.']);
        }

        return redirect()->route('announcements.index')->with('announcement_saved', 'Announcement posted successfully.');
    }

    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', [
            'announcement' => $announcement,
            'types' => Announcement::TYPES,
            'pageTitle' => 'Edit Announcement',
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $this->validateAnnouncement($request, true);
        $storedPath = null;
        $oldDisk = $announcement->attachment_disk;
        $oldPath = $announcement->attachment_path;
        $replaceAttachment = $request->hasFile('attachment');
        $removeAttachment = $request->boolean('remove_attachment');

        try {
            DB::transaction(function () use ($request, $data, $announcement, &$storedPath, $replaceAttachment, $removeAttachment) {
                $attributes = $this->attributes($request, $data);
                if ($replaceAttachment) {
                    $attributes += $this->storeAttachment($request, $storedPath);
                } elseif ($removeAttachment) {
                    $attributes += $this->emptyAttachment();
                }
                $announcement->update($attributes);
            });
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            report($exception);
            return back()->withInput()->withErrors(['announcement' => 'The announcement could not be updated. Please try again.']);
        }

        if (($replaceAttachment || $removeAttachment) && $oldDisk && $oldPath) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        return redirect()->route('announcements.index')->with('announcement_saved', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $disk = $announcement->attachment_disk;
        $path = $announcement->attachment_path;
        try {
            DB::transaction(fn () => $announcement->delete());
            if ($disk && $path) {
                Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['announcement' => 'The announcement could not be deleted. Please try again.']);
        }

        return redirect()->route('announcements.index')->with('announcement_saved', 'Announcement deleted successfully.');
    }

    public function download(Announcement $announcement): StreamedResponse
    {
        abort_unless($announcement->hasAttachment(), 404);
        $disk = Storage::disk($announcement->attachment_disk);
        abort_unless($disk->exists($announcement->attachment_path), 404);

        return $disk->download(
            $announcement->attachment_path,
            $announcement->attachment_name,
            ['Content-Type' => $announcement->attachment_mime ?: 'application/octet-stream']
        );
    }

    private function validateAnnouncement(Request $request, bool $editing = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(Announcement::TYPES)],
            'body' => ['required', 'string', 'max:10000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'is_pinned' => ['nullable', 'boolean'],
            'remove_attachment' => [$editing ? 'nullable' : 'prohibited', 'boolean'],
        ]);
    }

    private function attributes(Request $request, array $data): array
    {
        return [
            'title' => $data['title'],
            'type' => $data['type'],
            'body' => $data['body'],
            'is_pinned' => $request->boolean('is_pinned'),
        ];
    }

    private function storeAttachment(Request $request, ?string &$storedPath): array
    {
        $file = $request->file('attachment');
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $filename = (string) Str::uuid().($extension ? '.'.preg_replace('/[^a-z0-9]/', '', $extension) : '');
        $storedPath = $file->storeAs('announcements', $filename, 'local');
        if (!$storedPath) {
            throw new \RuntimeException('Unable to store attachment.');
        }
        $original = trim(preg_replace('/[\x00-\x1F\x7F]+/', '', basename($file->getClientOriginalName())));

        return [
            'attachment_disk' => 'local',
            'attachment_path' => $storedPath,
            'attachment_name' => Str::limit($original ?: 'attachment', 240, ''),
            'attachment_mime' => Str::limit((string) $file->getMimeType(), 150, ''),
            'attachment_size' => $file->getSize(),
        ];
    }

    private function emptyAttachment(): array
    {
        return [
            'attachment_disk' => null,
            'attachment_path' => null,
            'attachment_name' => null,
            'attachment_mime' => null,
            'attachment_size' => null,
        ];
    }
}
