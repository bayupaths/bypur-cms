<?php

namespace App\Http\Controllers\Api;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;

abstract class ApiController extends Controller
{
    use ApiResponse;
}
