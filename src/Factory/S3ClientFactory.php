<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Factory;

use Aws\S3\S3Client;

class S3ClientFactory
{
    public function create(
        string $keyId,
        string $applicationKey,
        string $region,
        string $endpoint
    ): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $endpoint,
            'credentials' => [
                'key' => $keyId,
                'secret' => $applicationKey,
            ],
            'use_path_style_endpoint' => true,
        ]);
    }
}
