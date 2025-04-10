<?php declare(strict_types=1);

namespace App\Repositories\Storage\Cached;

use App\Entities\Media;
use Illuminate\Support\Facades\Cache;
use App\Contracts\Interface\Repositories\Storage\MediaStorageRepositoryInterface;
use App\Repositories\Storage\Queries\MediaQueryRepository;
use App\Repositories\Storage\Transactions\MediaTransactionRepository;
use Ramsey\Uuid\UuidInterface;
use Carbon\Carbon;

final class MediaCachedRepository implements MediaStorageRepositoryInterface
{
	/**
     * Cache key for storing all media.
     */
    private const CACHE_MEDIA_ALL_KEY = 'media';

    /**
     * Constructs a new MediaCachedRepository instance.
     *
     * @param MediaQueryRepository $mediaQuery
     * @param MediaTransactionRepository $mediaTransaction
     */
    public function __construct(
        private MediaQueryRepository $mediaQuery,
        private MediaTransactionRepository $mediaTransaction
    ) {}

    /**
     * Retrieves all media using a caching strategy.
     *
     * @return array
     */
    public function all(): array
    {
        return Cache::flexible(
            key: self::CACHE_MEDIA_ALL_KEY,
            ttl: [
                Carbon::now()->addMinutes(value: 5),
                Carbon::now()->addMinutes(value: 15)
            ],
            callback: fn () => $this->mediaQuery->all(),
            lock: ['seconds' => 10]
        );
    }

    /**
     * Finds a media by ID directly from the database.
     *
     * @param \Ramsey\Uuid\UuidInterface $id
     * @return \App\Entities\Media|null
     */
    public function findById(UuidInterface $id): ?Media
    {
        return $this->mediaQuery->findById(id: $id);
    }

    /**
     * Finds media by entity ID directly from the database.
     *
     * @param string $entityId
     * @return array
     */
    public function findByEntityId(string $entityId): array
    {
        return $this->mediaQuery->findByEntityId(
            entityId: $entityId
        );
    }

    /**
     * Saves a media and invalidates cache if necessary.
     *
     * @param \App\Entities\Media $media
     */
    public function save(Media $media): void
    {
        $this->mediaTransaction->save(media: $media);

        if (Cache::has(key: self::CACHE_MEDIA_ALL_KEY)) {
            Cache::forget(key: self::CACHE_MEDIA_ALL_KEY);
        }
    }

    /**
     * Removes a media and invalidates cache if necessary.
     *
     * @param \App\Entities\Media $media
     */
    public function remove(Media $media): void
    {
        $this->mediaTransaction->remove(media: $media);

        if (Cache::has(key: self::CACHE_MEDIA_ALL_KEY)) {
            Cache::forget(key: self::CACHE_MEDIA_ALL_KEY);
        }
    }
}
