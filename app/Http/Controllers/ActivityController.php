<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ActivityController extends Controller
{
    public function index(Project $project)
    {
        return view('project.activity', compact('project'));
    }
}