<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class SendGridException extends Exception
{
  protected $errors;

  public function __construct($message = '', $errors = [], $code = 0, Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
    $this->errors = $errors;
  }

  /**
   * Get the SendGrid API errors.
   *
   * @return array
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Report or log the exception.
   *
   * @return bool
   */
  public function report()
  {
    Log::error('SendGrid API Error', [
      'message' => $this->getMessage(),
      'errors' => $this->getErrors(),
      'code' => $this->getCode(),
    ]);

    return false;
  }

  /**
   * Render the exception into an HTTP response.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function render($request)
  {
    if ($request->expectsJson()) {
      return response()->json([
        'error' => $this->getMessage(),
        'details' => $this->getErrors(),
      ], 422);
    }

    return back()->withErrors([
      'sendgrid' => $this->getMessage(),
    ])->withInput();
  }
}
