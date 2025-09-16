<?php
namespace Klassroom\helpers;

class ResponseHelper {
    public static function defaultResponse(): array {
        return [
            "state" => 0,
            "message" => ""
        ];
    }
} 
