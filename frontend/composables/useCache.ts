/**
 * Data caching composable to prevent excessive API calls
 * Caches data in memory with TTL (time-to-live)
 */

interface CacheEntry<T> {
  data: T
  timestamp: number
  ttl: number
}

interface CacheOptions {
  ttl?: number // Time to live in milliseconds (default: 5 minutes)
  forceRefresh?: boolean
}

export const useCache = () => {
  // In-memory cache store
  const cache = useState<Map<string, CacheEntry<any>>>('app-cache', () => new Map())

  /**
   * Get data from cache or fetch if not available/expired
   */
  const getCached = async <T>(
    key: string,
    fetchFn: () => Promise<T>,
    options: CacheOptions = {}
  ): Promise<T> => {
    const { ttl = 5 * 60 * 1000, forceRefresh = false } = options // Default 5 minutes

    // Check if we should force refresh
    if (forceRefresh) {
      const data = await fetchFn()
      cache.value.set(key, {
        data,
        timestamp: Date.now(),
        ttl
      })
      return data
    }

    // Check if data exists in cache
    const cached = cache.value.get(key)
    
    if (cached) {
      const age = Date.now() - cached.timestamp
      
      // Return cached data if not expired
      if (age < cached.ttl) {
        console.log(`[Cache HIT] ${key} (age: ${Math.round(age / 1000)}s)`)
        return cached.data as T
      } else {
        console.log(`[Cache EXPIRED] ${key}`)
      }
    } else {
      console.log(`[Cache MISS] ${key}`)
    }

    // Fetch fresh data
    const data = await fetchFn()
    
    // Store in cache
    cache.value.set(key, {
      data,
      timestamp: Date.now(),
      ttl
    })

    return data
  }

  /**
   * Invalidate specific cache entry
   */
  const invalidate = (key: string) => {
    cache.value.delete(key)
    console.log(`[Cache INVALIDATED] ${key}`)
  }

  /**
   * Invalidate cache entries matching a pattern
   */
  const invalidatePattern = (pattern: string) => {
    const keys = Array.from(cache.value.keys())
    const matchingKeys = keys.filter(key => key.includes(pattern))
    
    matchingKeys.forEach(key => {
      cache.value.delete(key)
    })
    
    console.log(`[Cache INVALIDATED] ${matchingKeys.length} entries matching "${pattern}"`)
  }

  /**
   * Clear all cache
   */
  const clearAll = () => {
    cache.value.clear()
    console.log('[Cache CLEARED] All entries removed')
  }

  /**
   * Get cache stats
   */
  const getStats = () => {
    const entries = Array.from(cache.value.entries())
    const now = Date.now()
    
    return {
      total: entries.length,
      expired: entries.filter(([_, entry]) => now - entry.timestamp > entry.ttl).length,
      valid: entries.filter(([_, entry]) => now - entry.timestamp <= entry.ttl).length,
      entries: entries.map(([key, entry]) => ({
        key,
        age: Math.round((now - entry.timestamp) / 1000),
        ttl: Math.round(entry.ttl / 1000),
        expired: now - entry.timestamp > entry.ttl
      }))
    }
  }

  return {
    getCached,
    invalidate,
    invalidatePattern,
    clearAll,
    getStats
  }
}
