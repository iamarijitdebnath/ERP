<?php

namespace App\Http\Controllers;

abstract class Controller {
    
    public function apiResponse($status = 200, $message = "", $data = [], $headers = []) {
        return response()->json([
            'status' => $status,
            'message' => $message,
            ($status < 400 ? 'data' : 'errors') => $data,
            'timestamp' => time()
        ], $status, $headers);
    }
}
