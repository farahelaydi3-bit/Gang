<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sports;
use App\Models\Courts;
class SportsController extends Controller
{

public function index(){

   $sports = Sports::all();

    // return view('pages.home',compact('sports'));
}
}
