# K3-VirtuAI - Refactored Architecture

## Overview

K3-VirtuAI is a K3 (Safety, Health, and Environment) virtual reality simulation system that has been refactored from a monolithic structure into a clean MVC (Model-View-Controller) architecture.

### 📱 Mobile Responsive

Aplikasi telah dioptimalkan untuk perangkat mobile dengan:

- **Responsive Design**: Menyesuaikan layout untuk desktop, tablet, dan smartphone
- **Hamburger Navigation**: Menu otomatis berubah di layar kecil
- **Touch-Friendly UI**: Button dan input optimal untuk sentuhan
- **Dark Mode Support**: Otomatis apply dark mode sesuai preference device
- **Performance Optimized**: Lightweight CSS untuk mobile bandwidth

**Untuk panduan lengkap mobile, lihat [MOBILE_GUIDE.md](MOBILE_GUIDE.md)**  
**Untuk testing responsiveness, buka [mobile-test.html](mobile-test.html)**

### 🔒 Admin Security Lockdown

Sistem keamanan untuk mencegah registrasi sebagai admin:

- **Frontend Protection**: Option admin tidak tersedia di form register
- **Backend Validation**: Server reject jika ada upaya bypass
- **Admin Management**: Admin tetap bisa menambah admin baru melalui dashboard
- **Data Preservation**: Akun admin existing tidak terpengaruh

**Untuk detail keamanan admin, lihat [ADMIN_SECURITY.md](ADMIN_SECURITY.md)**

## Project Structure

```
k3_project/
├── public/                    # Web root directory
│   └── index.php             # Main application entry point
├── config/                   # Configuration files
│   └── database.php          # Database connection and utilities
├── backend/                  # Backend logic
│   ├── controllers/          # Controller classes
│   │   └── DashboardController.php
│   ├── api/                  # API endpoints
│   │   └── simulation.php    # AJAX API for simulation operations
│   └── [other PHP files]     # Legacy backend files (to be migrated)
├── frontend/                 # Frontend assets and templates
│   ├── assets/
│   │   ├── css/
│   │   │   └── dashboard.css
│   │   └── js/
│   │       └── dashboard.js
│   └── templates/
│       └── dashboard.php
└── [other files]             # Legacy files (script.js, style.css, etc.)
```

## Architecture Benefits

1. **Separation of Concerns**: Backend logic, frontend presentation, and configuration are now properly separated
2. **Maintainability**: Easier to modify and extend individual components
3. **Scalability**: Better structure for adding new features
4. **Testing**: Components can be tested independently
5. **Security**: Sensitive backend logic is separated from public access

## Key Components

### Entry Point (`public/index.php`)

- Main application entry point
- Routes requests to appropriate controllers
- Handles session initialization

### Configuration (`config/database.php`)

- Database connection management
- Session handling utilities
- User authentication helpers

### Controller (`backend/controllers/DashboardController.php`)

- Handles business logic for the dashboard
- Processes user actions and admin functions
- Retrieves data for views

### API (`backend/api/simulation.php`)

- RESTful endpoints for AJAX operations
- Handles simulation start/stop, scoring, and statistics

### Template (`frontend/templates/dashboard.php`)

- HTML structure for the dashboard
- Contains PHP for dynamic content
- Includes CSS and JavaScript assets

### Assets

- `frontend/assets/css/dashboard.css`: Complete styling
- `frontend/assets/js/dashboard.js`: Client-side functionality

## Development Server

To run the application:

```bash
cd /d c:\xampp\htdocs\k3_project
php -S localhost:8000 -t public
```

Then visit `http://localhost:8000` in your browser.

## Migration Notes

- Original `index.php` has been split into multiple files
- All functionality has been preserved
- Navigation links updated to reflect new structure
- Legacy files remain for reference but are not used in new structure

## Future Improvements

1. Migrate remaining backend files to MVC structure
2. Implement proper routing system
3. Add input validation and sanitization
4. Implement proper error handling
5. Add unit tests for controllers and models
