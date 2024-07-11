<?php

namespace App\Exceptions;

use Exception;

class RowImportException extends Exception
{
    public $rowNumber;
    public $column;
    public $sheet;
    
    public function __construct($message, $rowNumber, $column = null, $sheet = null)
    {
        parent::__construct($message);
        $this->rowNumber = $rowNumber;
        $this->column = $column;
        $this->sheet = $sheet;
    }

    public function getRowNumber()
    {
        return $this->rowNumber;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getSheet()
    {
        return $this->sheet;
    }
}
