<?php

namespace App\Service\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use App\Service\FileUpload\FileUploadServiceInterface;

class CachedThumbnailService
{
    private const CACHE_PREFIX = 'thumbnail_url_';
    private const CACHE_TTL = 86400; // 24 hours
    private const URL_EXPIRES_IN = 604800; // 7 days

    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getThumbnailUrl(string $thumbnailKey): string
    {
        $cacheKey = self::CACHE_PREFIX . md5($thumbnailKey);
        
        try {
            $cacheItem = $this->cache->getItem($cacheKey);
            
            if ($cacheItem->isHit()) {
                $this->logger->debug('Thumbnail URL cache hit', ['key' => $thumbnailKey]);
                return $cacheItem->get();
            }
            
            // Generate new pre-signed URL
            $presignedUrl = $this->fileUploadService->getPresignedUrl(
                $thumbnailKey, 
                self::URL_EXPIRES_IN
            );
            
            // Cache the URL (expires before the actual URL expires)
            $cacheItem->set($presignedUrl);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);
            
            $this->logger->debug('Thumbnail URL generated and cached', ['key' => $thumbnailKey]);
            
            return $presignedUrl;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get cached thumbnail URL', [
                'key' => $thumbnailKey,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to direct generation
            return $this->fileUploadService->getPresignedUrl($thumbnailKey, self::URL_EXPIRES_IN);
        }
    }

    public function invalidateThumbnailCache(string $thumbnailKey): void
    {
        $cacheKey = self::CACHE_PREFIX . md5($thumbnailKey);
        $this->cache->deleteItem($cacheKey);
    }

    public function warmupThumbnailCache(array $thumbnailKeys): void
    {
        foreach ($thumbnailKeys as $key) {
            $this->getThumbnailUrl($key);
        }
    }
}
