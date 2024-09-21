<?php

namespace App\Modules\Absolute\Controller;

use App\Modules\Absolute\Job\AbsoluteParserJob;

class AbsoluteController
{
    public function index()
    {
        //AbsoluteParserJob::dispatch(7242, 3, 1);
        dd('test');
        return view('welcome');
    }
}
