<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('id')->get();

        return view('home', compact('projects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:projects,name',
            'description' => 'nullable|string|max:200',
        ]);
        $data['slug'] = Str::slug($data['name']);

        Project::create($data);

        return redirect('/')->with('ok', 'Project ditambahkan.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect('/')->with('ok', 'Project dihapus.');
    }
}