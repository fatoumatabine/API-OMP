<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Orange Money API",
 *     version="1.0.0",
 *     description="API pour les services financiers mobile",
 *     @OA\Contact(
 *         email="support@orangemoney.com"
 *     )
 * )
 * @OA\Server(
 *     url="https://ompay-4mgy.onrender.com",
 *     description="Serveur de production"
 * )
  * @OA\Server(
  *     url="http://localhost:8000",
  *     description="Serveur local"
  * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
