<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Factory;

use App\Dto\Product\ProductCreateDto;
use App\Dto\Product\ProductUpdateDto;
use Symfony\Component\HttpFoundation\Request;

interface ProductDtoFactoryInterface
{
    public function createFromRequest(Request $request): ProductCreateDto;
    public function createUpdateFromRequest(Request $request): ProductUpdateDto;
}
