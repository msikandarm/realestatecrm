# Real Estate Management System (CRM)

A comprehensive Real Estate CRM built with Laravel for managing properties, plots, societies, clients, deals, payments, and more.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js & NPM

### Installation

1. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure database** (edit `.env`)
   ```env
   DB_DATABASE=realestatecrm
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run migrations & seed data**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start development server**
   ```bash
   php artisan serve
   npm run dev
   ```

6. **Access application**
   - URL: http://localhost:8000
   - Admin: `admin@realestatecrm.com` / `password`

## 📋 System Modules

### 1. Society Management
- Create and manage housing societies
- Hierarchical structure: Societies → Blocks → Streets
- Track area, status, and location details

### 2. Plot Management
- Comprehensive plot tracking
- Multiple unit support (marla, kanal, sq ft)
- Plot status (available, booked, sold)
- Location features (corner, park facing, main road)
- Pricing and dimensions

### 3. Property Management
- Houses, Apartments, Commercial properties
- New/Old/Under-construction categories
- Sale/Rent options
- Amenities tracking
- Multiple images support

### 4. CRM System
- **Leads**: Track potential customers with conversion
- **Clients**: Manage buyers and sellers
- **Follow-ups**: Schedule and track activities
- **Deals**: Record transactions with commission

### 5. File System
- Property ownership files
- Installment plan generation
- Payment tracking
- File transfer between clients
- Automatic status updates

### 6. Payment System
- Record all payment types
- Link to installments
- Receipt generation
- Late fee calculation
- Multiple payment methods

### 7. User & Role Management
- 5 default roles: Admin, Manager, Dealer, Accountant, Staff
- 60+ granular permissions
- Role-based access control

## 🔐 Default Users

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@realestatecrm.com | password |
| Manager | manager@realestatecrm.com | password |
| Dealer | dealer@realestatecrm.com | password |
| Accountant | accountant@realestatecrm.com | password |

**⚠️ Change passwords after first login!**

## 📊 Key Features

✅ Complete CRUD for all modules
✅ Role-based permission system
✅ Dashboard with real-time analytics
✅ Automatic number generation (deals, files, receipts)
✅ Unit conversion (marla/kanal/sqft)
✅ Polymorphic relationships
✅ Installment auto-generation
✅ Overdue tracking with late fees
✅ File transfer capability
✅ Soft deletes for data recovery
✅ Activity tracking

## 📁 Project Structure

```
realestatecrm/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # All module controllers
│   │   └── Middleware/      # Role & Permission middleware
│   └── Models/              # Eloquent models
├── database/
│   ├── migrations/          # Database schema (16 migrations)
│   └── seeders/             # Initial data (roles, permissions, users)
├── routes/
│   └── web.php             # All application routes
└── DOCUMENTATION.md        # Complete system documentation
```

## 🗃️ Database Schema

**16 Tables:**
- societies, blocks, streets
- plots, properties
- clients, leads, follow_ups
- deals, property_files, installments, payments
- users, roles, permissions, role_permission

**Relationships:**
- Hierarchical: Society → Block → Street
- Polymorphic: Deals/Files can link to Plot OR Property
- Follow-ups can link to Lead OR Client

## 📖 Documentation

For detailed documentation including:
- Complete architecture breakdown
- API endpoints
- Permission matrix
- Workflow examples
- Model relationships
- Helper methods

See: **[DOCUMENTATION.md](DOCUMENTATION.md)**

## 🛠️ Development

### Run migrations
```bash
php artisan migrate
```

### Rollback migrations
```bash
php artisan migrate:rollback
```

### Refresh database
```bash
php artisan migrate:fresh --seed
```

### Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 🔧 Tech Stack

- **Backend**: Laravel 11.x
- **Database**: MySQL
- **Frontend**: Blade Templates
- **Authentication**: Laravel Breeze
- **Assets**: Vite

## 📈 Future Enhancements

- RESTful API for mobile app
- Document management system
- SMS/Email integration
- Advanced reporting
- Map integration (Google Maps)
- WhatsApp integration
- Multi-language support
- Commission tracking dashboard

## 🤝 Contributing

This is a proprietary system. For custom development or support, contact the development team.

## 📄 License

Proprietary - All rights reserved

## 📞 Support

For issues or questions, refer to the complete documentation in `DOCUMENTATION.md`.

---

**Version**: 1.0.0
**Last Updated**: January 2026
