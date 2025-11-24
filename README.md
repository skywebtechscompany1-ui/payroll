# Payroll Management System

A modern, comprehensive payroll management system built with Vue.js/Nuxt.js frontend and Python FastAPI backend.

## 🚀 Features

### Core Features
- **Employee Management**: Complete employee profiles with onboarding workflows
- **Attendance Management**: Multiple capture methods (manual, biometric, GPS-based)
- **Leave Management**: Configurable policies, multi-level approvals, automated calculations
- **Payroll Processing**: Automated calculations, multiple pay frequencies, statutory compliance
- **Advanced Analytics**: Real-time dashboards, custom reports, data visualization
- **Role-Based Access Control**: Granular permissions for different user roles

### Technical Features
- **Modern Tech Stack**: Vue.js 3 + Nuxt.js frontend, Python FastAPI backend
- **Responsive Design**: Mobile-first design that works on all devices
- **Dark/Light Mode**: User preference support with system detection
- **Real-time Updates**: Live data synchronization and notifications
- **Progressive Web App**: Offline support and native-like experience
- **Security**: JWT authentication, RBAC, audit trails, data encryption

## 🏗️ Architecture

### Frontend (Nuxt.js)
- **Framework**: Vue.js 3 + Nuxt.js 3
- **Styling**: Tailwind CSS with custom design system
- **State Management**: Pinia
- **UI Components**: Custom component library with accessibility support
- **Charts**: Chart.js integration for analytics
- **TypeScript**: Full type safety throughout

### Backend (FastAPI)
- **Framework**: Python 3.11+ with FastAPI
- **Database**: PostgreSQL with SQLAlchemy ORM
- **Authentication**: JWT with refresh tokens
- **Background Tasks**: Celery with Redis
- **API Documentation**: Auto-generated OpenAPI/Swagger docs
- **Validation**: Pydantic schemas for data integrity

### Infrastructure
- **Containerization**: Docker with Docker Compose
- **Reverse Proxy**: Nginx with SSL/TLS support
- **Caching**: Redis for session management and caching
- **Monitoring**: Flower for Celery task monitoring
- **Security**: Rate limiting, CORS, security headers

## 📋 Prerequisites

- Docker & Docker Compose
- Node.js 18+ (for local development)
- Python 3.11+ (for local development)
- PostgreSQL 15+ (if not using Docker)

## 🛠️ Installation

### Quick Start with Docker

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd payroll
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Start the services**
   ```bash
   docker-compose up -d
   ```

4. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000
   - API Documentation: http://localhost:8000/docs
   - Flower Monitoring: http://localhost:5555

### Local Development

#### Backend Setup

1. **Navigate to backend directory**
   ```bash
   cd backend
   ```

2. **Create virtual environment**
   ```bash
   python -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   ```

3. **Install dependencies**
   ```bash
   pip install -r requirements/dev.txt
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database configuration
   ```

5. **Run database migrations**
   ```bash
   alembic upgrade head
   ```

6. **Start the development server**
   ```bash
   uvicorn main:app --reload --host 0.0.0.0 --port 8000
   ```

#### Frontend Setup

1. **Navigate to frontend directory**
   ```bash
   cd frontend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your API URL
   ```

4. **Start the development server**
   ```bash
   npm run dev
   ```

## 📊 Database Schema

The system uses PostgreSQL with the following main tables:

- **users**: Employee information and authentication
- **departments**: Organizational departments
- **designations**: Job titles and roles
- **attendance_records**: Employee attendance data
- **leave_applications**: Leave requests and approvals
- **payroll_periods**: Payroll processing periods
- **payslips**: Generated payslips and salary details

## 🔐 Security Features

- **Authentication**: JWT with access/refresh tokens
- **Authorization**: Role-based access control (RBAC)
- **Password Security**: Bcrypt hashing with strength validation
- **API Security**: Rate limiting, CORS, input validation
- **Data Protection**: Encrypted sensitive data, audit trails
- **Session Management**: Secure session handling with Redis

## 🎨 UI/UX Features

- **Responsive Design**: Mobile-first approach
- **Dark Mode**: System preference detection and manual toggle
- **Accessibility**: WCAG 2.1 compliance with ARIA labels
- **Performance**: Code splitting, lazy loading, image optimization
- **Internationalization**: Multi-language support (i18n ready)

## 📈 Analytics & Reporting

- **Dashboard**: Real-time metrics and KPI tracking
- **Custom Reports**: Drag-and-drop report builder
- **Data Visualization**: Interactive charts and graphs
- **Export Options**: PDF, Excel, CSV exports
- **Scheduled Reports**: Automated report generation

## 🧪 Testing

### Backend Tests
```bash
cd backend
pytest tests/
```

### Frontend Tests
```bash
cd frontend
npm run test        # Unit tests
npm run test:e2e    # End-to-end tests
```

## 📦 Deployment

### Production Deployment

1. **Configure production environment**
   ```bash
   cp .env.example .env
   # Update all production values
   ```

2. **Generate SSL certificates**
   ```bash
   # Place certificates in nginx/ssl/
   ```

3. **Deploy with Docker Compose**
   ```bash
   docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
   ```

### Environment Variables

Key environment variables to configure:

- `SECRET_KEY`: JWT signing key (change in production)
- `DATABASE_URL`: PostgreSQL connection string
- `REDIS_URL`: Redis connection string
- `ALLOWED_HOSTS`: Comma-separated list of allowed domains
- `API_BASE_URL`: Backend API URL for frontend

## 🔧 Configuration

### Backend Configuration

Edit `backend/app/core/config.py` for advanced settings:
- JWT token expiration
- File upload limits
- Email settings
- Rate limiting rules

### Frontend Configuration

Edit `frontend/nuxt.config.ts` for:
- API endpoints
- Build settings
- Plugin configuration
- Runtime config

## 📚 API Documentation

Once running, access the interactive API documentation:
- **Swagger UI**: http://localhost:8000/docs
- **ReDoc**: http://localhost:8000/redoc

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the API documentation
- Review the configuration examples

## 🔄 Migration from Laravel

For migration from the existing Laravel system:

1. **Export existing data** from Laravel database
2. **Run data migration scripts** (provided in `/scripts/migrate/`)
3. **Verify data integrity** after migration
4. **Update user credentials** if needed

## 🗺️ Roadmap

- [ ] Mobile app development
- [ ] Advanced payroll analytics
- [ ] Integration with accounting systems
- [ ] Biometric device integration
- [ ] Advanced reporting features
- [ ] Multi-company support
- [ ] API rate limiting enhancements
- [ ] Performance optimization
- [ ] Enhanced security features