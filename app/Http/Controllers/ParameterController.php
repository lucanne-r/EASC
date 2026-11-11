<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ParameterController extends Controller
{
    public function index(Project $project)
    {
        return view('project.parameter', compact('project'));
    }
}