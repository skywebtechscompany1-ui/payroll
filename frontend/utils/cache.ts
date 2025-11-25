/**
 * Data Caching Utility
 * Provides localStorage and sessionStorage caching with TTL support
 */

export interface CacheOptions {
  ttl?: number // Time to live in milliseconds
  storage?: 'local' | 'session'
}

export class DataCache {
  private static readonly DEFAULT_TTL = 5 * 60 * 1000 // 5 minutes

  /**
   * Set cache item
   */
  static set(key: string, value: any, options: CacheOptions = {}): void {
    if (!process.client) return

    const { ttl = this.DEFAULT_TTL, storage = 'local' } = options
    
    const item = {
      value,
      timestamp: Date.now(),
      ttl
    }

    const storageObj = storage === 'local' ? localStorage : sessionStorage
    storageObj.setItem(key, JSON.stringify(item))
  }

  /**
   * Get cache item
   */
  static get<T = any>(key: string): T | null {
    if (!process.client) return null

    try {
      const localItem = localStorage.getItem(key)
      const sessionItem = sessionStorage.getItem(key)
      const itemStr = localItem || sessionItem

      if (!itemStr) return null

      const item = JSON.parse(itemStr)
      const now = Date.now()

      // Check if expired
      if (item.ttl && now - item.timestamp > item.ttl) {
        this.remove(key)
        return null
      }

      return item.value as T
    } catch (error) {
      console.error('Cache get error:', error)
      return null
    }
  }

  /**
   * Remove cache item
   */
  static remove(key: string): void {
    if (!process.client) return

    localStorage.removeItem(key)
    sessionStorage.removeItem(key)
  }

  /**
   * Clear all cache
   */
  static clear(storage?: 'local' | 'session'): void {
    if (!process.client) return

    if (!storage || storage === 'local') {
      localStorage.clear()
    }
    if (!storage || storage === 'session') {
      sessionStorage.clear()
    }
  }

  /**
   * Check if cache exists and is valid
   */
  static has(key: string): boolean {
    return this.get(key) !== null
  }

  /**
   * Get or fetch data with caching
   */
  static async getOrFetch<T = any>(
    key: string,
    fetchFn: () => Promise<T>,
    options: CacheOptions = {}
  ): Promise<T> {
    // Try to get from cache first
    const cached = this.get<T>(key)
    if (cached !== null) {
      return cached
    }

    // Fetch fresh data
    const data = await fetchFn()
    
    // Cache the result
    this.set(key, data, options)
    
    return data
  }

  /**
   * Invalidate cache by pattern
   */
  static invalidatePattern(pattern: string): void {
    if (!process.client) return

    const regex = new RegExp(pattern)

    // Check localStorage
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i)
      if (key && regex.test(key)) {
        localStorage.removeItem(key)
      }
    }

    // Check sessionStorage
    for (let i = 0; i < sessionStorage.length; i++) {
      const key = sessionStorage.key(i)
      if (key && regex.test(key)) {
        sessionStorage.removeItem(key)
      }
    }
  }
}

/**
 * Cache keys constants
 */
export const CACHE_KEYS = {
  // System
  SYSTEM_SETTINGS: 'system_settings',
  USER_PROFILE: 'user_profile',
  
  // Dropdowns (long TTL)
  DEPARTMENTS: 'departments_list',
  DESIGNATIONS: 'designations_list',
  ROLES: 'roles_list',
  EMPLOYEES: 'employees_list',
  
  // Dashboard (short TTL)
  DASHBOARD_STATS: 'dashboard_stats',
  
  // Leave
  LEAVE_TYPES: 'leave_types',
  HOLIDAYS: 'holidays_list',
  
  // Patterns for invalidation
  PATTERN: {
    DEPARTMENTS: '^departments_',
    EMPLOYEES: '^employees_',
    LEAVE: '^leave_',
    PAYROLL: '^payroll_'
  }
}

/**
 * Cache TTL presets (in milliseconds)
 */
export const CACHE_TTL = {
  SHORT: 1 * 60 * 1000,      // 1 minute
  MEDIUM: 5 * 60 * 1000,     // 5 minutes
  LONG: 30 * 60 * 1000,      // 30 minutes
  VERY_LONG: 24 * 60 * 60 * 1000  // 24 hours
}

/**
 * Composable for easy cache usage
 */
export const useCache = () => {
  return {
    set: DataCache.set.bind(DataCache),
    get: DataCache.get.bind(DataCache),
    remove: DataCache.remove.bind(DataCache),
    clear: DataCache.clear.bind(DataCache),
    has: DataCache.has.bind(DataCache),
    getOrFetch: DataCache.getOrFetch.bind(DataCache),
    invalidatePattern: DataCache.invalidatePattern.bind(DataCache),
    KEYS: CACHE_KEYS,
    TTL: CACHE_TTL
  }
}
