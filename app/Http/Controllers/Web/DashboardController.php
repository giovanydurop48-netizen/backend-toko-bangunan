<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->role === 'owner') {
            return redirect()->route('owner.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }
}