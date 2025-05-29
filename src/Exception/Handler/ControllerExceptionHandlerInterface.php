<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Exception\Handler;

use Symfony\Component\HttpFoundation\JsonResponse;

interface ControllerExceptionHandlerInterface
{
    public function handle(\Exception $exception): JsonResponse;
}
