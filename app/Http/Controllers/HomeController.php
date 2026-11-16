<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __invoke()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        return redirect()->route(auth()->user()->dashboardRoute());
    }
}
