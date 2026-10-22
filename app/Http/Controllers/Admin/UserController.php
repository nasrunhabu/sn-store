<?php

namespace App\Http\Controllers\Admin;

use App\DemoStore;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(DemoStore $store): View
    {
        $users = collect($store->all()['users'])->sortBy('name')->values();

        return view('pages.admin.users.index', compact('users'));
    }
}
