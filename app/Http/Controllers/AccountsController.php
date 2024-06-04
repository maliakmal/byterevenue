<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AccountsController extends Controller
{
    public function index()
    {
        $accounts = User::select()->orderby('name', 'asc')->paginate(5);

        return view('accounts.index', compact('accounts'));
    }

}
