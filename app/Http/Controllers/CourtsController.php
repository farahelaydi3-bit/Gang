<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Court;
use App\Models\Sports;

class CourtsController extends Controller
{
    public function index(){
       $courts = Court::with(['sports']);
        // return view('pages.home', compact('courts'));
    }
}
