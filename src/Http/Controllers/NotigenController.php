<?php

namespace Notigen\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotigenController extends Controller
{
    public function index()
    {
        return view('notigen::templates.index');
    }

    public function create()
    {
        return view('notigen::templates.create');
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
