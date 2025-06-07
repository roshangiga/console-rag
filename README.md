# Console - Document Management System

A comprehensive RAG (Retrieval-Augmented Generation) document management system built with Laravel 12 and Vue.js 3.

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 18+
- MySQL 8.0+

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd console-rag
   ```

2. **Set up MySQL** (if not installed)
   ```bash
   # Run the MySQL setup script
   chmod +x setup-mysql.sh
   ./setup-mysql.sh
   
   # Or manually configure MySQL
   sudo mysql < fix-mysql.sql
   ```

3. **Start the application**
   ```bash
   # Linux/Mac
   chmod +x start.sh restart.sh stop.sh
   ./start.sh
   
   # Windows
   start.bat
   ```

## 🎮 Control Scripts

### Linux/Mac
- **Start**: `./start.sh` - Start all services
- **Stop**: `./stop.sh` - Gracefully stop all services  
- **Restart**: `./restart.sh` - Stop and restart all services

### Windows
- **Start**: `start.bat` - Start all services
- **Stop**: `stop.bat` - Gracefully stop all services
- **Restart**: `restart.bat` - Stop and restart all services

## 🌐 Access Points

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api
- **API Documentation**: http://localhost:8000/api/documentation
- **phpMyAdmin**: http://localhost:8080

## 🔐 Demo Credentials

- **Email**: admin@telecom.mu
- **Password**: password

## 🏗️ Architecture

### Backend (Laravel 12)
- **Authentication**: Laravel Sanctum + JWT
- **Database**: MySQL with hierarchical directory structure
- **API**: RESTful endpoints with OpenAPI documentation
- **Features**: Document management, user authentication, tag system

### Frontend (Vue.js 3)
- **Framework**: Vue 3 with Composition API
- **UI Library**: Vuetify 3
- **State Management**: Pinia
- **Features**: Responsive design, dark/light theme, real-time updates

### Database Schema
- **users**: Authentication with optional Entra ID
- **directories**: Hierarchical structure (3+ levels)
- **documents**: File metadata with versioning and status
- **document_tags**: Tagging system (Internal, Enterprise, Public)

## 📋 Features

### ✅ Implemented
- User authentication with JWT tokens
- Hierarchical directory management
- Document upload simulation with metadata
- Status tracking (SUCCESS, FAILED, UPDATING)
- Search and filtering capabilities
- Tag-based organization
- Responsive UI with theme switching
- API documentation with Swagger
- Database management with phpMyAdmin

### 🔄 Planned
- Real file processing integration
- Microsoft Entra ID authentication
- Advanced search with RAG capabilities
- Bulk operations
- File versioning system

## 🛠️ Development

### Backend Commands
```bash
cd backend
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

### Frontend Commands
```bash
cd frontend
npm install
npm run dev
npm run build
```

### API Documentation
The API is fully documented with OpenAPI 3.0 specification. Visit http://localhost:8000/api/documentation for interactive documentation.

## 🗄️ Database Management

Access phpMyAdmin at http://localhost:8080 to:
- Browse database structure
- View and edit records
- Execute SQL queries
- Monitor database performance

## 🏢 Company Information

**Project**: Console  
**Company**: Mauritius Telecom  
**Department**: Innovation Team  
**Contact**: innovation@maurtiustelecom.mu

## 📄 License

This project is proprietary software developed for Mauritius Telecom.