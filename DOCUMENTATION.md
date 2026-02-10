# Real Estate Management System - Complete Documentation

## System Overview

A comprehensive Real Estate CRM built with Laravel that manages properties, plots, societies, clients, deals, payments, and more with a robust role-based permission system.

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Module Breakdown](#module-breakdown)
3. [Database Schema](#database-schema)
4. [Role & Permission System](#role--permission-system)
5. [Installation Guide](#installation-guide)
6. [API Endpoints](#api-endpoints)
7. [User Roles](#user-roles)
8. [Key Features](#key-features)

---

## System Architecture

### Technology Stack
- **Backend**: Laravel 11.x
- **Database**: MySQL
- **Frontend**: Blade Templates (with future API support)
- **Authentication**: Laravel Breeze/Sanctum
- **Authorization**: Custom Role-Permission System

### Architecture Pattern
- **MVC Pattern**: Model-View-Controller
- **Repository Pattern**: For data access layer
- **Service Layer**: For business logic
- **Policy-based Authorization**: Laravel Policies with custom middleware

---

## Module Breakdown

### 1. **Society Management Module**
Manages housing societies/schemes with hierarchical structure.

**Features:**
- Create, edit, delete societies
- Track society status (active, inactive, under development)
- Manage total area with different units (marla, kanal, sqft, acres)
- Link blocks and plots to societies

**Models:**
- `Society` (parent)
- `Block` (child of Society)
- `Street` (child of Block)

**Files:**
- Migration: `2026_01_28_000005_create_societies_table.php`
- Model: `app/Models/Society.php`
- Controller: `app/Http/Controllers/SocietyController.php`

---

### 2. **Plot Management Module**
Comprehensive plot/land management system.

**Features:**
- Plot listing with detailed specifications
- Size in multiple units (marla, kanal, sqft) with automatic conversion
- Plot dimensions (width, length)
- Location features (corner plot, park facing, main road)
- Plot status tracking (available, booked, sold, reserved)
- Price management (base price, current price)
- Category (residential, commercial)
- Map images and documents

**Models:**
- `Plot`

**Key Fields:**
- Plot number (unique identifier)
- Size with unit conversion
- Pricing information
- Location benefits
- Status tracking

**Files:**
- Migration: `2026_01_28_000008_create_plots_table.php`
- Model: `app/Models/Plot.php`
- Controller: `app/Http/Controllers/PlotController.php`

---

### 3. **Property Management Module**
Manages houses, apartments, and commercial properties.

**Features:**
- Property types: House, Apartment, Commercial
- Property categories: New, Old, Under Construction
- For: Sale or Rent
- Property details: bedrooms, bathrooms, floors
- Amenities tracking (parking, garden, gym, pool)
- Furnished/unfurnished status
- Featured properties
- GPS coordinates support
- Multiple images and documents

**Models:**
- `Property`

**Files:**
- Migration: `2026_01_28_000009_create_properties_table.php`
- Model: `app/Models/Property.php`
- Controller: `app/Http/Controllers/PropertyController.php`

---

### 4. **CRM Module**

#### 4.1 Lead Management
Track and convert potential customers.

**Features:**
- Lead source tracking (website, Facebook, referral, walk-in, call)
- Lead status (new, contacted, qualified, negotiation, converted, lost)
- Priority levels (low, medium, high, urgent)
- Interest type (plot, house, apartment, commercial)
- Budget range and preferred location
- Assignment to dealers/agents
- Lead conversion to client

**Models:**
- `Lead`

**Files:**
- Migration: `2026_01_28_000011_create_leads_table.php`
- Model: `app/Models/Lead.php`
- Controller: `app/Http/Controllers/LeadController.php`

#### 4.2 Client Management
Manage buyer and seller information.

**Features:**
- Client types (buyer, seller, both)
- Complete contact information (phone, email, CNIC)
- Client status (active, inactive, blacklisted)
- Occupation and company details
- Assignment to dealers/agents
- Document storage (CNIC copies, etc.)
- Track all deals, files, and payments

**Models:**
- `Client`

**Files:**
- Migration: `2026_01_28_000010_create_clients_table.php`
- Model: `app/Models/Client.php`
- Controller: `app/Http/Controllers/ClientController.php`

#### 4.3 Follow-up Management
Schedule and track follow-up activities.

**Features:**
- Follow-up types (call, meeting, email, SMS, WhatsApp, site visit)
- Schedule future follow-ups
- Track completed and pending follow-ups
- Overdue follow-up alerts
- Outcome recording
- Polymorphic relation (works with both Leads and Clients)

**Models:**
- `FollowUp`

**Files:**
- Migration: `2026_01_28_000012_create_follow_ups_table.php`
- Model: `app/Models/FollowUp.php`
- Controller: `app/Http/Controllers/FollowUpController.php`

#### 4.4 Deal Management
Record and track property/plot deals.

**Features:**
- Unique deal numbers (auto-generated)
- Deal types (purchase, sale, booking)
- Payment types (cash, installment)
- Commission calculation (percentage-based)
- Installment plan setup
- Deal status (pending, confirmed, cancelled, completed)
- Polymorphic dealable (Plot or Property)
- Link to clients and dealers

**Models:**
- `Deal`

**Files:**
- Migration: `2026_01_28_000013_create_deals_table.php`
- Model: `app/Models/Deal.php`
- Controller: `app/Http/Controllers/DealController.php`

---

### 5. **File System Module**
Property file management with installment tracking.

**Features:**
- Unique file numbers (auto-generated)
- Total amount, paid amount, remaining amount tracking
- Payment plan (cash or installment)
- Installment frequency (monthly, quarterly, yearly)
- Automatic installment generation
- File status (active, completed, transferred, cancelled, defaulted)
- File transfer between clients
- Transfer charges calculation
- Link to deals

**Models:**
- `PropertyFile`
- `Installment`

**Key Workflows:**
1. Create file from deal
2. Auto-generate installments
3. Track payments against installments
4. Update file status based on payment completion
5. Transfer file to new owner

**Files:**
- Migrations:
  - `2026_01_28_000014_create_property_files_table.php`
  - `2026_01_28_000015_create_installments_table.php`
- Models:
  - `app/Models/PropertyFile.php`
  - `app/Models/Installment.php`
- Controller: `app/Http/Controllers/PropertyFileController.php`

---

### 6. **Payment System Module**
Comprehensive payment and receipt management.

**Features:**
- Unique receipt numbers (auto-generated)
- Payment types (installment, down payment, token, full payment, late fee, transfer fee)
- Payment methods (cash, bank transfer, cheque, online, card)
- Payment status (completed, pending, bounced, reversed)
- Link to property files and installments
- Late fee calculation for overdue installments
- Bank and cheque details tracking
- Payment receipt generation (PDF)
- Document attachments

**Models:**
- `Payment`

**Key Workflows:**
1. Record payment
2. Auto-update installment status
3. Update file paid amount
4. Calculate remaining balance
5. Auto-complete file when fully paid
6. Generate receipt

**Files:**
- Migration: `2026_01_28_000016_create_payments_table.php`
- Model: `app/Models/Payment.php`
- Controller: `app/Http/Controllers/PaymentController.php`

---

### 7. **User & Role Management Module**

**Features:**
- User management (create, edit, activate/deactivate)
- Role assignment
- Permission-based access control
- User activity tracking

**Default Roles:**
1. **Administrator** - Full system access
2. **Manager** - Access to most features except system settings
3. **Dealer/Agent** - Sales-focused access (leads, clients, deals)
4. **Accountant** - Financial management (payments, files, reports)
5. **Staff** - Basic viewing permissions

**Models:**
- `User`
- `Role`
- `Permission`

**Files:**
- Migrations:
  - `2026_01_28_000001_create_roles_table.php`
  - `2026_01_28_000002_create_permissions_table.php`
  - `2026_01_28_000003_create_role_permission_table.php`
  - `2026_01_28_000004_add_role_to_users_table.php`
- Models:
  - `app/Models/Role.php`
  - `app/Models/Permission.php`
  - Extended `app/Models/User.php`
- Middleware:
  - `app/Http/Middleware/CheckRole.php`
  - `app/Http/Middleware/CheckPermission.php`

---

## Database Schema

### Entity Relationships

```
Society
  ├── Blocks (1:many)
  │     ├── Streets (1:many)
  │     └── Plots (1:many)
  └── Properties (1:many)

Client
  ├── Deals (1:many)
  ├── PropertyFiles (1:many)
  ├── Payments (1:many)
  └── FollowUps (polymorphic)

Lead
  └── FollowUps (polymorphic)

Deal
  ├── Dealable (Plot or Property) - polymorphic
  └── PropertyFile (1:1)

PropertyFile
  ├── Fileable (Plot or Property) - polymorphic
  ├── Installments (1:many)
  └── Payments (1:many)

User
  ├── Role (many:1)
  ├── AssignedLeads (1:many)
  ├── AssignedClients (1:many)
  ├── Deals as Dealer (1:many)
  └── FollowUps (1:many)

Role
  └── Permissions (many:many)
```

### Key Tables Summary

| Table | Purpose | Key Features |
|-------|---------|--------------|
| societies | Housing societies/schemes | Hierarchical structure |
| blocks | Society subdivisions | Linked to society |
| streets | Street/road information | Linked to blocks |
| plots | Land plots | Size conversion, status tracking |
| properties | Houses/Apartments/Commercial | Multiple types and categories |
| clients | Customer information | Buyer/Seller types |
| leads | Potential customers | Conversion tracking |
| deals | Sale/Purchase transactions | Commission calculation |
| property_files | Property ownership files | Installment tracking |
| installments | Payment schedule | Overdue calculation |
| payments | Payment records | Receipt generation |
| follow_ups | Activity scheduling | Polymorphic relation |
| users | System users | Role-based access |
| roles | User roles | Permission management |
| permissions | Access permissions | Module-based |

---

## Role & Permission System

### Permission Modules

1. **Societies**: view, create, edit, delete
2. **Blocks**: view, create, edit, delete
3. **Plots**: view, create, edit, delete, assign
4. **Properties**: view, create, edit, delete
5. **Clients**: view, create, edit, delete, view_all
6. **Leads**: view, create, edit, delete, convert, view_all
7. **Deals**: view, create, edit, delete, approve, view_all
8. **Files**: view, create, edit, delete, transfer
9. **Payments**: view, create, edit, delete, view_all, approve
10. **Users**: view, create, edit, delete
11. **Roles**: manage
12. **Reports**: view, export, financial
13. **Follow-ups**: view, create, edit, delete
14. **Settings**: manage

### Role Permission Matrix

| Permission | Admin | Manager | Dealer | Accountant | Staff |
|------------|-------|---------|--------|------------|-------|
| Societies (all) | ✅ | ✅ | ❌ | ❌ | View only |
| Plots (all) | ✅ | ✅ | View only | ❌ | View only |
| Properties (all) | ✅ | ✅ | View only | ❌ | View only |
| Clients (all) | ✅ | ✅ | Own only | View all | View only |
| Leads (all) | ✅ | ✅ | Own only | ❌ | View only |
| Deals (all) | ✅ | ✅ | Own only | View all | ❌ |
| Files (all) | ✅ | ✅ | View only | ✅ | ❌ |
| Payments (all) | ✅ | ✅ | ❌ | ✅ | ❌ |
| Reports | ✅ | ✅ | ❌ | ✅ | ❌ |
| Users/Roles | ✅ | View only | ❌ | ❌ | ❌ |
| Settings | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## Installation Guide

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Node.js & NPM (for frontend assets)

### Step-by-Step Installation

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Database**
   Edit `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=realestatecrm
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run Migrations & Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Compile Assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

6. **Start Development Server**
   ```bash
   php artisan serve
   ```

7. **Access the Application**
   - URL: `http://localhost:8000`
   - Admin Login:
     - Email: `admin@realestatecrm.com`
     - Password: `password`
   - Manager Login:
     - Email: `manager@realestatecrm.com`
     - Password: `password`
   - Dealer Login:
     - Email: `dealer@realestatecrm.com`
     - Password: `password`
   - Accountant Login:
     - Email: `accountant@realestatecrm.com`
     - Password: `password`

### Post-Installation

1. **Change Default Passwords**
   ```bash
   php artisan tinker
   >>> $user = User::where('email', 'admin@realestatecrm.com')->first();
   >>> $user->password = Hash::make('new_secure_password');
   >>> $user->save();
   ```

2. **Storage Link**
   ```bash
   php artisan storage:link
   ```

3. **Schedule Cron Jobs** (for automated tasks)
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

---

## API Endpoints

### Society Management
- `GET /societies` - List all societies
- `GET /societies/create` - Show create form
- `POST /societies` - Store new society
- `GET /societies/{id}` - Show society details
- `GET /societies/{id}/edit` - Show edit form
- `PUT /societies/{id}` - Update society
- `DELETE /societies/{id}` - Delete society

### Plot Management
- `GET /plots` - List plots (with filters)
- `POST /plots` - Create plot
- `GET /plots/{id}` - View plot
- `PUT /plots/{id}` - Update plot
- `DELETE /plots/{id}` - Delete plot

### Property Management
- `GET /properties` - List properties
- `POST /properties` - Create property
- `GET /properties/{id}` - View property
- `PUT /properties/{id}` - Update property
- `DELETE /properties/{id}` - Delete property

### Client Management
- `GET /clients` - List clients
- `POST /clients` - Create client
- `GET /clients/{id}` - View client
- `PUT /clients/{id}` - Update client
- `DELETE /clients/{id}` - Delete client

### Lead Management
- `GET /leads` - List leads
- `POST /leads` - Create lead
- `GET /leads/{id}` - View lead
- `PUT /leads/{id}` - Update lead
- `POST /leads/{id}/convert` - Convert lead to client
- `DELETE /leads/{id}` - Delete lead

### Deal Management
- `GET /deals` - List deals
- `POST /deals` - Create deal
- `GET /deals/{id}` - View deal
- `PUT /deals/{id}` - Update deal
- `POST /deals/{id}/approve` - Approve deal
- `DELETE /deals/{id}` - Delete deal

### Property File Management
- `GET /files` - List files
- `POST /files` - Create file
- `GET /files/{id}` - View file
- `PUT /files/{id}` - Update file
- `POST /files/{id}/transfer` - Transfer file to another client

### Payment Management
- `GET /payments` - List payments
- `POST /payments` - Record payment
- `GET /payments/{id}` - View payment
- `GET /payments/{id}/receipt` - Generate receipt
- `PUT /payments/{id}` - Update payment

### Follow-up Management
- `GET /followups` - List follow-ups
- `POST /followups` - Create follow-up
- `GET /followups/{id}` - View follow-up
- `PUT /followups/{id}` - Update follow-up
- `POST /followups/{id}/complete` - Mark as completed
- `DELETE /followups/{id}` - Delete follow-up

---

## Key Features

### 1. **Automatic Number Generation**
- Deal numbers: `DEAL-2026-0001`
- File numbers: `FILE-2026-00001`
- Receipt numbers: `RCT-2026-000001`

### 2. **Unit Conversion**
Automatic conversion between:
- 1 Marla = 272.25 sq ft
- 1 Kanal = 5,445 sq ft = 20 Marlas
- Custom conversion logic in controllers

### 3. **Polymorphic Relationships**
- Deals can be for Plot OR Property
- Files can be for Plot OR Property
- Follow-ups can be for Lead OR Client

### 4. **Installment Auto-Generation**
When creating a file with installment plan:
- Automatically creates all installments
- Calculates due dates based on frequency
- Tracks payment status

### 5. **Overdue Management**
- Auto-calculates overdue days
- Calculates late fees
- Marks installments as overdue

### 6. **Payment Tracking**
- Links payments to installments
- Auto-updates file paid amount
- Auto-completes file when fully paid
- Generates unique receipt numbers

### 7. **Permission-Based Access**
- Middleware protection on routes
- Users see only authorized data
- Dealers see only their assigned leads/clients
- Accountants see all financial data

### 8. **Dashboard Analytics**
- Real-time statistics
- Revenue tracking (daily, monthly, yearly)
- Plot availability status
- Deal pipeline
- Follow-up reminders
- Overdue alerts

### 9. **Soft Deletes**
All major entities use soft deletes for data recovery

### 10. **Activity Tracking**
- `created_by` field tracks who created records
- `received_by` for payments
- `assigned_to` for leads/clients

---

## Workflow Examples

### Complete Sale Workflow

1. **Lead Entry** → Dealer creates lead
2. **Follow-up** → Schedule calls/meetings
3. **Lead Conversion** → Convert qualified lead to client
4. **Deal Creation** → Create deal for plot/property
5. **Deal Approval** → Manager/Admin approves
6. **File Creation** → Generate property file with installments
7. **Payment Recording** → Record installment payments
8. **File Completion** → Auto-complete when fully paid

### File Transfer Workflow

1. Original client has active file
2. Manager initiates transfer
3. New client assigned
4. Transfer charges applied
5. Remaining installments transferred
6. History maintained

---

## Future Enhancements

### Planned Features
1. **API Layer** - RESTful API for mobile app
2. **Document Management** - File upload/download
3. **SMS Integration** - Automated SMS for follow-ups and payment reminders
4. **Email Notifications** - Payment receipts, overdue alerts
5. **Advanced Reporting** - Custom report builder
6. **Map Integration** - Google Maps for plot/property locations
7. **Inventory Management** - Stock tracking
8. **Commission Tracking** - Dealer commission dashboard
9. **Multi-language Support**
10. **WhatsApp Integration** - Direct messaging

---

## Support & Documentation

### Model Relationships Quick Reference

```php
// Society relationships
$society->blocks;
$society->plots;
$society->properties;

// Plot relationships
$plot->society;
$plot->block;
$plot->street;
$plot->deals;
$plot->propertyFiles;

// Client relationships
$client->assignedTo; // Dealer
$client->deals;
$client->propertyFiles;
$client->payments;
$client->followUps;

// Deal relationships
$deal->client;
$deal->dealer;
$deal->dealable; // Plot or Property
$deal->propertyFile;

// PropertyFile relationships
$file->client;
$file->fileable; // Plot or Property
$file->deal;
$file->installments;
$file->payments;

// User relationships
$user->role;
$user->assignedClients;
$user->assignedLeads;
$user->deals;
$user->followUps;
```

### Helper Methods

```php
// User
$user->hasRole('admin');
$user->hasPermission('plots.create');
$user->isAdmin();
$user->isDealer();

// Plot
$plot->isAvailable();

// Lead
$lead->convertToClient($client);

// PropertyFile
$file->updatePaymentStatus();
PropertyFile::generateFileNumber();

// Deal
Deal::generateDealNumber();

// Payment
Payment::generateReceiptNumber();

// Installment
$installment->markAsPaid($method, $reference, $userId);
$installment->calculateOverdue();
```

---

## Testing

### Running Tests
```bash
php artisan test
```

### Sample Test Cases
- User authentication
- Permission checking
- Model relationships
- Deal creation workflow
- Payment recording
- Installment generation
- File transfer

---

## Troubleshooting

### Common Issues

1. **Migration Errors**
   - Ensure MySQL version is 8.0+
   - Check foreign key constraints
   - Run migrations in order

2. **Permission Denied**
   - Verify user role assignment
   - Check permission seeding
   - Clear cache: `php artisan cache:clear`

3. **Storage Issues**
   - Run: `php artisan storage:link`
   - Check folder permissions

4. **Seeder Errors**
   - Run seeders in order: roles → permissions → role_permissions → users

---

## Credits

**Developer**: Real Estate CRM Development Team
**Version**: 1.0.0
**Laravel Version**: 11.x
**License**: Proprietary

---

## Changelog

### Version 1.0.0 (January 2026)
- Initial release
- Complete CRUD for all modules
- Role-based permission system
- Dashboard with analytics
- Payment and installment tracking
- File management system
- CRM with lead conversion
- Follow-up management

---

For additional support or custom development, please contact the development team.
