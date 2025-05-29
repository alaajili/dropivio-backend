<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Exception\Handler; 

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ControllerExceptionHandler implements ControllerExceptionHandlerInterface
{
    public function handle(\Exception $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof NotFoundHttpException => 
                new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND),
            $exception instanceof AccessDeniedHttpException =>
                new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN),
            $exception instanceof BadRequestHttpException => 
                new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST),
            default => new JsonResponse(
                ['error' => 'An error occurred while processing the request', 'details' => $exception->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            )
        };
    }
}
