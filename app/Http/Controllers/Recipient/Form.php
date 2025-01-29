<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Form extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Recipient $recipient = null, bool $useModal = false)
  {
    return Inertia::modal($request->query->has('modal') ? 'Recipients/QuickForm' : 'Recipients/Form', [
      'recipient' => $recipient ?: new Recipient()
    ])->baseRoute('recipients.index');
  }
}
