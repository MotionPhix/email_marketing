<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Imports\RecipientsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class Import extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $request->validate([
      'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
    ]);

    Excel::import(new RecipientsImport, $request->file('file'));

    return back();
  }
}
