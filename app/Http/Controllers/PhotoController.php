<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }

    public function image($fileName){
        return response()->download(public_path().'/image/'.$fileName, 'photo');
    }
}
