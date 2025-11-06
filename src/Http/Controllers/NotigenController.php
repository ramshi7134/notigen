<?php

namespace Notigen\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotigenController extends Controller
{
    public function index()
    {
        $templates = config('notigen.templates', []);
        $templateData = collect($templates)->map(function ($template, $key) {
            return [
                'id' => $key,
                'name' => $template['name'] ?? '',
                'type' => $template['channels'][0] ?? 'mail',
                'created_at' => now()
            ];
        });
        
        return view('notigen::templates.index', compact('templateData'));
    }

    public function create()
    {
        $channels = config('notigen.default_channels', ['mail']);
        return view('notigen::templates.create', compact('channels'));
    }

    public function store(Request $request)
    {
        // Add template storage logic here
    }

    public function show($id)
    {
        // Add template viewing logic here
    }

    public function edit($id)
    {
        // Add template editing logic here
    }

    public function update(Request $request, $id)
    {
        // Add template update logic here
    }

    public function destroy($id)
    {
        // Add template deletion logic here
    }
}
