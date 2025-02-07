<?php

namespace App\Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Services\PayChanguService;
use Illuminate\Http\Request;

class PayChanguWebhook extends Controller
{
  public function __construct(
    protected PayChanguService $paychanguService
  ) {}

  public function handle(Request $request)
  {
    try {
      $this->paychanguService->handleWebhook($request->all());
      return response()->noContent();
    } catch (\Exception $e) {
      report($e);
      return response()->json(['error' => $e->getMessage()], 400);
    }
  }
}
