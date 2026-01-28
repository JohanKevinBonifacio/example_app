<?php

namespace App\Services;

class CacheService
{
    protected static array $cache = [];
    protected static array $expirations = [];
    
    /**
     * Almacenar valor en caché
     */
    public function set(string $key, $value, int $ttlSeconds = 3600): void
    {
        $expirationTime = time() + $ttlSeconds;
        
        self::$cache[$key] = $value;
        self::$expirations[$key] = $expirationTime;
        
        \Log::info("📦 Cache SET: {$key} - TTL: {$ttlSeconds}s");
    }
    
    /**
     * Obtener valor de caché
     */
    public function get(string $key)
    {
        // Verificar si existe
        if (!isset(self::$cache[$key])) {
            \Log::info("❌ Cache MISS: {$key}");
            return null;
        }
        
        // Verificar si ha expirado
        if (isset(self::$expirations[$key]) && time() > self::$expirations[$key]) {
            \Log::info("⏰ Cache EXPIRED: {$key}");
            $this->delete($key);
            return null;
        }
        
        \Log::info("✅ Cache HIT: {$key}");
        return self::$cache[$key];
    }
    
    /**
     * Eliminar clave
     */
    public function delete(string $key): void
    {
        unset(self::$cache[$key]);
        unset(self::$expirations[$key]);
    }
    
    /**
     * Limpiar caché completa
     */
    public function clearCache(): void
    {
        self::$cache = [];
        self::$expirations = [];
        \Log::info("🧹 Cache CLEARED");
    }
    
    /**
     * Obtener tamaño de caché en MB
     */
    public function getCacheSize(): float
    {
        $totalSize = 0;
        
        foreach (self::$cache as $key => $value) {
            $totalSize += strlen(serialize($key)) + strlen(serialize($value));
        }
        
        return $totalSize / 1024 / 1024;
    }
    
    /**
     * Debug: Ver todas las keys en cache
     */
    public function getAllKeys(): array
    {
        return [
            'keys' => array_keys(self::$cache),
            'expirations' => self::$expirations,
            'count' => count(self::$cache)
        ];
    }
}