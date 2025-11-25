# 🚀 RENDER DEPLOYMENT GUIDE

**Complete guide to deploy your Payroll System to Render**

---

## 📋 PREREQUISITES

✅ Your Redis Labs URL: `redis-10569.c245.us-east-1-3.ec2.cloud.redislabs.com:10569`  
✅ Redis password (you should have this)  
✅ GitHub account  
✅ Render account (free)

---

## 🎯 DEPLOYMENT STEPS

### **STEP 1: Prepare Your Code (5 minutes)**

#### **1.1 Update Redis Configuration**

The `render.yaml` is already configured with your Redis URL. You just need to add the password in Render dashboard.

#### **1.2 Push to GitHub**

```powershell
# Navigate to project root
cd c:\Users\Hp\Desktop\payroll\payroll

# Initialize git (if not already)
git init

# Add all files
git add .

# Commit
git commit -m "Production ready deployment"

# Add your GitHub repository
git remote add origin https://github.com/YOUR_USERNAME/payroll.git

# Push
git push -u origin main
```

---

### **STEP 2: Create Render Account (2 minutes)**

1. Go to https://render.com
2. Click "Get Started for Free"
3. Sign up with GitHub
4. Authorize Render to access your repositories

---

### **STEP 3: Deploy Using Blueprint (5 minutes)**

#### **Option A: Using render.yaml (Recommended)**

1. **In Render Dashboard:**
   - Click "New" → "Blueprint"
   - Connect your GitHub repository
   - Select the `payroll` repository
   - Render will detect `backend/render.yaml`
   - Click "Apply"

2. **Add Redis Password:**
   - Go to "payroll-api" service
   - Click "Environment"
   - Find `REDIS_PASSWORD`
   - Click "Edit" and add your Redis Labs password
   - Click "Save Changes"

3. **Wait for Deployment:**
   - Render will automatically:
     - Create PostgreSQL database
     - Deploy FastAPI backend
     - Run migrations
   - Takes ~5-10 minutes

#### **Option B: Manual Setup**

If blueprint doesn't work, follow manual steps below.

---

### **STEP 4: Manual Deployment (Alternative)**

#### **4.1 Create PostgreSQL Database**

1. Dashboard → "New" → "PostgreSQL"
2. Settings:
   ```
   Name: payroll-db
   Database: payroll
   User: payroll_user
   Region: Oregon (or closest to you)
   Plan: Free
   ```
3. Click "Create Database"
4. **Copy the Internal Database URL** (starts with `postgresql://`)

#### **4.2 Create Web Service**

1. Dashboard → "New" → "Web Service"
2. Connect your GitHub repository
3. Settings:
   ```
   Name: payroll-api
   Region: Oregon
   Branch: main
   Root Directory: backend
   Runtime: Python 3
   Build Command: pip install -r requirements.txt
   Start Command: gunicorn main:app --workers 4 --worker-class uvicorn.workers.UvicornWorker --bind 0.0.0.0:$PORT
   Plan: Free
   ```

#### **4.3 Add Environment Variables**

Click "Advanced" → "Add Environment Variable" and add these:

```env
# Application
DEBUG=False
SECRET_KEY=<click-generate-to-create-random-key>
ACCESS_TOKEN_EXPIRE_MINUTES=15
REFRESH_TOKEN_EXPIRE_DAYS=7

# Database (paste the URL from step 4.1)
DATABASE_URL=postgresql://payroll_user:password@host/payroll

# Redis (Your Redis Labs)
REDIS_HOST=redis-10569.c245.us-east-1-3.ec2.cloud.redislabs.com
REDIS_PORT=10569
REDIS_DB=0
REDIS_PASSWORD=<your-redis-labs-password>

# CORS (update with your actual domain)
ALLOWED_HOSTS=https://yourdomain.com,https://www.yourdomain.com

# Company
COMPANY_NAME=Your Company Name
COMPANY_EMP_NUMBER=P051217793P
```

4. Click "Create Web Service"

---

### **STEP 5: Run Database Migrations (2 minutes)**

After deployment completes:

1. Go to your service → "Shell"
2. Run:
   ```bash
   cd backend
   alembic upgrade head
   python seed_data_improved.py
   ```

Or use Render's "Deploy Hook" to run migrations automatically.

---

### **STEP 6: Get Your Backend URL (1 minute)**

1. Go to your "payroll-api" service
2. Copy the URL: `https://payroll-api-xxxx.onrender.com`
3. Test it: `https://payroll-api-xxxx.onrender.com/health`

Should return:
```json
{
  "status": "healthy",
  "services": {
    "database": "healthy",
    "redis": "healthy"
  }
}
```

---

### **STEP 7: Configure Custom Domain (Optional)**

#### **For Backend API:**

1. **In Render:**
   - Go to "payroll-api" service
   - Settings → "Custom Domain"
   - Add: `api.yourdomain.com`
   - Copy the CNAME value

2. **In Your Domain DNS (cPanel):**
   - Add CNAME record:
   ```
   Type: CNAME
   Name: api
   Value: payroll-api-xxxx.onrender.com
   TTL: 3600
   ```

3. **Wait for SSL:**
   - Render will automatically provision SSL
   - Takes ~5 minutes

---

## 🎨 FRONTEND DEPLOYMENT (cPanel)

### **STEP 1: Update Frontend Configuration**

```powershell
cd c:\Users\Hp\Desktop\payroll\payroll\frontend

# Create .env file
echo NUXT_PUBLIC_API_BASE=https://payroll-api-xxxx.onrender.com/api/v1 > .env

# Or if using custom domain:
echo NUXT_PUBLIC_API_BASE=https://api.yourdomain.com/api/v1 > .env
```

### **STEP 2: Build Frontend**

```powershell
# Install dependencies
npm install

# Build for production
npm run build
```

This creates `.output/public` folder.

### **STEP 3: Upload to cPanel**

1. **Login to cPanel**
2. **File Manager** → `public_html`
3. **Upload all files** from `frontend/.output/public`
4. **Create .htaccess:**

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # Handle SPA routing
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ /index.html [L]
</IfModule>

# Compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## ✅ VERIFICATION CHECKLIST

### **Backend Tests:**
- [ ] Health endpoint works: `https://your-api-url/health`
- [ ] API docs accessible: `https://your-api-url/docs`
- [ ] Login works: Test with admin credentials
- [ ] Redis connected (check health endpoint)
- [ ] Database connected (check health endpoint)

### **Frontend Tests:**
- [ ] Website loads: `https://yourdomain.com`
- [ ] Login page works
- [ ] Can login successfully
- [ ] Dashboard loads
- [ ] API calls work (check browser console)

### **Security Tests:**
- [ ] HTTPS enabled (green padlock)
- [ ] CORS configured correctly
- [ ] Rate limiting works (try 6 failed logins)
- [ ] Logout works (token blacklisted)

---

## 🔧 TROUBLESHOOTING

### **Issue: Build Failed**

**Solution:**
```powershell
# Check requirements.txt
pip install -r backend/requirements.txt

# If specific package fails, update version
```

### **Issue: Database Connection Failed**

**Solution:**
1. Check `DATABASE_URL` in environment variables
2. Ensure it's the **Internal URL** from Render
3. Format: `postgresql://user:pass@host:5432/dbname`

### **Issue: Redis Connection Failed**

**Solution:**
1. Verify Redis Labs password is correct
2. Check Redis host and port
3. Test connection:
   ```python
   import redis
   r = redis.Redis(
       host='redis-10569.c245.us-east-1-3.ec2.cloud.redislabs.com',
       port=10569,
       password='your-password',
       decode_responses=True
   )
   r.ping()  # Should return True
   ```

### **Issue: CORS Errors**

**Solution:**
1. Update `ALLOWED_HOSTS` in Render environment variables
2. Add your frontend domain:
   ```
   ALLOWED_HOSTS=https://yourdomain.com,https://www.yourdomain.com
   ```

### **Issue: 502 Bad Gateway**

**Solution:**
1. Check Render logs: Service → "Logs"
2. Usually means app crashed on startup
3. Check for missing environment variables

---

## 📊 MONITORING

### **View Logs:**
1. Go to "payroll-api" service
2. Click "Logs"
3. See real-time application logs

### **View Metrics:**
1. Go to "payroll-api" service
2. Click "Metrics"
3. See CPU, Memory, Response times

### **Set Up Alerts:**
1. Settings → "Notifications"
2. Add email for deployment failures
3. Add webhook for Slack/Discord

---

## 💰 COST BREAKDOWN

### **Free Tier (Good for testing):**
- ✅ Backend: Free (spins down after 15 min inactivity)
- ✅ PostgreSQL: Free for 90 days
- ✅ Redis: Your Redis Labs (check your plan)
- ✅ SSL: Free
- ✅ Custom domain: Free

**Total: $0/month** (first 90 days)

### **Paid Tier (Production):**
- Backend: $7/month (always on)
- PostgreSQL: $7/month
- Redis: Your Redis Labs plan
- **Total: ~$14/month + Redis**

---

## 🚀 DEPLOYMENT COMPLETE!

### **Your URLs:**
- **Backend API:** `https://payroll-api-xxxx.onrender.com`
- **API Docs:** `https://payroll-api-xxxx.onrender.com/docs`
- **Frontend:** `https://yourdomain.com`
- **Health Check:** `https://payroll-api-xxxx.onrender.com/health`

### **Default Credentials:**
- Email: `admin@jafasol.com`
- Password: `3r14F65gMv`

**⚠️ IMPORTANT: Change default password after first login!**

---

## 📞 SUPPORT

**Issues?**
1. Check Render logs
2. Check browser console
3. Verify environment variables
4. Test health endpoint

**Documentation:**
- Render Docs: https://render.com/docs
- FastAPI Docs: https://fastapi.tiangolo.com
- Your project docs: See `COMPLETE_IMPLEMENTATION.md`

---

## 🎉 SUCCESS!

Your payroll system is now live and production-ready! 🚀

**Next Steps:**
1. Change default admin password
2. Add your employees
3. Configure company settings
4. Test all features
5. Monitor logs for any issues

**Enjoy your new payroll system!** 🎊
