<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenAPI\Attributes as QA;


/**
 * @OA\Info(
 *     title="Blog API",
 *     version="1.0.0",
 *     description="API documentation for Blog API",
 *     @OA\Contact(
 *         name="Likan Shrestha",
 *         email="likan.stha@gmail.com",
 *         url="https://www.yourwebsite.com"
 *     ),
 * )
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
