<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    const BALANCE_CACHE_TTL = 300; // 5 minutes
    const HISTORY_CACHE_TTL = 600; // 10 minutes

    /**
     * Get cached balance for a user
     */
    public function getBalance(User $user): ?string
    {
        return Cache::get($this->getBalanceCacheKey($user->id));
    }

    /**
     * Set balance cache for a user
     */
    public function setBalance(User $user, string $balance): void
    {
        Cache::put(
            $this->getBalanceCacheKey($user->id),
            $balance,
            self::BALANCE_CACHE_TTL
        );
    }

    /**
     * Invalidate balance cache
     */
    public function invalidateBalance(User $user): void
    {
        Cache::forget($this->getBalanceCacheKey($user->id));
    }

    /**
     * Get cached transaction history
     */
    public function getHistory(User $user, int $page = 1, int $perPage = 10): ?array
    {
        return Cache::get($this->getHistoryCacheKey($user->id, $page, $perPage));
    }

    /**
     * Set transaction history cache
     */
    public function setHistory(User $user, int $page = 1, int $perPage = 10, array $history = []): void
    {
        Cache::put(
            $this->getHistoryCacheKey($user->id, $page, $perPage),
            $history,
            self::HISTORY_CACHE_TTL
        );
    }

    /**
     * Invalidate all history caches for a user
     */
    public function invalidateAllHistory(User $user): void
    {
        // Invalidate multiple pages
        for ($page = 1; $page <= 10; $page++) {
            for ($perPage = 10; $perPage <= 100; $perPage += 10) {
                Cache::forget($this->getHistoryCacheKey($user->id, $page, $perPage));
            }
        }
    }

    /**
     * Get balance cache key
     */
    private function getBalanceCacheKey(string $userId): string
    {
        return "user.{$userId}.balance";
    }

    /**
     * Get history cache key
     */
    private function getHistoryCacheKey(string $userId, int $page, int $perPage): string
    {
        return "user.{$userId}.history.{$page}.{$perPage}";
    }
}
