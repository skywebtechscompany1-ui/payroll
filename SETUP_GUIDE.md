# 🚀 PAYROLL SYSTEM - SETUP & INSTALLATION GUIDE

## 📋 Prerequisites

- **Python 3.10+** installed
- **Node.js 18+** and npm installed
- **PostgreSQL** database running
- **Redis** (optional but recommended for production features)

---

## 🔧 BACKEND SETUP

### 1. Install Redis (REQUIRED for new features)

#### **Option 1: Docker (Recommended)**
```powershell
# Pull and run Redis
docker run -d --name payroll-redis -p 6379:6379 redis:latest

# Verify it's running
docker ps
```

#### **Option 2: Windows Subsystem for Linux (WSL)**
```powershell
# Install WSL if not already installed
wsl --install

# In WSL terminal:
sudo apt-get update
sudo apt-get install redis-server

# Start Redis
sudo service redis-server start

# Verify
redis-cli ping
# Should return: PONG
```

#### **Option 3: Native Windows**
```powershell
# Using Chocolatey
choco install redis-64

# Or download from:
# https://github.com/microsoftarchive/redis/releases
```

### 2. Install Python Dependencies

```powershell
cd c:\Users\Hp\Desktop\payroll\payroll\backend

# Install all dependencies
pip install -r requirements.txt
```

**New dependencies added:**
- `redis` - Session management and caching
- `slowapi` - Rate limiting
- `email-validator` - Email validation
- `pandas` - Excel/CSV import
- `openpyxl` - Excel file handling

### 3. Configure Environment Variables

Create or update `.env` file in backend directory:

```env
# Database
DATABASE_URL=postgresql://username:password@localhost:5432/payroll_db

# Redis (if using custom config)
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_DB=0
# REDIS_PASSWORD=your_password  # Uncomment if Redis has password

# Security
SECRET_KEY=your-secret-key-here
ACCESS_TOKEN_EXPIRE_MINUTES=15
REFRESH_TOKEN_EXPIRE_DAYS=7

# CORS (for production, specify exact origins)
ALLOWED_HOSTS=http://localhost:3000,http://localhost:8000

# Debug
DEBUG=True
```

### 4. Test Backend

```powershell
# Start the backend
cd c:\Users\Hp\Desktop\payroll\payroll\backend
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

**Expected output:**
```
✅ Redis connection successful
✅ Rate limiting middleware enabled
✅ Payroll API started successfully
INFO:     Uvicorn running on http://0.0.0.0:8000
```

**Test endpoints:**
- Health check: http://localhost:8000/health
- API docs: http://localhost:8000/api/v1/docs

---

## 🎨 FRONTEND SETUP

### 1. Install Dependencies

```powershell
cd c:\Users\Hp\Desktop\payroll\payroll\frontend

# Install packages
npm install
```

### 2. Configure Environment

Create or update `.env` file in frontend directory:

```env
NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1
```

### 3. Start Frontend

```powershell
npm run dev
```

**Expected output:**
```
Nuxt 3.x.x
Local:    http://localhost:3000
```

---

## ✅ VERIFICATION CHECKLIST

### Backend Health Check

Visit: http://localhost:8000/health

**Expected response:**
```json
{
  "status": "healthy",
  "timestamp": 1732542000.123,
  "version": "1.0.0",
  "services": {
    "database": "healthy",
    "redis": "healthy"
  }
}
```

### Test Authentication Features

#### 1. **Login with Rate Limiting**
Try logging in 6 times quickly with wrong password:
- First 5 attempts: "Incorrect email or password"
- 6th attempt: "Too many login attempts. Please try again later."

#### 2. **Account Lockout**
Try logging in 5 times with wrong password:
- Account gets locked for 15 minutes
- Error: "Account locked due to multiple failed attempts. Try again in 15 minutes."

#### 3. **Successful Login**
Login with correct credentials:
- Email: `admin@jafasol.com`
- Password: `3r14F65gMv`
- Should receive access token and refresh token

#### 4. **Logout (Token Blacklisting)**
- Logout
- Try using the same token again
- Should get: "Token has been revoked"

#### 5. **Security Headers**
Open browser DevTools → Network → Check response headers:
- ✅ `X-Content-Type-Options: nosniff`
- ✅ `X-Frame-Options: DENY`
- ✅ `X-XSS-Protection: 1; mode=block`
- ✅ `Strict-Transport-Security`
- ✅ `Content-Security-Policy`

---

## 🐛 TROUBLESHOOTING

### Redis Connection Failed

**Error:** `⚠️ Redis connection failed: Error 10061`

**Solutions:**
1. **Check if Redis is running:**
   ```powershell
   # Docker
   docker ps
   
   # WSL
   sudo service redis-server status
   ```

2. **Start Redis:**
   ```powershell
   # Docker
   docker start payroll-redis
   
   # WSL
   sudo service redis-server start
   ```

3. **Test connection:**
   ```powershell
   redis-cli ping
   # Should return: PONG
   ```

### Database Connection Failed

**Error:** `unhealthy: could not connect to server`

**Solutions:**
1. Check PostgreSQL is running
2. Verify DATABASE_URL in `.env`
3. Test connection:
   ```powershell
   psql -U username -d payroll_db
   ```

### Import Errors

**Error:** `ModuleNotFoundError: No module named 'redis'`

**Solution:**
```powershell
pip install -r requirements.txt
```

### Port Already in Use

**Error:** `Address already in use`

**Solution:**
```powershell
# Find process using port 8000
netstat -ano | findstr :8000

# Kill the process
taskkill /PID <process_id> /F
```

---

## 🔄 RUNNING WITHOUT REDIS

If you can't install Redis right now, the system will still work with degraded functionality:

**Features that will be disabled:**
- ❌ Token blacklisting (logout won't invalidate tokens)
- ❌ Session management (sessions won't persist)
- ❌ Rate limiting (no brute force protection)
- ❌ Account lockout (unlimited login attempts)

**Features that will still work:**
- ✅ Login/logout (basic functionality)
- ✅ All CRUD operations
- ✅ Security headers
- ✅ Request logging
- ✅ All business logic

**To run without Redis:**
The system will automatically detect Redis is unavailable and continue with warnings.

---

## 📊 MONITORING

### Check Logs

Backend logs show all important events:
```
INFO - Successful login: admin@jafasol.com from 127.0.0.1
WARNING - Failed login attempt for test@test.com from 127.0.0.1. Attempts: 3
ERROR - Account locked: test@test.com
INFO - User logged out: admin@jafasol.com
```

### Monitor Redis

```powershell
# Connect to Redis CLI
redis-cli

# Monitor all commands
MONITOR

# Check keys
KEYS *

# Check specific key
GET blacklist:your-token-here
```

---

## 🎯 NEXT STEPS

Once setup is complete:

1. ✅ **Test all authentication features**
2. ✅ **Review security headers in browser**
3. ✅ **Test rate limiting**
4. ✅ **Test account lockout**
5. ✅ **Check health endpoint**
6. ✅ **Review logs for any errors**

Then proceed to:
- Phase 2: Input Validation
- Phase 3: Business Logic Fixes
- Phase 4: Performance Optimization
- Phase 5: Frontend Improvements
- Phase 6: Testing & Deployment

---

## 📞 SUPPORT

If you encounter issues:

1. Check the logs for detailed error messages
2. Verify all services are running (PostgreSQL, Redis)
3. Ensure all dependencies are installed
4. Check environment variables are set correctly

**Common Issues:**
- Redis connection: Make sure Redis is running
- Database connection: Check PostgreSQL credentials
- Import errors: Run `pip install -r requirements.txt`
- Port conflicts: Change port or kill conflicting process

---

## ✨ WHAT'S NEW

### Security Improvements:
- ✅ Token blacklisting on logout
- ✅ Session management with Redis
- ✅ Rate limiting (5 login attempts/min per IP)
- ✅ Account lockout (5 failed attempts = 15 min lock)
- ✅ Security headers (XSS, CSRF, Clickjacking protection)
- ✅ Comprehensive audit logging
- ✅ Global rate limiting (100 req/min per IP)

### System Improvements:
- ✅ Better error handling
- ✅ Health check endpoint with service status
- ✅ Request timing and logging
- ✅ Graceful degradation (works without Redis)
- ✅ Better startup/shutdown logging

---

**Ready to deploy to production?** See `DEPLOYMENT_CHECKLIST.md` (coming soon)
