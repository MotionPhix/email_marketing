<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BatchHandler extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $request->validate([
      'action' => 'required|string|in:delete,export_pdf,export_excel,export_csv',
      'recipients' => 'required|array',
      'recipients.*' => 'integer|exists:recipients,id',
    ]);

    $action = $request->action;
    $recipients = Recipient::whereIn('id', $request->recipients)->get();

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
        return response()->json(['message' => 'Invalid action'], Response::HTTP_BAD_REQUEST);
    }
  }

  private function deleteRecipients($recipients)
  {
    $recipients->each->delete();
    return response()->json(['message' => 'Recipients deleted successfully']);
  }

  private function exportToPdf($recipients)
  {
    // Use a package like dompdf to generate the PDF
    $pdf = \PDF::loadView('exports.recipients', ['recipients' => $recipients]);
    return $pdf->download('recipients.pdf');
  }

  private function exportToExcel($recipients)
  {
    // Use Laravel-Excel package
    return \Excel::download(new RecipientsExport($recipients), 'recipients.xlsx');
  }

  private function exportToCsv($recipients)
  {
    // Use Laravel-Excel package
    return \Excel::download(new RecipientsExport($recipients), 'recipients.csv');
  }
}
