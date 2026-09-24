<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class PlayingController extends Controller
{
    use ResponseTrait;

    /**
     * Playing is here
     */
    public function playing(Request $request)
    {
        return $this->success200(null, 'Playing');
    }
}
