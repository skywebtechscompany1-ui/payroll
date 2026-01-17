"""
Redis client for session management and caching
All functions gracefully handle Redis failures to ensure the app works without Redis
"""
import redis
from typing import Optional
import logging
from app.core.config import settings

logger = logging.getLogger(__name__)

# Flag to track if Redis is available
_redis_available = True


class RedisClient:
    """Redis client singleton with graceful failure handling"""
    _instance: Optional[redis.Redis] = None
    
    @classmethod
    def get_client(cls) -> Optional[redis.Redis]:
        """Get or create Redis client, returns None if unavailable"""
        global _redis_available
        
        if not _redis_available:
            return None
            
        if cls._instance is None:
            try:
                cls._instance = redis.Redis(
                    host=settings.REDIS_HOST,
                    port=settings.REDIS_PORT,
                    db=settings.REDIS_DB,
                    decode_responses=True,
                    password=settings.REDIS_PASSWORD if hasattr(settings, 'REDIS_PASSWORD') else None,
                    socket_connect_timeout=2,
                    socket_timeout=2
                )
                # Test connection
                cls._instance.ping()
            except Exception as e:
                logger.warning(f"Redis connection failed: {e}. Operating without Redis.")
                _redis_available = False
                cls._instance = None
                return None
        return cls._instance
    
    @classmethod
    def close(cls):
        """Close Redis connection"""
        if cls._instance:
            try:
                cls._instance.close()
            except:
                pass
            cls._instance = None


def _safe_redis_operation(operation, default=None):
    """Wrapper to safely execute Redis operations"""
    try:
        client = RedisClient.get_client()
        if client is None:
            return default
        return operation(client)
    except Exception as e:
        logger.warning(f"Redis operation failed: {e}")
        return default


# Token blacklist functions
def blacklist_token(token: str, expires_in: int):
    """Add token to blacklist"""
    def op(client):
        client.setex(f"blacklist:{token}", expires_in, "1")
    _safe_redis_operation(op)


def is_token_blacklisted(token: str) -> bool:
    """Check if token is blacklisted"""
    def op(client):
        return client.exists(f"blacklist:{token}") > 0
    return _safe_redis_operation(op, default=False)


# Session management functions
def store_session(user_id: int, token: str, expires_in: int, metadata: dict = None):
    """Store user session"""
    def op(client):
        session_key = f"session:{user_id}:{token}"
        session_data = {
            "user_id": str(user_id),
            "token": token,
            **(metadata or {})
        }
        client.hset(session_key, mapping=session_data)
        client.expire(session_key, expires_in)
    _safe_redis_operation(op)


def get_user_sessions(user_id: int) -> list:
    """Get all active sessions for a user"""
    def op(client):
        pattern = f"session:{user_id}:*"
        sessions = []
        for key in client.scan_iter(match=pattern):
            session_data = client.hgetall(key)
            sessions.append(session_data)
        return sessions
    return _safe_redis_operation(op, default=[])


def revoke_session(user_id: int, token: str):
    """Revoke a specific session"""
    def op(client):
        session_key = f"session:{user_id}:{token}"
        client.delete(session_key)
    _safe_redis_operation(op)


def revoke_all_sessions(user_id: int):
    """Revoke all sessions for a user"""
    def op(client):
        pattern = f"session:{user_id}:*"
        for key in client.scan_iter(match=pattern):
            client.delete(key)
    _safe_redis_operation(op)


# Rate limiting functions
def check_rate_limit(key: str, limit: int, window: int) -> bool:
    """
    Check if rate limit is exceeded
    
    Args:
        key: Unique identifier (e.g., IP address, user ID)
        limit: Maximum number of requests
        window: Time window in seconds
    
    Returns:
        True if within limit, False if exceeded
        Always returns True if Redis is unavailable (fail open)
    """
    def op(client):
        rate_key = f"rate:{key}"
        current = client.get(rate_key)
        if current is None:
            client.setex(rate_key, window, 1)
            return True
        if int(current) >= limit:
            return False
        client.incr(rate_key)
        return True
    
    # Default to True (allow) if Redis is down - fail open
    return _safe_redis_operation(op, default=True)


def get_rate_limit_remaining(key: str, limit: int) -> int:
    """Get remaining requests in current window"""
    def op(client):
        rate_key = f"rate:{key}"
        current = client.get(rate_key)
        if current is None:
            return limit
        return max(0, limit - int(current))
    return _safe_redis_operation(op, default=limit)


# Failed login tracking
def track_failed_login(identifier: str) -> int:
    """
    Track failed login attempt
    
    Returns:
        Number of failed attempts (returns 1 if Redis unavailable)
    """
    def op(client):
        key = f"failed_login:{identifier}"
        attempts = client.incr(key)
        if attempts == 1:
            # Set expiry on first attempt (15 minutes)
            client.expire(key, 900)
        return attempts
    return _safe_redis_operation(op, default=1)


def reset_failed_logins(identifier: str):
    """Reset failed login counter"""
    def op(client):
        client.delete(f"failed_login:{identifier}")
    _safe_redis_operation(op)


def is_account_locked(identifier: str, max_attempts: int = 5) -> bool:
    """Check if account is locked due to failed attempts"""
    def op(client):
        key = f"failed_login:{identifier}"
        attempts = client.get(key)
        return attempts is not None and int(attempts) >= max_attempts
    # Default to False (not locked) if Redis is down
    return _safe_redis_operation(op, default=False)


def get_lock_remaining_time(identifier: str) -> int:
    """Get remaining lock time in seconds"""
    def op(client):
        key = f"failed_login:{identifier}"
        return client.ttl(key)
    return _safe_redis_operation(op, default=0)
