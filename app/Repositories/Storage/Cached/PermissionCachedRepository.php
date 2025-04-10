<?php declare(strict_types=1);

namespace App\Repositories\Storage\Cached;

use App\Entities\Permission;
use Illuminate\Support\Facades\Cache;
use App\Contracts\Interface\Repositories\Storage\PermissionStorageRepositoryInterface;
use App\Repositories\Storage\Queries\PermissionQueryRepository;
use App\Repositories\Storage\Transactions\PermissionTransactionRepository;
use Ramsey\Uuid\UuidInterface;
use Carbon\Carbon;

final class PermissionCachedRepository implements PermissionStorageRepositoryInterface
{
	/**
     * Cache key for storing all permissions.
     */
    private const CACHE_PERMISSION_ALL_KEY = 'permissions';

    /**
     * Constructs a new PermissionCachedRepository instance.
     *
     * @param PermissionQueryRepository $permissionQuery
     * @param PermissionTransactionRepository $permissionTransaction
     */
    public function __construct(
        private PermissionQueryRepository $permissionQuery,
        private PermissionTransactionRepository $permissionTransaction
    ) {}

    /**
     * Cache key for storing all permissions.
     *
     * @return array
     */
    public function all(): array
    {
        return Cache::flexible(
            key: self::CACHE_PERMISSION_ALL_KEY,
            ttl: [
                Carbon::now()->addMinutes(value: 5),
                Carbon::now()->addMinutes(value: 15)
            ],
            callback: fn () => $this->permissionQuery->all(),
            lock: ['seconds' => 10]
        );
    }

    /**
     * Finds a permission by ID directly from the database.
     *
     * @param \Ramsey\Uuid\UuidInterface $id
     * @return \App\Entities\Permission|null
     */
    public function findById(UuidInterface $id): ?Permission
    {
        return $this->permissionQuery->findById(id: $id);
    }

    /**
     * Finds a permission by slug directly from the database.
     *
     * @param string $slug
     * @return \App\Entities\Permission|null
     */
    public function findBySlug(string $slug): ?Permission
    {
        return $this->permissionQuery->findBySlug(slug: $slug);
    }

    /**
     * Saves a permission and invalidates cache if necessary.
     *
     * @param \App\Entities\Permission $permission
     */
    public function save(Permission $permission): void
    {
        $this->permissionTransaction->save(permission: $permission);

        if (Cache::has(key: self::CACHE_PERMISSION_ALL_KEY)) {
            Cache::forget(key: self::CACHE_PERMISSION_ALL_KEY);
        }
    }

    /**
     * Removes a permission and invalidates cache if necessary.
     *
     * @param \App\Entities\Permission $permission
     */
    public function remove(Permission $permission): void
    {
        $this->permissionTransaction->remove(permission: $permission);

        if (Cache::has(key: self::CACHE_PERMISSION_ALL_KEY)) {
            Cache::forget(key: self::CACHE_PERMISSION_ALL_KEY);
        }
    }
}
