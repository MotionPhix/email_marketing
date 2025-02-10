<?php

namespace App\Modules\Home\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class Index extends Controller
{
  public function __invoke()
  {
    return Inertia::render('Home/Index');
  }
}
