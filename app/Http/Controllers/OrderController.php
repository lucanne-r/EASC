<?php

namespace App\Http\Controllers;

use App\Models\Project;

class OrderController extends Controller
{
    public function index(Project $project)
    {
        return view('project.order', compact('project'));
    }
}