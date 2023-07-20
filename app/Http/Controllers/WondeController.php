<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WondeController extends Controller
{
    public function show(\Wonde\Client $client) {

        
        dd($client->schools->all());
        return view('wonde');
    }
}
