<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecipientsExport implements FromCollection, ShouldAutoSize, WithStyles, WithHeadings, WithProperties
{
  public function __construct(protected Collection $recipients) { }

  public function styles(Worksheet $sheet)
  {
    return [
      // Style the first row as bold text.
      'A:D'    => ['font' => ['size' => 12, 'name' => 'Mont']],
      1    => ['font' => ['bold' => true, 'name' => 'Mont Bold']],
    ];
  }

  public function properties(): array
  {
    return [
      'creator'        => 'Ultrashots',
      'lastModifiedBy' => 'Ultrashots',
      'title'          => 'Subscribers Export',
      'description'    => 'My Subscribers List',
      'subject'        => 'Subscribers',
      'keywords'       => 'subscribers,export,spreadsheet',
      'category'       => 'Recipients',
      'manager'        => 'Kingsley Nyirenda',
      'company'        => 'Ultrashots',
    ];
  }

  public function headings(): array
  {
    return [
      'Name',
      'Email',
      'Gender',
      'Status'
    ];
  }

  public function collection()
  {
    return $this->recipients->map(fn($recipient) => [
      'name' => $recipient->name,
      'email' => $recipient->email,
      'gender' => $recipient->gender,
      'status' => $recipient->status,
    ]);
  }
}
