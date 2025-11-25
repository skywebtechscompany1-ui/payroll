# ⚡ QUICK START GUIDE

## 🚀 Get Up and Running in 5 Minutes

### 1️⃣ Install Redis (Choose One)

**Docker (Easiest):**
```powershell
docker run -d --name payroll-redis -p 6379:6379 redis:latest
```

**WSL:**
```powershell
wsl --install
# Then in WSL:
sudo apt-get install redis-server
sudo service redis-server start
```

### 2️⃣ Install Backend Dependencies

```powershell
cd c:\Users\Hp\Desktop\payroll\payroll\backend
pip install -r requirements.txt
```

### 3️⃣ Start Backend

```powershell
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

**Look for:**
```
✅ Redis connection successful
✅ Rate limiting middleware enabled
✅ Payroll API started successfully
```

### 4️⃣ Test It Works

Visit: http://localhost:8000/health

**Should see:**
```json
{
  "status": "healthy",
  "services": {
    "database": "healthy",
    "redis": "healthy"
  }
}
```

### 5️⃣ Login and Test

1. **Login:** http://localhost:3000/login
   - Email: `admin@jafasol.com`
   - Password: `3r14F65gMv`

2. **Try wrong password 6 times** → Should get rate limited

3. **Logout** → Token should be blacklisted

4. **Try using old token** → Should fail

---

## ✅ What's New

- ✅ **Logout actually works** (tokens blacklisted)
- ✅ **Brute force protection** (5 attempts then locked)
- ✅ **Rate limiting** (5 login attempts/min)
- ✅ **Security headers** (XSS, CSRF protection)
- ✅ **Session tracking** (see who's logged in)
- ✅ **Audit logging** (all events logged)

---

## 🐛 Troubleshooting

**Redis not connecting?**
```powershell
# Check if running
docker ps

# Start it
docker start payroll-redis

# Test
redis-cli ping
```

**Import errors?**
```powershell
pip install -r requirements.txt
```

**Port in use?**
```powershell
netstat -ano | findstr :8000
taskkill /PID <process_id> /F
```

---

## 📚 Full Documentation

- **Setup Guide:** `SETUP_GUIDE.md`
- **Phase 1 Complete:** `PHASE_1_COMPLETE.md`
- **Full Audit:** `PRODUCTION_READINESS_AUDIT.md`
- **Progress:** `IMPLEMENTATION_PROGRESS.md`

---

## 🎯 Next Steps

Once everything works:
1. Review `PHASE_1_COMPLETE.md` for details
2. Test all security features
3. Approve Phase 2 to continue

**Questions?** Check the documentation or ask!
