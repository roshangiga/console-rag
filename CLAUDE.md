# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Console RAG Document Management System**
- Company: Mauritius Telecom (Innovation Department)
- Architecture: Full-stack application with Laravel backend and Vue.js + Vuetify frontend
- Purpose: Document management system for RAG (Retrieval-Augmented Generation) with hierarchical directory structure

## Project Structure

```
project-root/
├── backend/     # Laravel API (Laravel 10+)
└── frontend/    # Vue.js + Vuetify SPA
```

## Development Commands

### Backend (Laravel)
```bash
cd backend
composer install
php artisan serve
php artisan migrate
php artisan db:seed
php artisan test
```

### Frontend (Vue.js)
```bash
cd frontend
npm install
npm run dev
npm run build
npm run lint
npm test
```

## Architecture Overview

### Backend Architecture (Laravel)
- **Authentication**: Laravel Sanctum + JWT tokens, Microsoft Entra ID integration (placeholder)
- **API Pattern**: RESTful API with consistent resource formatting
- **Data Layer**: Repository pattern with service layer for business logic
- **Validation**: Form Request classes for input validation

### Database Schema
- **users**: Authentication with optional `entra_id` for Microsoft integration
- **directories**: Self-referencing hierarchical structure (`parent_id`)
- **documents**: File metadata with versioning, type enum (QA, Image, Process_Chart, Presentation, General_Doc), and status tracking
- **document_tags**: Many-to-many relationship for tags (Internal, Enterprise, Public)

### Frontend Architecture (Vue.js)
- **State Management**: Pinia stores for application state
- **Routing**: Vue Router with authentication guards
- **Components**: Vuetify 3 component library with composition API
- **Services**: Separate API service layer using Axios
- **Theme**: Dark/light mode toggle with localStorage persistence

## Key Features

### Directory Management
- Hierarchical tree structure (3+ levels deep)
- Expandable/collapsible navigation
- Drag-and-drop reorganization (UI only)
- Breadcrumb navigation

### Document Management
- File upload with metadata (name, type, version, purpose, tags)
- Status tracking (SUCCESS, FAILED, UPDATING) with color coding
- Filtering by type, status, and tags
- Search functionality
- Processing API simulation with random status returns

### UI/UX Patterns
- Vuetify components for consistent design
- Loading states and error boundaries
- Confirmation dialogs for destructive actions
- Snackbar notifications for user feedback
- Empty states with helpful messaging

## Implementation Notes

- All file upload APIs are dummy implementations (return success without processing)
- Document processing API simulates delay and returns random status
- Microsoft Entra ID integration uses placeholder environment variables
- Seeders create 3-4 parent directories with nested children and 10-15 sample documents
- Repository pattern for data access with service layer abstraction