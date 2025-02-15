<?php

namespace App\Exceptions;

use Exception;

class OnboardingException extends Exception
{
  protected $step;
  protected $errors;

  public function __construct(string $message, int $step = null, array $errors = [])
  {
    parent::__construct($message);
    $this->step = $step;
    $this->errors = $errors;
  }

  public function getStep(): ?int
  {
    return $this->step;
  }

  public function getErrors(): array
  {
    return $this->errors;
  }
}
