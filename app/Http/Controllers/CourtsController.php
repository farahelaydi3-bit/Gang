<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Courts;
use App\Models\Sports;

class CourtsController extends Controller
{
    public function index(){
        $courts = Courts::with(['sports'])->get();
        // return view('courts.index', compact('courts'));
    }
}
