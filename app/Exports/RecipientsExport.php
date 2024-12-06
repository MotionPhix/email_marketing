<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class RecipientsExport implements FromCollection
{
  public function __construct(protected Collection $recipients) { }

  public function collection()
  {
    return $this->recipients->map(fn($recipient) => [
      'id' => $recipient->id,
      'name' => $recipient->name,
      'email' => $recipient->email,
    ]);
  }
}
