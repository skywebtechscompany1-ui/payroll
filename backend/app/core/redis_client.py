"""
Redis client for session management and caching
"""
import redis
from typing import Optional
from app.core.config import settings

class RedisClient:
    """Redis client singleton"""
    _instance: Optional[redis.Redis] = None
    
    @classmethod
    def get_client(cls) -> redis.Redis:
        """Get or create Redis client"""
        if cls._instance is None:
            cls._instance = redis.Redis(
                host=settings.REDIS_HOST,
                port=settings.REDIS_PORT,
                db=settings.REDIS_DB,
                decode_responses=True,
                password=settings.REDIS_PASSWORD if hasattr(settings, 'REDIS_PASSWORD') else None
            )
        return cls._instance
    
    @classmethod
    def close(cls):
        """Close Redis connection"""
        if cls._instance:
            cls._instance.close()
            cls._instance = None


# Token blacklist functions
def blacklist_token(token: str, expires_in: int):
    """Add token to blacklist"""
    client = RedisClient.get_client()
    client.setex(f"blacklist:{token}", expires_in, "1")


def is_token_blacklisted(token: str) -> bool:
    """Check if token is blacklisted"""
    client = RedisClient.get_client()
    return client.exists(f"blacklist:{token}") > 0


# Session management functions
def store_session(user_id: int, token: str, expires_in: int, metadata: dict = None):
    """Store user session"""
    client = RedisClient.get_client()
    session_key = f"session:{user_id}:{token}"
    session_data = {
        "user_id": user_id,
        "token": token,
        **(metadata or {})
    }
    client.hset(session_key, mapping=session_data)
    client.expire(session_key, expires_in)


def get_user_sessions(user_id: int) -> list:
    """Get all active sessions for a user"""
    client = RedisClient.get_client()
    pattern = f"session:{user_id}:*"
    sessions = []
    for key in client.scan_iter(match=pattern):
        session_data = client.hgetall(key)
        sessions.append(session_data)
    return sessions


def revoke_session(user_id: int, token: str):
    """Revoke a specific session"""
    client = RedisClient.get_client()
    session_key = f"session:{user_id}:{token}"
    client.delete(session_key)


def revoke_all_sessions(user_id: int):
    """Revoke all sessions for a user"""
    client = RedisClient.get_client()
    pattern = f"session:{user_id}:*"
    for key in client.scan_iter(match=pattern):
        client.delete(key)


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
    """
    client = RedisClient.get_client()
    rate_key = f"rate:{key}"
    
    current = client.get(rate_key)
    if current is None:
        client.setex(rate_key, window, 1)
        return True
    
    if int(current) >= limit:
        return False
    
    client.incr(rate_key)
    return True


def get_rate_limit_remaining(key: str, limit: int) -> int:
    """Get remaining requests in current window"""
    client = RedisClient.get_client()
    rate_key = f"rate:{key}"
    current = client.get(rate_key)
    if current is None:
        return limit
    return max(0, limit - int(current))


# Failed login tracking
def track_failed_login(identifier: str) -> int:
    """
    Track failed login attempt
    
    Returns:
        Number of failed attempts
    """
    client = RedisClient.get_client()
    key = f"failed_login:{identifier}"
    attempts = client.incr(key)
    if attempts == 1:
        # Set expiry on first attempt (15 minutes)
        client.expire(key, 900)
    return attempts


def reset_failed_logins(identifier: str):
    """Reset failed login counter"""
    client = RedisClient.get_client()
    client.delete(f"failed_login:{identifier}")


def is_account_locked(identifier: str, max_attempts: int = 5) -> bool:
    """Check if account is locked due to failed attempts"""
    client = RedisClient.get_client()
    key = f"failed_login:{identifier}"
    attempts = client.get(key)
    return attempts is not None and int(attempts) >= max_attempts


def get_lock_remaining_time(identifier: str) -> int:
    """Get remaining lock time in seconds"""
    client = RedisClient.get_client()
    key = f"failed_login:{identifier}"
    return client.ttl(key)
