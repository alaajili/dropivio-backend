<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ 

namespace App\Service\Authorization;

use App\Entity\Product;
use App\Entity\User;

interface ProductAuthorizationServiceInterface
{
    public function checkUpdatePermission(?Product $product, User $user): void;
    public function checkDeletePermission(?Product $product, User $user): void;
}
