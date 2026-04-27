<?php

namespace App\Http\Controllers;

use App\Models\TeamMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        $messages = TeamMessage::with('user')
            ->orderBy('created_at')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'messages' => $messages->map(fn (TeamMessage $m) => [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'user_name' => $m->user->name ?? '—',
                    'body' => $m->body,
                    'attachment_url' => $m->attachment_path ? Storage::url($m->attachment_path) : null,
                    'attachment_name' => $m->attachment_name,
                    'created_at' => $m->created_at->toIso8601String(),
                ]),
            ]);
        }

        $request->user()->update(['last_chat_read_at' => now()]);

        return view('chat.index', ['messages' => $messages]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'image', 'max:10240'], // 10 MB
        ]);

        $body = $request->filled('body') ? trim($request->input('body')) : null;
        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat-attachments', 'public');
            if ($path) {
                $attachmentPath = $path;
                $attachmentName = $file->getClientOriginalName();
            }
        }

        if (! $body && ! $attachmentPath) {
            return back()->withErrors(['body' => 'Ajoutez un message ou une image.'])->withInput();
        }

        TeamMessage::create([
            'user_id' => $request->user()->id,
            'body' => $body,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        return redirect()->route('chat.index')->with('success', 'Message envoyé.');
    }
}
