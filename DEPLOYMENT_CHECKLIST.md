# 🚀 PRODUCTION DEPLOYMENT CHECKLIST

**Project:** Payroll Management System  
**Version:** 1.0.0  
**Date:** November 25, 2025

---

## ✅ PRE-DEPLOYMENT CHECKLIST

### **1. Environment Setup**

#### **Backend Environment Variables:**
```env
# Production Settings
DEBUG=False
SECRET_KEY=<generate-strong-random-key-here>
ACCESS_TOKEN_EXPIRE_MINUTES=15
REFRESH_TOKEN_EXPIRE_DAYS=7

# Database
DATABASE_URL=postgresql://user:password@host:5432/payroll_production

# Redis
REDIS_HOST=your-redis-host
REDIS_PORT=6379
REDIS_DB=0
REDIS_PASSWORD=<strong-password>

# CORS (Specify exact origins)
ALLOWED_HOSTS=https://yourdomain.com,https://www.yourdomain.com

# Email (for password reset)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASSWORD=<app-password>
EMAILS_FROM_EMAIL=noreply@yourdomain.com
EMAILS_FROM_NAME=Payroll System

# Company
COMPANY_NAME=Your Company Name
COMPANY_EMP_NUMBER=P051217793P
```

#### **Frontend Environment Variables:**
```env
NUXT_PUBLIC_API_BASE=https://api.yourdomain.com/api/v1
```

---

### **2. Security Hardening**

- [ ] **Change SECRET_KEY** to a strong random value
  ```python
  import secrets
  print(secrets.token_urlsafe(32))
  ```

- [ ] **Set DEBUG=False** in production

- [ ] **Configure ALLOWED_HOSTS** with exact domains (not `["*"]`)

- [ ] **Enable HTTPS/SSL**
  - [ ] Obtain SSL certificate (Let's Encrypt)
  - [ ] Configure nginx/Apache for HTTPS
  - [ ] Force HTTPS redirect

- [ ] **Set Redis password**
  ```bash
  redis-cli CONFIG SET requirepass "your-strong-password"
  ```

- [ ] **Secure database**
  - [ ] Use strong database password
  - [ ] Restrict database access to application server only
  - [ ] Enable SSL for database connections

- [ ] **Configure firewall**
  - [ ] Allow only ports 80, 443 (HTTP/HTTPS)
  - [ ] Block direct access to ports 8000, 6379, 5432
  - [ ] Enable fail2ban for SSH

---

### **3. Database Setup**

- [ ] **Create production database**
  ```sql
  CREATE DATABASE payroll_production;
  CREATE USER payroll_user WITH PASSWORD 'strong-password';
  GRANT ALL PRIVILEGES ON DATABASE payroll_production TO payroll_user;
  ```

- [ ] **Run migrations**
  ```bash
  cd backend
  alembic upgrade head
  ```

- [ ] **Seed initial data**
  ```bash
  python seed_data_improved.py
  ```

- [ ] **Create database backups**
  ```bash
  # Daily backup cron job
  0 2 * * * pg_dump payroll_production > /backups/payroll_$(date +\%Y\%m\%d).sql
  ```

---

### **4. Redis Setup**

- [ ] **Install Redis**
  ```bash
  sudo apt-get install redis-server
  ```

- [ ] **Configure Redis**
  ```bash
  # Edit /etc/redis/redis.conf
  requirepass your-strong-password
  maxmemory 256mb
  maxmemory-policy allkeys-lru
  ```

- [ ] **Enable Redis persistence**
  ```bash
  # In redis.conf
  save 900 1
  save 300 10
  save 60 10000
  ```

- [ ] **Start Redis**
  ```bash
  sudo systemctl start redis
  sudo systemctl enable redis
  ```

---

### **5. Application Deployment**

#### **Backend Deployment:**

- [ ] **Install dependencies**
  ```bash
  cd backend
  pip install -r requirements.txt
  pip install gunicorn  # Production server
  ```

- [ ] **Configure Gunicorn**
  ```bash
  # gunicorn_config.py
  bind = "0.0.0.0:8000"
  workers = 4
  worker_class = "uvicorn.workers.UvicornWorker"
  accesslog = "/var/log/gunicorn/access.log"
  errorlog = "/var/log/gunicorn/error.log"
  loglevel = "info"
  ```

- [ ] **Create systemd service**
  ```ini
  # /etc/systemd/system/payroll-api.service
  [Unit]
  Description=Payroll API
  After=network.target

  [Service]
  User=www-data
  Group=www-data
  WorkingDirectory=/var/www/payroll/backend
  Environment="PATH=/var/www/payroll/venv/bin"
  ExecStart=/var/www/payroll/venv/bin/gunicorn -c gunicorn_config.py main:app

  [Install]
  WantedBy=multi-user.target
  ```

- [ ] **Start service**
  ```bash
  sudo systemctl start payroll-api
  sudo systemctl enable payroll-api
  ```

#### **Frontend Deployment:**

- [ ] **Build frontend**
  ```bash
  cd frontend
  npm install
  npm run build
  ```

- [ ] **Configure nginx**
  ```nginx
  # /etc/nginx/sites-available/payroll
  server {
      listen 80;
      server_name yourdomain.com www.yourdomain.com;
      return 301 https://$server_name$request_uri;
  }

  server {
      listen 443 ssl http2;
      server_name yourdomain.com www.yourdomain.com;

      ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
      ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

      # Frontend
      location / {
          root /var/www/payroll/frontend/.output/public;
          try_files $uri $uri/ /index.html;
      }

      # API proxy
      location /api/ {
          proxy_pass http://localhost:8000;
          proxy_set_header Host $host;
          proxy_set_header X-Real-IP $remote_addr;
          proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
          proxy_set_header X-Forwarded-Proto $scheme;
      }
  }
  ```

- [ ] **Enable site**
  ```bash
  sudo ln -s /etc/nginx/sites-available/payroll /etc/nginx/sites-enabled/
  sudo nginx -t
  sudo systemctl reload nginx
  ```

---

### **6. Monitoring & Logging**

- [ ] **Set up application monitoring**
  - [ ] Install Sentry for error tracking
  - [ ] Configure New Relic/DataDog (optional)
  - [ ] Set up uptime monitoring (UptimeRobot, Pingdom)

- [ ] **Configure logging**
  ```python
  # backend/logging_config.py
  LOGGING = {
      'version': 1,
      'handlers': {
          'file': {
              'class': 'logging.handlers.RotatingFileHandler',
              'filename': '/var/log/payroll/app.log',
              'maxBytes': 10485760,  # 10MB
              'backupCount': 10,
          },
      },
      'root': {
          'level': 'INFO',
          'handlers': ['file'],
      },
  }
  ```

- [ ] **Set up log rotation**
  ```bash
  # /etc/logrotate.d/payroll
  /var/log/payroll/*.log {
      daily
      rotate 14
      compress
      delaycompress
      notifempty
      create 0640 www-data www-data
  }
  ```

---

### **7. Performance Optimization**

- [ ] **Enable gzip compression** (nginx)
  ```nginx
  gzip on;
  gzip_types text/plain text/css application/json application/javascript;
  gzip_min_length 1000;
  ```

- [ ] **Set up CDN** (Cloudflare, AWS CloudFront)
  - [ ] Configure DNS
  - [ ] Enable caching
  - [ ] Configure SSL

- [ ] **Database optimization**
  - [ ] Add indexes on frequently queried columns
  - [ ] Configure connection pooling
  - [ ] Enable query caching

- [ ] **Redis optimization**
  - [ ] Set appropriate maxmemory
  - [ ] Configure eviction policy
  - [ ] Enable persistence

---

### **8. Backup Strategy**

- [ ] **Database backups**
  ```bash
  # Daily full backup
  0 2 * * * pg_dump payroll_production | gzip > /backups/db/payroll_$(date +\%Y\%m\%d).sql.gz

  # Keep last 30 days
  find /backups/db -name "payroll_*.sql.gz" -mtime +30 -delete
  ```

- [ ] **Redis backups**
  ```bash
  # Backup RDB file
  0 3 * * * cp /var/lib/redis/dump.rdb /backups/redis/dump_$(date +\%Y\%m\%d).rdb
  ```

- [ ] **Application backups**
  ```bash
  # Backup application files
  0 4 * * * tar -czf /backups/app/payroll_$(date +\%Y\%m\%d).tar.gz /var/www/payroll
  ```

- [ ] **Test restore procedures**

---

### **9. Security Audit**

- [ ] **Run security scan**
  ```bash
  # Check for vulnerabilities
  pip install safety
  safety check

  # Frontend
  npm audit
  ```

- [ ] **Test authentication**
  - [ ] Login works
  - [ ] Logout invalidates tokens
  - [ ] Rate limiting works
  - [ ] Account lockout works
  - [ ] Password reset works

- [ ] **Test authorization**
  - [ ] Role-based access control works
  - [ ] Permissions enforced correctly
  - [ ] No unauthorized access possible

- [ ] **Test security headers**
  ```bash
  curl -I https://yourdomain.com
  # Check for:
  # - X-Content-Type-Options: nosniff
  # - X-Frame-Options: DENY
  # - Strict-Transport-Security
  # - Content-Security-Policy
  ```

---

### **10. Performance Testing**

- [ ] **Load testing**
  ```bash
  # Install locust
  pip install locust

  # Run load test
  locust -f loadtest.py --host=https://yourdomain.com
  ```

- [ ] **Stress testing**
  - [ ] Test with 100 concurrent users
  - [ ] Test with 500 concurrent users
  - [ ] Test with 1000 concurrent users

- [ ] **Monitor response times**
  - [ ] API endpoints < 200ms
  - [ ] Page loads < 2s
  - [ ] Database queries < 100ms

---

### **11. Final Checks**

- [ ] **Health check endpoint**
  ```bash
  curl https://yourdomain.com/health
  # Should return: {"status": "healthy"}
  ```

- [ ] **All services running**
  ```bash
  sudo systemctl status payroll-api
  sudo systemctl status nginx
  sudo systemctl status redis
  sudo systemctl status postgresql
  ```

- [ ] **SSL certificate valid**
  ```bash
  openssl s_client -connect yourdomain.com:443
  ```

- [ ] **DNS configured correctly**
  ```bash
  nslookup yourdomain.com
  ```

- [ ] **Firewall rules active**
  ```bash
  sudo ufw status
  ```

---

## 🚀 DEPLOYMENT STEPS

### **Step 1: Prepare Server**
```bash
# Update system
sudo apt-get update && sudo apt-get upgrade -y

# Install dependencies
sudo apt-get install -y python3-pip python3-venv nginx postgresql redis-server
```

### **Step 2: Deploy Application**
```bash
# Clone repository
git clone https://github.com/yourcompany/payroll.git /var/www/payroll

# Set up virtual environment
cd /var/www/payroll/backend
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# Build frontend
cd ../frontend
npm install
npm run build
```

### **Step 3: Configure Services**
```bash
# Copy configuration files
sudo cp payroll-api.service /etc/systemd/system/
sudo cp nginx.conf /etc/nginx/sites-available/payroll

# Enable services
sudo systemctl enable payroll-api
sudo systemctl enable nginx
sudo systemctl enable redis
sudo systemctl enable postgresql
```

### **Step 4: Start Services**
```bash
# Start all services
sudo systemctl start payroll-api
sudo systemctl start nginx
sudo systemctl start redis
sudo systemctl start postgresql

# Check status
sudo systemctl status payroll-api
```

### **Step 5: Verify Deployment**
```bash
# Test health endpoint
curl https://yourdomain.com/health

# Test login
curl -X POST https://yourdomain.com/api/v1/auth/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin@jafasol.com&password=3r14F65gMv"
```

---

## 📊 POST-DEPLOYMENT MONITORING

### **Day 1:**
- [ ] Monitor error logs
- [ ] Check performance metrics
- [ ] Verify all features working
- [ ] Test user workflows

### **Week 1:**
- [ ] Review security logs
- [ ] Check backup success
- [ ] Monitor resource usage
- [ ] Gather user feedback

### **Month 1:**
- [ ] Performance optimization
- [ ] Security audit
- [ ] Backup verification
- [ ] Capacity planning

---

## 🆘 ROLLBACK PROCEDURE

If deployment fails:

```bash
# Stop new version
sudo systemctl stop payroll-api

# Restore database backup
psql payroll_production < /backups/db/payroll_YYYYMMDD.sql

# Restore application
tar -xzf /backups/app/payroll_YYYYMMDD.tar.gz -C /var/www/

# Start old version
sudo systemctl start payroll-api

# Verify
curl https://yourdomain.com/health
```

---

## ✅ DEPLOYMENT COMPLETE!

Once all checks pass:
- ✅ System is live
- ✅ Monitoring active
- ✅ Backups configured
- ✅ Security hardened
- ✅ Performance optimized

**Your payroll system is now in production!** 🎉

---

## 📞 SUPPORT CONTACTS

- **Technical Lead:** [Your Name]
- **DevOps:** [DevOps Team]
- **Emergency:** [Emergency Contact]

**Documentation:** See `COMPLETE_IMPLEMENTATION.md` for full details.
