<?php
namespace ntentan\exceptions;

use \Exception;

class NtentanException extends Exception
{
    private string $detailedDescription = '';

    public function getDetailedDescription(): string
    {
        return $this->detailedDescription;
    }

    protected function setDetailedDescription(string $detailedDescription): NtentanException
    {
        $this->detailedDescription = $detailedDescription;
        return $this;
    }
}
