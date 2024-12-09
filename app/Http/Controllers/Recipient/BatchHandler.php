<?php

namespace App\Http\Controllers\Recipient;

use App\Exports\RecipientsExport;
use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class BatchHandler extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(string $action, $recipients)
  {
    $recipients = collect(explode(',', $recipients))->filter()->map(fn($id) => (int) $id);

    if ($recipients->isEmpty()) {
      return response()->json(['message' => 'No recipients provided'], Response::HTTP_BAD_REQUEST);
    }

    $recipients = Recipient::whereIn('id', $recipients)->get();

    switch ($action) {
      case 'delete':
        return $this->deleteRecipients($recipients);

      case 'export_pdf':
        return $this->exportToPdf($recipients);

      case 'export_excel':
        return $this->exportToExcel($recipients);

      case 'export_csv':
        return $this->exportToCsv($recipients);

      default:
        return $this->editRecipient($recipients);
    }
  }

  private function editRecipient($recipients)
  {
    $recipient = $recipients->first();

    return Inertia('Recipients/Form', [
      'recipient' => $recipient
    ]);
  }

  private function deleteRecipients($recipients)
  {
    $recipients->each->delete();
    $skippedCount = 0;

    $recipients->each(function ($recipient) use (&$skippedCount) {
      if ($recipient->campaigns()->exists()) {
        $skippedCount++;
      } else {
        $recipient->delete();
      }
    });

    return back()->withErrors([
      'message', "Deletion successful, skipped {$skippedCount} recipients"
    ]);
  }

  private function exportToPdf($recipients)
  {
    // Use a package like dompdf to generate the PDF
    $pdf = \PDF::loadView('exports.recipients', ['recipients' => $recipients])
      ->setPaper('a4', 'landscape')->setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    return $pdf->download('recipients.pdf');
  }

  private function exportToExcel($recipients)
  {
    // Use Laravel-Excel package
    return Excel::download(new RecipientsExport($recipients), 'recipients.xlsx');
  }

  private function exportToCsv($recipients)
  {
    // Use Laravel-Excel package
    return Excel::download(new RecipientsExport($recipients),
      'recipients.csv',
      \Maatwebsite\Excel\Excel::CSV
    );
  }
}
