<?php

namespace Fusio\Adapter\Util\Component;

/**
 * RequestChainStorage
 * 
 * A request-scoped key-value storage for sharing data between chained actions.
 * Uses static variables which are scoped to the current request execution.
 * This is safer than $GLOBALS as it doesn't interfere with other global variables
 * and is automatically cleaned up after the request completes.
 * 
 * Each chained action can read from and write to this storage as needed,
 * without needing to modify the request object itself.
 */
class RequestChainStorage
{
    /**
     * Storage for shared data in chain execution
     * @var array<string, mixed>
     */
    private static array $storage = [];

    /**
     * Store a value in the request-scoped storage
     * 
     * @param string $key The key to store the value under
     * @param mixed $value The value to store
     */
    public static function set(string $key, mixed $value): void
    {
        self::$storage[$key] = $value;
    }

    /**
     * Get a value from the request-scoped storage
     * 
     * @param string $key The key to retrieve
     * @param mixed $default The default value to return if key doesn't exist
     * @return mixed The stored value or default value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$storage[$key] ?? $default;
    }

    /**
     * Check if a key exists in the storage
     * 
     * @param string $key The key to check
     * @return bool True if key exists, false otherwise
     */
    public static function has(string $key): bool
    {
        return isset(self::$storage[$key]);
    }

    /**
     * Remove a key from the storage
     * 
     * @param string $key The key to remove
     */
    public static function remove(string $key): void
    {
        unset(self::$storage[$key]);
    }

    /**
     * Get all stored data
     * 
     * @return array<string, mixed> All stored data
     */
    public static function all(): array
    {
        return self::$storage;
    }

    /**
     * Clear all stored data
     */
    public static function clear(): void
    {
        self::$storage = [];
    }

    /**
     * Check if the storage is empty
     *
     * @return bool True if storage is empty, false otherwise
     */
    public static function isEmpty(): bool
    {
        return empty(self::$storage);
    }
}

