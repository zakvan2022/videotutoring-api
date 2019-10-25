<?php
namespace App\Classes;

class PropertySuccessResponse{
    public $value;
    public $key;

    public function __construct($key, $value){
        $this->value = $value;
        $this->key = $key;
    }
}

class SuccessResponse{
    public $message;
    public $code;
    public $data = [];

    public function __construct($message, $code, $data){
        $this->message = $message;
        $this->code = $code;
        $this->data = $data;
    }
}

class PropertyErrorResponse{
    public $message;
    public $propertyName;

    public function __construct($propertyName, $message){
        $this->propertyName = $propertyName;
        $this->message = $message;
    }
}

class ErrorResponse{
    public $message;
    public $code;
    public $modelErrors = [];

    public function __construct($message, $code, $modelErrors){
        $this->message = $message;
        $this->code = $code;
        $this->modelErrors = $modelErrors;
    }
}