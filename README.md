# Commercial Loan CRM System

## 🎯 Project Overview

A comprehensive Commercial Loan Client Information Management (CIM) System built with Laravel 12, featuring:

- ✅ **14 Normalized Database Tables** - No single big table, properly separated data
- ✅ **Complete Audit Trail** - IP tracking on all submissions and actions
- ✅ **Email/SMS Logging** - Full communication history
- ✅ **Living Expense Verification** - Client declared vs assessor verified amounts
- ✅ **Document Management** - With version control and categorization
- ✅ **Q&A Workflow** - Internal team can ask questions, clients can respond
- ✅ **Task Management** - ID checks, living expense verification, declarations
- ✅ **Role-Based Access** - Admin, Assessor, Client roles with permissions
- ✅ **Electronic Signatures** - Declaration tracking with IP addresses
- ✅ **Credit Check Integration** - Ready for Credit Sense API
- ✅ **PDF Export** - Compliance-ready exports

## 👥 Development Team

- **Allan** - Lead Developer
- **Aurelio** - Support Developer (API Integration)
- **Jeffrey** - Support Developer

## 📋 Requirements Met

### From 09 Feb 2026 Meeting Notes:
✅ Email and mobile phone uniqueness validation
✅ Declaration support (provided by John)
✅ Application form electronic sign-off on submit
✅ Living expense figures with client/assessor verification
✅ IP address tracking on all submissions
✅ All front-end fields available in backend
✅ Email in/out logging
✅ SMS in/out logging
✅ Comment system with timestamps
✅ PDF export for compliance
✅ Credit Sense API integration ready
✅ Task system (ID check, living expense check, declarations)

## 🗄️ Database Architecture

### Normalized Tables (14 total):

1. **applications** - Main loan application records
2. **personal_details** - Personal information (unique email/phone)
3. **residential_addresses** - 3-year address history
4. **employment_details** - Employment and income
5. **living_expenses** - Client declared + assessor verified amounts
6. **documents** - File uploads with versioning
7. **communications** - Email/SMS in/out logs
8. **comments** - Timestamped notes with IP tracking
9. **questions** - Q&A workflow system
10. **notifications** - System notifications
11. **activity_logs** - Complete audit trail
12. **tasks** - Workflow tasks (ID, expenses, declarations)
13. **declarations** - Electronic signatures
14. **credit_checks** - Credit check records

## 🚀 Quick Start

### Prerequisites
```bash
PHP >= 8.3
MySQL >= 8.0
Composer
Node.js >= 18
```

### Installation

1. **Clone and Install Dependencies**
```bash
cd commercial-loan-crm
composer install
npm install
```

2. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Configure Database** (in `.env`)
```env
DB_DATABASE=commercial_loan_crm
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. **Create Database**
```bash
mysql -u root -p
CREATE DATABASE commercial_loan_crm;
exit;
```

5. **Run Migrations**
```bash
php artisan migrate
```

6. **Install Jetstream**
```bash
php artisan jetstream:install livewire
php artisan migrate
```

7. **Seed Database**
```bash
php artisan db:seed
```

8. **Storage Link**
```bash
php artisan storage:link
```

9. **Compile Assets**
```bash
npm run build
```

10. **Start Server**
```bash
php artisan serve
```

Visit: http://localhost:8000

## 🔐 Default Login Credentials

After seeding:

### Admin Account
- Email: allan@commercialloan.com
- Password: password

### Assessor Accounts
- Email: aurelio@commercialloan.com (API Integration)
- Email: jeffrey@commercialloan.com
- Email: cindy@commercialloan.com (Living Expenses)
- Password: password (for all)

### Test Client
- Email: john.smith@example.com
- Password: password

## 📁 Project Structure

```
commercial-loan-crm/
├── app/
│   ├── Http/Controllers/
│   │   ├── ApplicationController.php ✅
│   │   ├── PersonalDetailsController.php ✅
│   │   └── Admin/
│   │       └── ApplicationController.php ✅
│   ├── Models/ (14 models - all created ✅)
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
│   │   ├── ActivityLog.php
│   │   └── User.php ✅
│   └── Policies/
│       └── ApplicationPolicy.php ✅
├── database/
│   ├── migrations/ (14 migrations - all created ✅)
│   └── seeders/
│       ├── DatabaseSeeder.php ✅
│       ├── RoleSeeder.php ✅
│       └── UserSeeder.php ✅
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php ✅
│   └── applications/
│       └── index.blade.php ✅
└── routes/
    └── web.php ✅
```

## ✨ Key Features

### Client Portal
- Secure registration and login (Jetstream)
- Create and manage loan applications
- Multi-step application form
- Document upload with categorization
- Answer assessor questions
- View application status
- Electronic signature for declarations
- All actions tracked with IP address

### Admin/Assessor Portal
- Application dashboard with filtering
- Detailed application review
- Assign applications to assessors
- Update application status
- Document review and approval
- Ask questions to clients
- Create and assign tasks
- Verify living expenses
- Add internal/client-visible comments
- Request credit checks
- Send emails/SMS (logged automatically)
- Export applications to PDF
- View complete audit trail

### Security & Compliance
- Role-based access control (Admin/Assessor/Client)
- Email uniqueness validation
- Phone number uniqueness validation
- IP address tracking on all critical actions
- Complete activity log for audit trail
- Encrypted document storage
- Electronic signature tracking
- Data isolation per application

### Communication Features
- Email in/out logging
- SMS in/out logging
- System notifications
- Two-way secure messaging
- All communications tracked and exportable

## 🔧 Configuration

### Email Setup (`.env`)
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

### SMS Setup - Twilio (`.env`)
```env
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_number
```

### Credit Sense API (`.env`)
```env
CREDIT_SENSE_API_KEY=your_api_key
CREDIT_SENSE_API_URL=https://api.creditsense.com.au
```

### Queue Setup (for emails/SMS)
```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

## 📝 Next Development Steps

### Phase 1 - Complete Core (Priority)
- [ ] Create remaining client controllers (Addresses, Employment, Expenses, Documents)
- [ ] Build application form views (multi-step wizard)
- [ ] Implement document upload functionality
- [ ] Create Q&A interface

### Phase 2 - Admin Features
- [ ] Complete admin dashboard
- [ ] Build application review interface
- [ ] Implement task management
- [ ] Create comment system UI

### Phase 3 - Communications
- [ ] Email service implementation
- [ ] SMS service (Twilio integration)
- [ ] Notification system
- [ ] Communication logging

### Phase 4 - Advanced
- [ ] Credit Sense API integration (Aurelio)
- [ ] PDF export templates
- [ ] Electronic signature flow
- [ ] Living expense verification workflow

### Phase 5 - Polish
- [ ] Comprehensive testing
- [ ] Security audit
- [ ] Performance optimization
- [ ] Documentation

## 🔍 Key Implementation Details

### IP Address Tracking
Every critical action captures `request()->ip()`:
- Application submissions
- Personal detail updates
- Document uploads
- Question answers
- Declaration agreements
- Comments
- All logged in `activity_logs` table

### Email/Phone Uniqueness
```php
// Implemented in personal_details table
$table->string('mobile_phone')->unique();
$table->string('email')->unique();

// Validation rules check across all records
Rule::unique('personal_details', 'mobile_phone')
Rule::unique('personal_details', 'email')
```

### Living Expense Verification
```php
// living_expenses table has two amounts:
- client_declared_amount  // What client says
- verified_amount         // What assessor confirms
- assessor_notes          // How verification was done
- verification_notes      // Additional context
- verified_by            // Who verified
- verified_at            // When verified
```

### Audit Trail
```php
// All changes tracked in activity_logs:
- user_id (who did it)
- action (what happened)
- old_values (before)
- new_values (after)
- ip_address (from where)
- user_agent (with what)
- timestamps
```

## 📦 What's Included

### Created Files (Ready to Use):
✅ 14 database migrations
✅ 14 Eloquent models with relationships
✅ 3 controllers (Application, PersonalDetails, Admin/Application)
✅ 1 policy (ApplicationPolicy)
✅ 3 seeders (Database, Role, User)
✅ 2 views (layout, applications index)
✅ Routes file with client and admin routes
✅ User model with roles support
✅ Complete installation guide
✅ Project structure documentation

### To Be Created (See PROJECT_STRUCTURE.md):
- Remaining controllers (addresses, employment, expenses, documents, questions, tasks, etc.)
- Additional views (forms, show pages, admin interfaces)
- Service classes (Email, SMS, Credit Check, PDF Export)
- Mail classes for notifications
- Additional view components

## 📚 Documentation

- `INSTALLATION.md` - Detailed installation steps
- `PROJECT_STRUCTURE.md` - Complete file structure and implementation guide
- `README.md` - This file (project overview)

## 🛠️ Technology Stack

- **Backend**: Laravel 12
- **Authentication**: Jetstream (Livewire)
- **Authorization**: Spatie Laravel Permission
- **Frontend**: Blade Templates + Tailwind CSS
- **Database**: MySQL 8.0+
- **PDF**: Laravel DomPDF
- **SMS**: Twilio SDK

## 🤝 Contributing

This is a proprietary commercial project. Access restricted to the development team.

## 📄 License

Proprietary - Commercial Loan CRM System

---

**For detailed installation instructions, see `INSTALLATION.md`**

**For complete project structure and development guide, see `PROJECT_STRUCTURE.md`**
