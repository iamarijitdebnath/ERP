# Science Surgical ERP

A comprehensive Enterprise Resource Planning (ERP) system custom-built for Science Surgical to streamline operations across human resources, inventory management, and sales.

## 🚀 Technologies

This application is built using the **TALL-like** stack integrated with Flowbite components for a modern, responsive user experience:

- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
- **UI Components**: Flowbite
- **Asset Bundler**: Vite
- **Database**: SQLite (default for development, configurable via `.env`)
- **Key Packages**:
  - `barryvdh/laravel-dompdf` (PDF generation)
  - `maatwebsite/excel` (Excel exports)

## 📦 Core Modules

The ERP is modularized into distinct functional areas:

- **System Module**: Core system configurations, roles, permissions, and general settings.
- **HRMS (Human Resource Management System)**: Employee management, attendance, leave tracking, and payroll.
- **Inventory Module**: Product catalog, stock tracking, purchase orders, and supplier management.
- **Sales Module**: Customer management, order processing, handling quotations, and invoicing.

## 🛠️ Installation & Setup

Follow these instructions to set up the project locally.

### Prerequisites

Ensure you have the following installed on your machine:

- PHP >= 8.2
- Composer
- Node.js (v20+ recommended) & npm

### Quick Setup

This project uses custom Composer scripts to automate the setup process.

1. **Clone the repository:**
   ```bash
   git clone <your-repository-url>
   cd erp.sciencesurgical.in
   ```

2. **Run the setup script:**
   This single command will install PHP dependencies, create the `.env` file, generate the app key, run database migrations, install Node packages, and build the frontend assets.
   ```bash
   composer run setup
   ```

### Manual Setup (Alternative)

If you prefer to run the steps manually:

1. Install PHP dependencies:
   ```bash
   composer install
   ```
2. Setup environment variables:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Create the SQLite database (if using SQLite):
   ```bash
   touch database/database.sqlite
   ```
4. Run migrations:
   ```bash
   php artisan migrate
   ```
5. Install and build frontend dependencies:
   ```bash
   npm install
   npm run build
   ```

## 💻 Running the Application

To start the local development server with Vite hot-module-replacement (HMR), queue listener, and Laravel queue workers built-in concurrently:

```bash
composer run dev
```

The application will be accessible at `http://127.0.0.1:8000`.

## 🧪 Testing

To run the automated test suite:

```bash
composer run test
```
Or via Artisan:
```bash
php artisan test
```


