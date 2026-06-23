# Laravel Shopify Product Import System

A complete Laravel 12 application for importing products from CSV files to Shopify with asynchronous processing, real-time tracking, and comprehensive error handling.

### Required Software

| Software | Minimum Version | Check Command |
|----------|----------------|---------------|
| PHP | 8.2 or higher | `php -v` |
| Composer | Latest | `composer -v` |
| MySQL | 5.7 or higher | `mysql --version` |
| Node.js | 16.x or higher | `node -v` |
| NPM | 8.x or higher | `npm -v` |
| Git | Latest | `git --version` |


## Step-by-Step Setup

### Step 1: Clone the Repository

Clone the project from GitHub to your local machine.

```bash
# Clone the repository
git clone https://github.com/kamanianand/shopify-import-system.git
```

# Navigate into the project directory
```bash
cd laravel-shopify-import
```

### Step 2: Install PHP Dependencies
# Install all required PHP packages using Composer.
```bash
composer install
```

### Step 3: Install NPM Dependencies
#Install frontend assets and dependencies.
```bash
npm install
```

### Step 4: Create Environment File
#Copy the example environment file to create your own configuration.
```bash
cp .env.example .env
```

### Step 5: Generate Application Key
#Generate a unique encryption key for your Laravel application.
```bash
php artisan key:generate
```

### Step 6: Create Database
#Create the MySQL database for your application.
# Login to MySQL
```bash
mysql -u root -p

# Enter your MySQL password when prompted
# Then run this command inside MySQL
```bash
CREATE DATABASE shopify_import CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Exit MySQL
exit;
```

### Step 7: Update Environment Configuration
#Open the .env file in your code editor and update these values:
# Open .env file (use any text editor)
```bash
code .env
#Update these lines:
# Database Configuration - Update with your credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yourdabasename
DB_USERNAME=root          # Your MySQL username
DB_PASSWORD=yourpassword  # Your MySQL password

# Shopify Configuration - Using your credentials
SHOPIFY_API_KEY=#
SHOPIFY_STORE_URL=#
SHOPIFY_ACCESS_TOKEN=#
SHOPIFY_API_VERSION=#
SHOPIFY_COLLECTION_ID=#

# Queue Configuration - Use database for simplicity
QUEUE_CONNECTION=database

BROADCAST_DRIVER=log
CACHE_DRIVER=file
```

### Step 8: Run Database Migrations
#Create all database tables required for the application.
```bash
php artisan migrate
```

### Step 9: Create Storage Link & clear cache
#Create a symbolic link from public/storage to storage/app/public for file uploads.
```bash
php artisan storage:link
php artisan optimize:clear
```

### Step 10: Build Frontend Assets
#Compile CSS and JavaScript assets.
# For production (recommended)
```bash
npm run build

# OR for development with hot reload
npm run dev
```

### Step 11: Run Queue Worker (IMPORTANT!)
#Open a new terminal window and keep it running.
# Navigate to your project directory in the new terminal
```bash
cd laravel-shopify-import

# Start the queue worker
php artisan queue:work
```

### Step 12: Start the Application
#Open another new terminal window and start the Laravel development server.
# Navigate to your project directory in the new terminal
```bash
cd laravel-shopify-import

# Start the development server
php artisan serve
```

Step 13: Access the Application
#Open your web browser and navigate to:
http://localhost:8000

Testing the Application
Visit http://localhost:8000
Click "New Import" button
Click "Choose CSV File" or drag and drop your CSV file
Click "Upload & Process"
Wait for success message






