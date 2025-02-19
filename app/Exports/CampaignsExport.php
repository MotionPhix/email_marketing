<?php

namespace App\Exports;

use App\Models\Campaign;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CampaignsExport implements FromQuery, WithHeadings, WithMapping
{
  protected $filters;

  public function __construct(array $filters)
  {
    $this->filters = $filters;
  }

  public function query()
  {
    return Campaign::query()
      ->when($this->filters['search'] ?? null, function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('name', 'like', "%{$search}%")
            ->orWhere('subject', 'like', "%{$search}%")
            ->orWhere('from_name', 'like', "%{$search}%")
            ->orWhere('from_email', 'like', "%{$search}%");
        });
      })
      ->when($this->filters['status'] ?? null, function ($query, $status) {
        $query->where('status', $status);
      })
      ->when($this->filters['date_from'] ?? null, function ($query, $date) {
        $query->whereDate('created_at', '>=', $date);
      })
      ->when($this->filters['date_to'] ?? null, function ($query, $date) {
        $query->whereDate('created_at', '<=', $date);
      })
      ->when($this->filters['sort_by'] ?? null, function ($query, $sortBy) {
        $direction = $this->filters['sort_direction'] === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $direction);
      }, function ($query) {
        $query->latest('id');
      });
  }

  public function headings(): array
  {
    return [
      'Name',
      'Subject',
      'From Name',
      'From Email',
      'Status',
      'Recipients',
      'Created At',
      'Scheduled At',
      'Sent At',
    ];
  }

  public function map($row): array
  {
    return [
      $row->name,
      $row->subject,
      $row->from_name,
      $row->from_email,
      $row->status,
      $row->recipient_count,
      $row->created_at->format('Y-m-d H:i:s'),
      $row->scheduled_at?->format('Y-m-d H:i:s'),
      $row->sent_at?->format('Y-m-d H:i:s'),
    ];
  }
}
