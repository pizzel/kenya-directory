<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For $this->authorize()
use Illuminate\Foundation\Validation\ValidatesRequests;   // For $this->validate()
use Illuminate\Routing\Controller as BaseController;      // The actual base controller from Laravel

abstract class Controller extends BaseController // It should extend BaseController
{
    use AuthorizesRequests, ValidatesRequests; // Use the traits here
}