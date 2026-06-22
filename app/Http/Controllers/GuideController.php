<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class GuideController extends Controller
{
    public function index()
    {
        return Inertia::render('Guide/Index');
    }
}
