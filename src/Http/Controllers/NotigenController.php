<?php

namespace Notigen\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Notigen\Models\NotificationTemplate;
use Illuminate\Support\Str;

class NotigenController extends Controller
{
    /**
     * Display a listing of notification templates.
     */
    public function index(): View
    {
        $templates = NotificationTemplate::latest()->paginate(10);
        return view('notigen::templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(): View
    {
        $channels = config('notigen.default_channels', ['mail']);
        return view('notigen::templates.create', compact('channels'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'template_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_-]+$/',
                'unique:notification_templates,template_key'
            ],
            'description' => ['nullable', 'string'],
            'channels' => ['required', 'array'],
            'channels.*' => [
                'string',
                'in:' . implode(',', config('notigen.default_channels', ['mail']))
            ],
            'content' => ['required', 'string'],
            'subject' => ['nullable', 'string', 'max:255'],
            'variables' => ['nullable', 'array'],
            'variables.*.name' => [
                'required',
                'string',
                'regex:/^[a-z][a-z0-9_]*$/'
            ],
            'variables.*.description' => ['nullable', 'string'],
            'variables.*.required' => ['nullable', 'boolean'],
        ]);

        $template = NotificationTemplate::create([
            'name' => $validated['name'],
            'template_key' => $validated['template_key'],
            'description' => $validated['description'],
            'channels' => $validated['channels'],
            'content' => $validated['content'],
            'variables' => collect($validated['variables'] ?? [])->keyBy('name')->toArray(),
        ]);

        return redirect()
            ->route('notigen.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template.
     */
    public function show(int $id): View
    {
        $template = NotificationTemplate::findOrFail($id);
        return view('notigen::templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(int $id): View
    {
        $template = NotificationTemplate::findOrFail($id);
        $channels = config('notigen.default_channels', ['mail']);
        return view('notigen::templates.edit', compact('template', 'channels'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $template = NotificationTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'template_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_-]+$/',
                function ($attribute, $value, $fail) use ($template) {
                    if (NotificationTemplate::where('template_key', $value)
                        ->where('id', '!=', $template->id)
                        ->exists()) {
                        $fail('The template key has already been taken.');
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'channels' => ['required', 'array'],
            'channels.*' => [
                'string',
                'in:' . implode(',', config('notigen.default_channels', ['mail']))
            ],
            'content' => ['required', 'string'],
            'subject' => ['nullable', 'string', 'max:255'],
            'variables' => ['nullable', 'array'],
            'variables.*.name' => [
                'required',
                'string',
                'regex:/^[a-z][a-z0-9_]*$/'
            ],
            'variables.*.description' => ['nullable', 'string'],
            'variables.*.required' => ['nullable', 'boolean'],
        ]);

        $template->update([
            'name' => $validated['name'],
            'template_key' => $validated['template_key'],
            'description' => $validated['description'],
            'channels' => $validated['channels'],
            'content' => $validated['content'],
            'variables' => collect($validated['variables'] ?? [])->keyBy('name')->toArray(),
        ]);

        return redirect()
            ->route('notigen.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(int $id): RedirectResponse
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->delete();

        return redirect()
            ->route('notigen.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Preview template with test data.
     */
    public function preview(Request $request, int $id): JsonResponse
    {
        $template = NotificationTemplate::findOrFail($id);
        
        try {
            $data = $request->validate([
                'variables' => ['required', 'array'],
                'variables.*' => ['required'],
            ]);

            $content = $template->renderContent($data['variables']);

            return response()->json([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generate a unique template key based on the template name.
     */
    protected function generateUniqueTemplateKey(string $name): string
    {
        $baseKey = Str::slug($name);
        $timestamp = now()->format('YmdHis');
        $randomStr = strtoupper(Str::random(6));
        
        $templateKey = "{$baseKey}_{$timestamp}_{$randomStr}";
        
        while (NotificationTemplate::where('template_key', $templateKey)->exists()) {
            $randomStr = strtoupper(Str::random(6));
            $templateKey = "{$baseKey}_{$timestamp}_{$randomStr}";
        }
        
        return $templateKey;
    }

    /**
     * Check if a template key is available.
     */
    public function checkKey(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key' => ['required', 'string', 'regex:/^[a-z0-9_-]+$/'],
                'id' => ['nullable', 'integer', 'exists:notification_templates,id'],
            ]);

            $query = NotificationTemplate::where('template_key', $validated['key']);
            
            if (!empty($validated['id'])) {
                $query->where('id', '!=', $validated['id']);
            }

            $exists = $query->exists();

            return response()->json([
                'success' => true,
                'available' => !$exists,
                'key' => $validated['key'],
                'message' => $exists ? 'This template key is already in use.' : 'This template key is available.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
