<?php

namespace App\Exports;

use App\Models\Recipient;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use phpDocumentor\Reflection\Types\Collection;

class RecipientsExport implements FromView // FromCollection
{
  public function __construct(protected $recipients) { }

  public function view(): View
  {
    return view('exports.recipients', [
      'recipients' => $this->recipients
    ]);
  }
}
