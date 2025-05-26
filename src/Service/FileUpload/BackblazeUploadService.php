<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\FileUpload;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

class BackblazeUploadService implements FileUploadServiceInterface
{
    public function __construct(
        private readonly string $bucketName,
        private readonly string $endpoint,
        private readonly SluggerInterface $slugger,
        private readonly LoggerInterface $logger,
        private readonly S3Client $s3Client,
    ) {
    }

    public function upload(UploadedFile $file, string $directory = ''): string
    {
        try {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
            
            $key = $directory ? rtrim($directory, '/') . '/' . $fileName : $fileName;

            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'Body' => fopen($file->getPathname(), 'r'),
                'ContentType' => $file->getMimeType(),
                'ACL' => 'public-read',
                'Metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                ],
            ]);

            $this->logger->info('File uploaded successfully', [
                'bucket' => $this->bucketName,
                'key' => $key,
                'original_name' => $file->getClientOriginalName(),
            ]);

            return $this->getPublicUrl($key);

        } catch (AwsException $e) {
            $this->logger->error('Failed to upload file to Backblaze', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            
            throw new \RuntimeException('Failed to upload file: ' . $e->getMessage(), 0, $e);
        }
    }

    public function delete(string $key): bool
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            $this->logger->info('File deleted successfully', [
                'bucket' => $this->bucketName,
                'key' => $key,
            ]);

            return true;

        } catch (AwsException $e) {
            $this->logger->error('Failed to delete file from Backblaze', [
                'error' => $e->getMessage(),
                'key' => $key,
            ]);
            
            return false;
        }
    }

    public function exists(string $key): bool
    {
        try {
            return $this->s3Client->doesObjectExist($this->bucketName, $key);
        } catch (AwsException $e) {
            $this->logger->error('Failed to check file existence', [
                'error' => $e->getMessage(),
                'key' => $key,
            ]);
            
            return false;
        }
    }

    public function getPublicUrl(string $key): string
    {
        // Extract key from full URL if provided
        if (filter_var($key, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($key);
            $key = ltrim($parsedUrl['path'], '/');
        }

        return sprintf('https://%s.%s/%s', 
            $this->bucketName, 
            str_replace(['https://', 'http://'], '', $this->endpoint),
            $key
        );
    }

    /**
     * Extract the S3 key from a full URL
     */
    public function extractKeyFromUrl(string $url): ?string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parsedUrl = parse_url($url);
        return ltrim($parsedUrl['path'], '/');
    }
}
