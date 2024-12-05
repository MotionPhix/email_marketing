<?php

namespace App\Imports;

use App\Models\Recipient;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RecipientsImport implements ToCollection, WithHeadingRow, ToModel
{
  /**
   * @param array $row
   *
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  public function model(array $row)
  {
    // Avoid duplicates by checking email
    if (Recipient::where('email', $row['email'])->exists()) {
      return null; // Skip if email already exists
    }

    return new Recipient([
      'name' => $row['name'],
      'email' => $row['email'],
      'status' => $row['status'] ?? 'active', // Default to active if not provided
      'gender' => $row['gender'] ?? 'unspecified',
      'user_id' => auth()->id(),
    ]);
  }

  public function collection(Collection $collection)
  {
    foreach ($collection as $row) {
      Recipient::updateOrCreate(
        ['email' => $row['email']], // Unique constraint
        [
          'name' => $row['name'] ?? null,
          'status' => $row['status'] ?? 'active',
          'gender' => $row['gender'] ?? 'unspecified',
          'user_id' => auth()->id(),
        ]
      );
    }
  }
}
