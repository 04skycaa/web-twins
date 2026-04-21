<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the attendance.
     */
    public function index()
    {
        return view('absensi.index');
    }
}
