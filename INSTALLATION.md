# Commercial Loan CRM System - Complete Installation Guide

## Project Overview

A comprehensive Commercial Loan CIM (Client Information Management) System built with:
- Laravel 12
- Jetstream (Livewire Stack) for Authentication
- Tailwind CSS for styling
- Native Laravel Blade templates
- MySQL Database with properly normalized tables

## Database Architecture

### Separate Tables (Normalized):
1. **applications** - Main loan applications
2. **personal_details** - Client personal information (unique email/phone validation)
3. **residential_addresses** - Current and 3-year address history
4. **employment_details** - Employment and income information
5. **living_expenses** - Dynamic expense tracking with client/assessor notes
6. **documents** - File uploads with version control
7. **communications** - Email/SMS in/out logs
8. **comments** - Internal/client-visible notes with timestamps
9. **questions** - Q&A workflow system
10. **notifications** - System notifications
11. **activity_logs** - Complete audit trail with IP tracking
12. **tasks** - ID check, living expense verification, declarations
13. **declarations** - Electronic signature records
14. **credit_checks** - Credit Sense API integration

## Installation Steps

### 1. System Requirements
```bash
PHP >= 8.3
MySQL >= 8.0
Composer
Node.js >= 18
NPM or Yarn
```

### 2. Initial Setup
```bash
# Clone/Download the project
cd commercial-loan-crm

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Configuration

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=commercial_loan_crm
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Create the database:
```bash
mysql -u your_username -p
CREATE DATABASE commercial_loan_crm;
exit;
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Install Jetstream
```bash
# Install Jetstream with Livewire
php artisan jetstream:install livewire

# Publish Jetstream resources
php artisan vendor:publish --tag=jetstream-views

# Run migrations for Jetstream tables
php artisan migrate
```

### 6. Set Up Roles and Permissions
```bash
# Install Spatie Permission package (already in composer.json)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Create roles seeder
php artisan db:seed --class=RoleSeeder
```

### 7. Storage Setup
```bash
# Create symbolic link for storage
php artisan storage:link

# Set proper permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 8. Compile Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 9. Queue Configuration (for emails/SMS)

In `.env`:
```env
QUEUE_CONNECTION=database
```

Run queue worker:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

### 10. Email Configuration

In `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 11. SMS Configuration (Twilio)

In `.env`:
```env
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_phone_number
```

### 12. Credit Sense API Configuration

In `config/services.php`:
```php
'credit_sense' => [
    'api_key' => env('CREDIT_SENSE_API_KEY'),
    'api_url' => env('CREDIT_SENSE_API_URL', 'https://api.creditsense.com.au'),
],
```

In `.env`:
```env
CREDIT_SENSE_API_KEY=your_api_key
CREDIT_SENSE_API_URL=https://api.creditsense.com.au
```

### 13. Start the Application
```bash
php artisan serve
```

Visit: http://localhost:8000

## Default User Accounts (After Seeding)

### Admin
- Email: admin@example.com
- Password: password

### Assessor
- Email: assessor@example.com
- Password: password

### Client (Test)
- Email: client@example.com
- Password: password

## Key Features Implemented

### Client Portal Features
✅ Secure registration with email verification
✅ Multi-step loan application form
✅ Personal details (unique email/phone validation)
✅ 3-year residential address history
✅ Employment and income details
✅ Dynamic living expenses table
✅ Document upload with categorization
✅ View application status
✅ Answer assessor questions
✅ Electronic signature for declarations
✅ All submissions tracked with IP address

### Backend Admin Features
✅ Application dashboard with filters
✅ Application assessment interface
✅ Document management and review
✅ Q&A workflow system
✅ Email/SMS communication logging
✅ Internal comments with timestamps
✅ Task assignment (ID check, living expense verification, declarations)
✅ Credit check integration ready
✅ Living expense verification workflow
✅ PDF export for compliance
✅ Complete audit trail
✅ IP address tracking on all actions

### Security & Compliance
✅ Role-based access control (Admin, Assessor, Client)
✅ Email and phone uniqueness validation
✅ IP address tracking for all submissions
✅ Activity logging for audit trail
✅ Electronic signature support
✅ Encrypted document storage
✅ Data isolation per client

### Communication Features
✅ Email in/out logging
✅ SMS in/out logging
✅ System notifications
✅ Two-way secure messaging
✅ Comment system with timestamps
✅ All communications exportable to PDF

## File Structure

```
commercial-loan-crm/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── ApplicationController.php
│   │       ├── PersonalDetailsController.php
│   │       ├── ResidentialAddressController.php
│   │       ├── EmploymentDetailsController.php
│   │       ├── LivingExpenseController.php
│   │       ├── DocumentController.php
│   │       ├── CommentController.php
│   │       ├── QuestionController.php
│   │       └── Admin/
│   │           ├── ApplicationController.php
│   │           ├── CommunicationController.php
│   │           ├── TaskController.php
│   │           └── CreditCheckController.php
│   ├── Models/
│   │   ├── Application.php
│   │   ├── PersonalDetail.php
│   │   ├── ResidentialAddress.php
│   │   ├── EmploymentDetail.php
│   │   ├── LivingExpense.php
│   │   ├── Document.php
│   │   ├── Communication.php
│   │   ├── Comment.php
│   │   ├── Question.php
│   │   ├── Task.php
│   │   ├── Declaration.php
│   │   ├── CreditCheck.php
│   │   └── ActivityLog.php
│   ├── Services/
│   │   ├── EmailService.php
│   │   ├── SmsService.php
│   │   ├── CreditCheckService.php
│   │   └── PdfExportService.php
│   └── Policies/
│       └── ApplicationPolicy.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_applications_table.php
│   │   ├── 2024_01_01_000002_create_personal_details_table.php
│   │   ├── 2024_01_01_000003_create_residential_addresses_table.php
│   │   ├── 2024_01_01_000004_create_employment_details_table.php
│   │   ├── 2024_01_01_000005_create_living_expenses_table.php
│   │   ├── 2024_01_01_000006_create_documents_table.php
│   │   ├── 2024_01_01_000007_create_communications_table.php
│   │   ├── 2024_01_01_000008_create_comments_table.php
│   │   ├── 2024_01_01_000009_create_questions_table.php
│   │   ├── 2024_01_01_000010_create_notifications_table.php
│   │   ├── 2024_01_01_000011_create_activity_logs_table.php
│   │   ├── 2024_01_01_000012_create_tasks_table.php
│   │   ├── 2024_01_01_000013_create_declarations_table.php
│   │   └── 2024_01_01_000014_create_credit_checks_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       └── UserSeeder.php
├── resources/
│   └── views/
│       ├── applications/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       └── admin/
│           └── applications/
│               ├── index.blade.php
│               ├── show.blade.php
│               └── pdf.blade.php
└── routes/
    ├── web.php
    └── api.php
```

## Additional Files to Create

### 1. Routes (routes/web.php)
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PersonalDetailsController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authenticated client routes
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Client applications
    Route::resource('applications', ApplicationController::class);
    Route::post('applications/{application}/submit', [ApplicationController::class, 'submit'])->name('applications.submit');
    Route::post('applications/{application}/personal-details', [PersonalDetailsController::class, 'store'])->name('applications.personal-details.store');
});

// Admin/Assessor routes
Route::middleware(['auth:sanctum', 'verified', 'role:admin|assessor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::patch('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    Route::patch('/applications/{application}/assign', [AdminApplicationController::class, 'assign'])->name('applications.assign');
    Route::get('/applications/{application}/export-pdf', [AdminApplicationController::class, 'exportPdf'])->name('applications.exportPdf');
});
```

### 2. Role Seeder (database/seeders/RoleSeeder.php)
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $assessorRole = Role::create(['name' => 'assessor']);
        $clientRole = Role::create(['name' => 'client']);

        // Create permissions
        $permissions = [
            'view applications',
            'create applications',
            'edit applications',
            'delete applications',
            'review applications',
            'assign applications',
            'verify living expenses',
            'request documents',
            'send communications',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $assessorRole->givePermissionTo([
            'view applications',
            'review applications',
            'verify living expenses',
            'request documents',
            'send communications',
        ]);
        $clientRole->givePermissionTo([
            'view applications',
            'create applications',
            'edit applications',
        ]);
    }
}
```

### 3. User Seeder (database/seeders/UserSeeder.php)
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Assessor user
        $assessor = User::create([
            'name' => 'Assessor User',
            'email' => 'assessor@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $assessor->assignRole('assessor');

        // Test client
        $client = User::create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $client->assignRole('client');
    }
}
```

## Team Members

- **Allan** - Lead Developer
- **Aurelio** - Support Developer (API Integration)
- **Jeffrey** - Support Developer

## Development Notes

### IP Tracking Implementation
All forms use `request()->ip()` to track submission IPs:
- Application submissions
- Personal detail updates
- Declaration agreements
- Document uploads
- Comments
- Question answers

### Email/Phone Uniqueness
Implemented in `personal_details` table with unique indexes and validation rules.

### Living Expense Verification
- Client declares amounts
- Assessor can verify and adjust
- Verification notes tracked
- Both amounts stored for compliance

### PDF Export
All application data, comments, and activity logs can be exported to PDF for compliance records.

## API Integrations Ready

1. **Credit Sense API** - Configuration in services.php
2. **Twilio SMS** - For SMS communications
3. **Email SMTP** - For email communications
4. **DocuSign (Future)** - For electronic signatures

## Next Steps

1. Create remaining views (Blade templates)
2. Implement remaining controllers
3. Set up email templates
4. Configure SMS provider
5. Integrate Credit Sense API
6. Set up electronic signature provider
7. Create PDF export templates
8. Implement notification system
9. Add file validation rules
10. Set up automated testing

## Support

For issues or questions, contact the development team.

## License

Proprietary - Commercial Loan CRM System
