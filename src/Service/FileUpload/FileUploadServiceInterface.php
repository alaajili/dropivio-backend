<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\FileUpload;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileUploadServiceInterface
{
    /**
     * Upload a file and return the public URL
     */
    public function upload(UploadedFile $file, string $directory = ''): string;

    /**
     * Delete a file by its key/path
     */
    public function delete(string $key): bool;

    /**
     * Check if a file exists
     */
    public function exists(string $key): bool;

    /**
     * Get the public URL for a file
     */
    public function getPublicUrl(string $key): string;

    /**
     * Get a pre-signed URL for a file
     */
    public function getPresignedUrl(string $key, int $expiresInSeconds = 604800): string;
}
