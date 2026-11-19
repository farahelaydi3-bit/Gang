<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sports;
class SportsController extends Controller
{

public function index(){

    $sports = Sports::all();
    
    return view('sports.index',compact('sports'));
}
}
