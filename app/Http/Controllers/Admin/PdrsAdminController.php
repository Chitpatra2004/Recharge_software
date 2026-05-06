<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PdrsAdminController extends Controller
{
    /**
     * PDRS Admin Dashboard
     */
    public function index()
    {
        return view('admin.pdrs.index');
    }
}
