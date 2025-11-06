<?php

namespace Notigen\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Notigen\Models\NotificationTemplate;

class NotigenController extends Controller
{
    public function index()
    {
        $templates = NotificationTemplate::latest()->get();
        return view('notigen::templates.index', compact('templates'));
    }

    public function create()
    {
        $channels = config('notigen.default_channels', ['mail']);
        return view('notigen::templates.create', compact('channels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'channels' => 'required|array',
            'channels.*' => 'string|in:' . implode(',', config('notigen.default_channels', ['mail'])),
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'variables.*.name' => 'required|string',
            'variables.*.description' => 'nullable|string',
        ]);

        $template = NotificationTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'channels' => $validated['channels'],
            'content' => $validated['content'],
            'variables' => collect($validated['variables'] ?? [])->keyBy('name')->toArray(),
        ]);

        return redirect()
            ->route('notigen.index')
            ->with('success', 'Template created successfully.');
    }

    public function show($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        return view('notigen::templates.show', compact('template'));
    }

    public function edit($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $channels = config('notigen.default_channels', ['mail']);
        return view('notigen::templates.edit', compact('template', 'channels'));
    }

    public function update(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'channels' => 'required|array',
            'channels.*' => 'string|in:' . implode(',', config('notigen.default_channels', ['mail'])),
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'variables.*.name' => 'required|string',
            'variables.*.description' => 'nullable|string',
        ]);

        $template->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'channels' => $validated['channels'],
            'content' => $validated['content'],
            'variables' => collect($validated['variables'] ?? [])->keyBy('name')->toArray(),
        ]);

        return redirect()
            ->route('notigen.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->delete();

        return redirect()
            ->route('notigen.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function preview(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $data = $request->validate([
            'variables' => 'required|array',
        ]);

        return response()->json([
            'content' => $template->renderContent($data['variables'])
        ]);
    }
}
