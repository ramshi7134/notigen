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
            'template_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_-]+$/',
                'unique:notification_templates,template_key'
            ],
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
            'template_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_-]+$/',
                function ($attribute, $value, $fail) use ($template) {
                    // Check if key is unique except for current template
                    if (NotificationTemplate::where('template_key', $value)
                        ->where('id', '!=', $template->id)
                        ->exists()) {
                        $fail('The template key has already been taken.');
                    }
                },
            ],
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

    /**
     * Generate a unique template key based on the template name
     *
     * @param string $name
     * @return string
     */
    protected function generateUniqueTemplateKey(string $name): string
    {
        // Convert name to slug
        $baseKey = str_slug($name);
        
        // Add timestamp and random string
        $timestamp = now()->format('YmdHis');
        $randomStr = strtoupper(str_random(6));
        
        // Combine all parts to create a unique key
        $templateKey = "{$baseKey}_{$timestamp}_{$randomStr}";
        
        // Verify uniqueness
        while (NotificationTemplate::where('template_key', $templateKey)->exists()) {
            $randomStr = strtoupper(str_random(6));
            $templateKey = "{$baseKey}_{$timestamp}_{$randomStr}";
        }
        
        return $templateKey;
    }

    /**
     * Check if a template key is available
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkKey(Request $request)
    {
        try {
            $key = $request->input('key');
            $id = $request->input('id');

            if (empty($key)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Template key is required'
                ], 400);
            }

            // Validate key format
            if (!preg_match('/^[a-z0-9_-]+$/', $key)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Invalid key format'
                ], 400);
            }

            $query = NotificationTemplate::where('template_key', $key);
            
            // Exclude current template when checking
            if ($id) {
                $query->where('id', '!=', $id);
            }

            $exists = $query->exists();

            return response()->json([
                'available' => !$exists,
                'key' => $key,
                'message' => $exists ? 'Key is already in use' : 'Key is available'
            ]);
    }
}
