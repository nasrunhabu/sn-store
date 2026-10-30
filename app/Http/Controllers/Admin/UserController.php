<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(UserService $userService): View
    {
        return view('pages.admin.users.index', [
            'users' => $userService->getUsers(),
        ]);
    }
}
