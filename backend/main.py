"""
Payroll Management System API
FastAPI backend for modern payroll management
"""

from fastapi import FastAPI, Request, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.middleware.trustedhost import TrustedHostMiddleware
from fastapi.responses import JSONResponse
from contextlib import asynccontextmanager
import time
import logging

from app.core.config import settings
from app.core.database import engine, Base
from app.core.middleware import (
    SecurityHeadersMiddleware,
    RequestLoggingMiddleware,
    RateLimitMiddleware
)
from app.api.v1.api import api_router

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup
    logger.info("Starting Payroll API...")
    
    # Create database tables
    Base.metadata.create_all(bind=engine)
    
    # Test Redis connection
    try:
        from app.core.redis_client import RedisClient
        redis_client = RedisClient.get_client()
        redis_client.ping()
        logger.info("✅ Redis connection successful")
    except Exception as e:
        logger.warning(f"⚠️ Redis connection failed: {e}")
        logger.warning("Some features (session management, rate limiting) will be disabled")
    
    logger.info("✅ Payroll API started successfully")
    
    yield
    
    # Shutdown
    logger.info("Shutting down Payroll API...")
    
    # Close Redis connection
    try:
        from app.core.redis_client import RedisClient
        RedisClient.close()
        logger.info("✅ Redis connection closed")
    except Exception as e:
        logger.warning(f"⚠️ Error closing Redis: {e}")
    
    logger.info("✅ Payroll API shutdown complete")


app = FastAPI(
    title=settings.PROJECT_NAME,
    description="Modern Payroll Management System API",
    version=settings.VERSION,
    openapi_url=f"{settings.API_V1_STR}/openapi.json",
    lifespan=lifespan
)

# Add security middleware (order matters!)
# 1. Rate limiting (first line of defense)
try:
    app.add_middleware(RateLimitMiddleware)
    logger.info("✅ Rate limiting middleware enabled")
except Exception as e:
    logger.warning(f"⚠️ Rate limiting disabled: {e}")

# 2. Security headers
app.add_middleware(SecurityHeadersMiddleware)

# 3. Request logging
app.add_middleware(RequestLoggingMiddleware)

# 4. CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.ALLOWED_HOSTS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# 5. Trusted host (if not allowing all hosts)
# Note: TrustedHostMiddleware disabled for cloud deployments
# Cloud platforms like Render handle host validation at the infrastructure level
# if settings.ALLOWED_HOSTS != ["*"]:
#     app.add_middleware(
#         TrustedHostMiddleware,
#         allowed_hosts=settings.ALLOWED_HOSTS
#     )


# Global exception handlers
@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    logger.error(f"Unhandled exception: {exc}", exc_info=True)
    return JSONResponse(
        status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
        content={
            "success": False,
            "error": "Internal server error",
            "detail": str(exc) if settings.DEBUG else "An unexpected error occurred"
        }
    )

@app.exception_handler(404)
async def not_found_handler(request: Request, exc):
    return JSONResponse(
        status_code=status.HTTP_404_NOT_FOUND,
        content={
            "success": False,
            "error": "Not found",
            "detail": f"The requested resource was not found: {request.url.path}"
        }
    )


# Include API routes
app.include_router(api_router, prefix=settings.API_V1_STR)


@app.get("/")
async def root():
    return {
        "message": "Payroll Management System API",
        "version": settings.VERSION,
        "docs": f"{settings.API_V1_STR}/docs"
    }


@app.get("/health")
async def health_check():
    """Health check endpoint with service status"""
    health_status = {
        "status": "healthy",
        "timestamp": time.time(),
        "version": settings.VERSION,
        "services": {}
    }
    
    # Check database
    try:
        from app.core.database import SessionLocal
        db = SessionLocal()
        db.execute("SELECT 1")
        db.close()
        health_status["services"]["database"] = "healthy"
    except Exception as e:
        health_status["services"]["database"] = f"unhealthy: {str(e)}"
        health_status["status"] = "degraded"
    
    # Check Redis
    try:
        from app.core.redis_client import RedisClient
        redis_client = RedisClient.get_client()
        redis_client.ping()
        health_status["services"]["redis"] = "healthy"
    except Exception as e:
        health_status["services"]["redis"] = f"unhealthy: {str(e)}"
        health_status["status"] = "degraded"
    
    return health_status


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=8000,
        reload=settings.DEBUG
    )