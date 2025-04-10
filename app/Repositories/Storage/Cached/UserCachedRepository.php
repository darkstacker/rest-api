<?php declare(strict_types=1);

namespace App\Repositories\Storage\Cached;

use App\Entities\User;
use Illuminate\Support\Facades\Cache;
use App\Contracts\Interface\Repositories\Storage\UserStorageRepositoryInterface;
use App\Repositories\Storage\Queries\UserQueryRepository;
use App\Repositories\Storage\Transactions\UserTransactionRepository;
use Ramsey\Uuid\UuidInterface;
use Carbon\Carbon;

final class UserCachedRepository implements UserStorageRepositoryInterface
{
	/**
     * Cache key for storing all users.
     */
    private const CACHE_USER_ALL_KEY = 'users';

    /**
     * Constructs a new UserCachedRepository instance.
     *
     * @param UserQueryRepository $userQuery
     * @param UserTransactionRepository $userTransaction
     */
    public function __construct(
        private UserQueryRepository $userQuery,
        private UserTransactionRepository $userTransaction
    ) {}

    /**
     * Cache key for storing all users.
     *
     * @return array
     */
    public function all(): array
    {
        return Cache::flexible(
            key: self::CACHE_USER_ALL_KEY,
            ttl: [
                Carbon::now()->addMinutes(value: 5),
                Carbon::now()->addMinutes(value: 15)
            ],
            callback: fn () => $this->userQuery->all(),
            lock: ['seconds' => 10]
        );
    }

    /**
     * Finds a user by ID directly from the database.
     *
     * @param \Ramsey\Uuid\UuidInterface $id
     * @return \App\Entities\User|null
     */
    public function findById(UuidInterface $id): ?User
    {
        return $this->userQuery->findById(id: $id);
    }

    /**
     * Finds a user by email directly from the database.
     *
     * @param string $email
     * @return \App\Entities\User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userQuery->findByEmail(email: $email);
    }

    /**
     * Saves a user and invalidates cache if necessary.
     *
     * @param \App\Entities\User $user
     */
    public function save(User $user): void
    {
        $this->userTransaction->save(user: $user);

        if (Cache::has(key: self::CACHE_USER_ALL_KEY)) {
            Cache::forget(key: self::CACHE_USER_ALL_KEY);
        }
    }

    /**
     * Removes a user and invalidates cache if necessary.
     *
     * @param \App\Entities\User $user
     */
    public function remove(User $user): void
    {
        $this->userTransaction->remove(user: $user);

        if (Cache::has(key: self::CACHE_USER_ALL_KEY)) {
            Cache::forget(key: self::CACHE_USER_ALL_KEY);
        }
    }
}
