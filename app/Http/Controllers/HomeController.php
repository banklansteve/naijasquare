<?php

namespace App\Http\Controllers;

use App\Home\BuildHomePage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request, BuildHomePage $home): Response
    {
        return Inertia::render('Home', $home->handle($request->user(), $request));
    }
}
