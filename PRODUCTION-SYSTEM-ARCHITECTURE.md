# 🏗️ REAL ESTATE CRM - PRODUCTION SYSTEM ARCHITECTURE

**Version:** 2.0 (Production-Ready)
**Last Updated:** January 29, 2026
**Framework:** Laravel 11+ with Blade Templates
**Database:** MySQL 8.0+
**Frontend:** Vanilla JS, Chart.js, TailwindCSS

---

## 📊 SYSTEM OVERVIEW

### Core Business Model

```
┌─────────────────────────────────────────────────────────────────────┐
│                    REAL ESTATE MANAGEMENT SYSTEM                    │
│                      (Complete Business Workflow)                    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────┐    ┌──────────────┐    ┌───────────────┐        │
│  │   INQUIRY   │───→│ QUALIFICATION│───→│  NEGOTIATION  │        │
│  │   (Lead)    │    │   (Follow-Up)│    │   (Proposal)  │        │
│  └─────────────┘    └──────────────┘    └───────┬───────┘        │
│        │                                          │                 │
│        ├──→ Multi-channel Tracking               │                 │
│        ├──→ Source Attribution                   │                 │
│        └──→ Auto-Assignment                      ↓                 │
│                                         ┌────────────────┐          │
│  ┌─────────────────┐                   │  CONVERSION   │          │
│  │   INVENTORY     │←──────────────────│  (Client)     │          │
│  │  Management     │                   └────────────────┘          │
│  ├─────────────────┤                           │                   │
│  │ • Societies     │                           ↓                   │
│  │ • Blocks        │                   ┌────────────────┐          │
│  │ • Streets       │←──────────────────│     DEAL       │          │
│  │ • Plots         │                   │  (Transaction) │          │
│  │ • Properties    │                   └────────┬───────┘          │
│  └─────────────────┘                            │                   │
│                                                  │                   │
│                                                  ↓                   │
│  ┌─────────────────────────────────────────────────────┐           │
│  │              FINANCIAL MANAGEMENT                   │           │
│  ├─────────────────────────────────────────────────────┤           │
│  │ • Property Files (Contracts)                        │           │
│  │ • Installment Plans (Auto-Generated)                │           │
│  │ • Payment Tracking (Multi-Method)                   │           │
│  │ • Commission Calculation (Dealer Earnings)          │           │
│  │ • Late Payment Detection (Auto Late Fees)           │           │
│  │ • Receipt Generation (PDF)                          │           │
│  │ • File Transfers (Ownership Change)                 │           │
│  └─────────────────────────────────────────────────────┘           │
│                                                                     │
│  ┌─────────────────────────────────────────────────────┐           │
│  │           ANALYTICS & REPORTING LAYER               │           │
│  ├─────────────────────────────────────────────────────┤           │
│  │ • Real-time Dashboard                               │           │
│  │ • Sales Analytics                                   │           │
│  │ • Commission Reports                                │           │
│  │ • Payment Collection Tracking                       │           │
│  │ • Overdue Management                                │           │
│  │ • Performance Metrics                               │           │
│  └─────────────────────────────────────────────────────┘           │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🎯 MODULE BREAKDOWN

### 1️⃣ AUTHENTICATION & AUTHORIZATION MODULE

**Status:** ✅ **PRODUCTION READY**

#### Database Tables
- `users` - System users with enhanced profile
- `roles` - 6 pre-defined roles (Spatie Package)
- `permissions` - 100+ granular permissions
- `model_has_roles` - User-Role assignments
- `role_has_permissions` - Role-Permission assignments
- `model_has_permissions` - Direct user permissions

#### Roles Hierarchy
```
Super Admin (God Mode)
  ├─ Admin (Full System Access)
  │   ├─ Manager (Operations Management)
  │   │   ├─ Dealer (Sales & Client Management)
  │   │   ├─ Accountant (Financial Operations)
  │   │   └─ Staff (Basic Operations)
```

#### Permission Matrix

| Module | Super Admin | Admin | Manager | Dealer | Accountant | Staff |
|--------|:-----------:|:-----:|:-------:|:------:|:----------:|:-----:|
| **Users** | ✓ Full | ✓ Create/Edit | 🔍 View | ❌ | ❌ | ❌ |
| **Societies** | ✓ Full | ✓ Full | ✓ Full | 🔍 View | 🔍 View | 🔍 View |
| **Blocks/Streets** | ✓ Full | ✓ Full | ✓ Full | 🔍 View | 🔍 View | 🔍 View |
| **Plots** | ✓ Full | ✓ Full | ✓ Edit | 🔍 View | 🔍 View | 🔍 View |
| **Properties** | ✓ Full | ✓ Full | ✓ Edit | 🔍 View | 🔍 View | 🔍 View |
| **Leads** | ✓ Full | ✓ All | ✓ All | 👤 Own | ❌ | ✍️ Create |
| **Clients** | ✓ Full | ✓ All | ✓ All | 👤 Own | 🔍 View | 🔍 View |
| **Deals** | ✓ Full | ✓ Approve | ✓ Approve | 👤 Own | 🔍 View | ❌ |
| **Property Files** | ✓ Full | ✓ Full | ✓ Full | 👤 Own | 🔍 View | ❌ |
| **Payments** | ✓ Full | ✓ Full | ✓ Receive | ✓ Receive | ✓ Full | ❌ |
| **Expenses** | ✓ Full | ✓ Full | ✓ Approve | ❌ | ✓ Full | ❌ |
| **Reports** | ✓ All | ✓ All | ✓ All | 📊 Own | 💰 Financial | ❌ |

#### Key Features
- ✅ Spatie Laravel Permission v6.x
- ✅ Middleware-based protection
- ✅ Blade directive authorization
- ✅ Route-level permission guards
- ✅ Scope-based data access (own vs all)
- ✅ Audit trail (created_by, updated_by)

#### Implementation
- **Models:** `User`, `Role`, `Permission`
- **Middleware:** `permission`, `role`
- **Seeders:** `RoleSeeder`, `PermissionSeeder`, `UserSeeder`
- **Routes:** All routes protected by permission middleware

---

### 2️⃣ PROPERTY STRUCTURE MODULE

**Status:** ✅ **PRODUCTION READY**

#### 4-Tier Hierarchical Structure

```
┌───────────────────────────────────────────────────────┐
│ SOCIETY (Level 1)                                     │
│ ┌─────────────────────────────────────────────────┐   │
│ │ DHA Phase 1, Bahria Town, Gulberg, etc.        │   │
│ │ • City, Province, Total Area                   │   │
│ │ • Launch Date, Completion Date                 │   │
│ │ • Developer Info, Amenities (JSON)             │   │
│ │ • Map File, Status                             │   │
│ └───────────────┬─────────────────────────────────┘   │
│                 │                                      │
│     ┌───────────▼────────┐                            │
│     │ BLOCK (Level 2)    │                            │
│     │ ┌────────────────┐ │                            │
│     │ │ Block A, B, C  │ │                            │
│     │ │ • Auto-counts  │ │                            │
│     │ │ • Map File     │ │                            │
│     │ └────────┬───────┘ │                            │
│     └──────────┼─────────┘                            │
│                │                                       │
│     ┌──────────▼─────────┐                            │
│     │ STREET (Level 3)   │                            │
│     │ ┌────────────────┐ │                            │
│     │ │ Street 1,2,3   │ │                            │
│     │ │ • Width/Length │ │                            │
│     │ │ • Type: Main,  │ │                            │
│     │ │   Commercial,  │ │                            │
│     │ │   Residential  │ │                            │
│     │ └────────┬───────┘ │                            │
│     └──────────┼─────────┘                            │
│                │                                       │
│     ┌──────────▼─────────────────┐                    │
│     │ PLOT (Level 4)             │                    │
│     │ ┌────────────────────────┐ │                    │
│     │ │ Plot 123, 124, 125     │ │                    │
│     │ │ • plot_code: AUTO      │ │                    │
│     │ │   (DHA-A-ST1-123)      │ │                    │
│     │ │ • Size: Marla/Kanal    │ │                    │
│     │ │ • Price Calculation    │ │                    │
│     │ │ • Status: Available,   │ │                    │
│     │ │   Booked, Sold         │ │                    │
│     │ │ • Premium: Corner,     │ │                    │
│     │ │   Park-Facing          │ │                    │
│     │ └────────────────────────┘ │                    │
│     └────────────────────────────┘                    │
└───────────────────────────────────────────────────────┘
```

#### Database Tables

**1. societies**
```sql
Key Fields:
- id, name, code (auto-generated), slug
- city, province, address
- total_area, area_unit (marla/kanal/acre)
- status (planning/under_development/developed/completed)
- launch_date, completion_date
- developer_name, developer_contact
- amenities (JSON: parks, schools, mosques, security)
- map_file (path)
- created_by, updated_by
- timestamps, soft_deletes

Indexes:
- slug (unique), status, is_active
- city (for location-based search)
- FULLTEXT(name, location, city)

Relationships:
- hasMany(Block)
- through: hasMany(Street), hasMany(Plot)
```

**2. blocks**
```sql
Key Fields:
- id, society_id (FK CASCADE)
- name, code (auto from name)
- total_area, area_unit
- status, description
- total_plots (auto-calculated)
- available_plots (auto-calculated)
- sold_plots (auto-calculated)
- map_file

Indexes:
- society_id, is_active
- UNIQUE(society_id, code)

Auto-Updates:
- Updates counts when street/plot changes
- Touches parent society on updates

Relationships:
- belongsTo(Society)
- hasMany(Street)
- through: hasMany(Plot)
```

**3. streets**
```sql
Key Fields:
- id, block_id (FK CASCADE)
- name, code (auto)
- width, length (in feet)
- type (main/commercial/residential)
- status
- total_plots, available_plots, sold_plots (auto)

Indexes:
- block_id
- UNIQUE(block_id, code)

Auto-Updates:
- Updates counts from plots
- Updates parent block counts

Relationships:
- belongsTo(Block)
- hasMany(Plot)
- through: belongsTo(Society)
```

**4. plots**
```sql
Key Fields:
- id, street_id (FK CASCADE)
- plot_number, plot_code (UNIQUE, auto: SOCIETY-BLOCK-STREET-PLOT)
- area, area_unit (marla/kanal/acre/sq_ft)
- length, width (in feet)
- type (residential/commercial/industrial/agricultural)
- status (available/booked/sold/on-hold)
- price_per_marla, total_price (auto-calculated)
- corner, facing, park_facing, main_road_facing
- description, features, map_location

Indexes:
- street_id, status, type
- plot_code (unique)
- COMPOSITE(street_id, plot_number)

Auto-Generation:
- plot_code: "DHA-A-ST1-123"
- total_price: area × price_per_marla (with unit conversion)

Auto-Updates:
- Updates parent street counts on status change
- Cascades to block and society

Relationships:
- belongsTo(Street)
- morphMany(Deal) - Polymorphic
- morphOne(PropertyFile) - Polymorphic
- hasOne(Property) - If property built on plot
```

#### Key Features

✅ **Auto Code Generation**
```php
// Society code from name
"DHA Phase 1" → "DHAP1"
"Bahria Town Sector C" → "BTSC"

// Block code
Society: DHA, Block: A → "DHA-A"

// Street code
Block: DHA-A, Street: 1 → "DHA-A-ST1"

// Plot code
Street: DHA-A-ST1, Plot: 123 → "DHA-A-ST1-123"
```

✅ **Auto Count Management**
- Plot status change → Update street counts
- Street update → Recalculate block counts
- Block update → Recalculate society counts
- Cascade updates through hierarchy

✅ **Unit Conversion System**
```php
// Automatic conversion to sq_ft for standardization
1 Marla = 272.25 sq ft
1 Kanal = 5,445 sq ft (20 Marla)
1 Acre = 43,560 sq ft

// Price calculation
total_price = area (in marla) × price_per_marla
```

✅ **Premium Plot Detection**
- Corner plots (higher value)
- Park-facing (desirable)
- Main road facing (commercial advantage)
- Direction facing (north/south/east/west)

#### Controllers & Routes

**SocietyController**
- `index()` - List with search/filter/sort
- `create()` - Form with validation
- `store()` - Save with auto-code generation
- `show()` - Details with blocks, stats
- `edit()` - Update form
- `update()` - Save changes
- `destroy()` - Soft delete (cascade to blocks/streets/plots)

**BlockController**
- Full CRUD + `getBySociety()` API for cascading dropdowns
- Auto-calculation of plot counts
- Touch parent society on changes

**StreetController**
- Full CRUD + `getByBlock()` API
- Auto-calculation and cascade to block

**PlotController**
- Full CRUD with advanced filtering
- Status management workflow
- Premium plot marking

**Routes:** 70+ routes with permission middleware

---

### 3️⃣ PROPERTY MANAGEMENT MODULE

**Status:** ✅ **PRODUCTION READY**

#### Overview
Manages constructed properties (Houses, Apartments, Commercial Units) as separate inventory from plots.

#### Database Tables

**1. properties**
```sql
Key Fields:
- id, title, reference_code (UNIQUE, auto)
- type (house/apartment/commercial)
- condition (new/old/under_construction)
- property_for (sale/rent/both)

Location Hierarchy:
- plot_id (FK, nullable) - If built on a plot
- society_id, block_id, street_id (FK)
- address, area, city, province
- latitude, longitude (GPS)

Property Details:
- size, size_unit (sq_ft/sq_m/marla/kanal)
- size_in_sqft (auto-calculated)
- bedrooms, bathrooms, floors
- year_built, furnished, parking, parking_spaces
- amenities (JSON: electricity, gas, water, internet)
- features (JSON: garden, pool, gym, security)

Pricing:
- price (sale price)
- rental_price, rental_period (monthly/yearly)
- price_per_unit (auto-calculated)
- negotiable (boolean)

Ownership:
- owner_id (FK clients) - Current owner
- owner_name, owner_contact (external owners)

Status:
- status (available/sold/rented/under_negotiation/reserved/off_market)
- featured, is_verified, views_count

Media:
- featured_image, images (JSON), documents (JSON)
- video_url, virtual_tour_url

Audit:
- created_by, updated_by
- timestamps, soft_deletes

Indexes:
- COMPOSITE(type, status, property_for)
- COMPOSITE(society_id, block_id)
- city, area
- price, condition
- FULLTEXT(title, address, description)

Relationships:
- belongsTo(Plot) - Optional
- belongsTo(Society, Block, Street)
- belongsTo(Client as owner)
- hasMany(PropertyImage)
- morphMany(Deal) - Polymorphic
- morphMany(PropertyFile) - Polymorphic
```

**2. property_images**
```sql
Key Fields:
- id, property_id (FK CASCADE)
- image_path, caption
- order (for sorting), is_featured

Relationships:
- belongsTo(Property)
```

#### Query Scopes
```php
// Status
Property::available()
Property::sold()
Property::rented()
Property::featured()

// Type
Property::houses()
Property::apartments()
Property::commercial()

// Purpose
Property::forSale()
Property::forRent()

// Features
Property::furnished()
Property::withParking()

// Location
Property::byCity('Lahore')
Property::byOwner($clientId)
Property::byPriceRange(5000000, 10000000)
```

#### Key Features

✅ **Auto-Reference Code Generation**
```php
// Format: PROP-YYYY-NNNN
"PROP-2026-0001"
```

✅ **Size Unit Conversion**
```php
// Auto-convert all sizes to sq_ft for consistency
size_in_sqft = size * unit_multiplier
```

✅ **Media Management**
- Multiple image upload
- Featured image selection
- Image ordering
- Video/virtual tour links
- Document attachments

✅ **Amenities & Features (JSON)**
```json
{
  "amenities": ["electricity", "gas", "water", "internet", "sewerage"],
  "features": ["garden", "swimming_pool", "gym", "security_system", "backup_generator"]
}
```

✅ **Ownership Tracking**
- Links to client (buyer)
- Transfer functionality through deals
- Owner history tracking

#### Controller
**PropertyController** - Full CRUD with:
- Multi-filter search (type, condition, status, city, society)
- Image upload/management
- Price calculation
- Featured property management
- View counter

---

### 4️⃣ CRM MODULE (Lead → Client → Deal)

**Status:** ✅ **PRODUCTION READY**

#### Business Flow

```
┌──────────────────────────────────────────────────────────────┐
│                    CRM FUNNEL WORKFLOW                       │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  STAGE 1: LEAD CAPTURE                                       │
│  ┌──────────────────────────────────────────────┐            │
│  │ Multi-Channel Inquiry                        │            │
│  │ • Website Form                               │            │
│  │ • Facebook Ad                                │            │
│  │ • Referral                                   │            │
│  │ • Walk-in                                    │            │
│  │ • Phone Call                                 │            │
│  │ • WhatsApp                                   │            │
│  │ • Email                                      │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STAGE 2: QUALIFICATION                                      │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ Lead Management                              │            │
│  │ • Status: New → Contacted → Qualified →      │            │
│  │   Negotiation → Converted/Lost               │            │
│  │ • Priority: Low/Medium/High/Urgent           │            │
│  │ • Interest: Plot/House/Apartment/Commercial  │            │
│  │ • Budget Range                               │            │
│  │ • Preferred Location                         │            │
│  │ • Auto-Assignment to Dealer                  │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STAGE 3: FOLLOW-UP (Polymorphic)                           │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ Follow-Up System                             │            │
│  │ • Links to Lead OR Client                    │            │
│  │ • Type: Call/Meeting/Email/Site Visit        │            │
│  │ • Scheduled Date/Time                        │            │
│  │ • Status: Scheduled/Completed/Cancelled      │            │
│  │ • Outcome Notes                              │            │
│  │ • Next Follow-Up Date                        │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STAGE 4: CONVERSION                                         │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ Lead → Client Conversion                     │            │
│  │ • Capture CNIC, Full Address                 │            │
│  │ • Mark Lead as Converted                     │            │
│  │ • Track Conversion Date & Source             │            │
│  │ • Maintain Lead-Client Link                  │            │
│  │ • Copy Dealer Assignment                     │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STAGE 5: TRANSACTION                                        │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ Deal Creation (Polymorphic)                  │            │
│  │ • Link Client + Dealer                       │            │
│  │ • Select Plot OR Property                    │            │
│  │ • Deal Type: Purchase/Sale/Booking           │            │
│  │ • Deal Amount                                │            │
│  │ • Payment Type: Cash/Installment             │            │
│  │ • Commission Calculation (Auto)              │            │
│  │ • Status: Pending → Confirmed → Completed    │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STAGE 6: COMMISSION TRACKING                                │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ Dealer Commission                            │            │
│  │ • Type: Primary/Referral/Split               │            │
│  │ • Percentage from Dealer Profile             │            │
│  │ • Amount Auto-Calculated                     │            │
│  │ • Status: Pending → Confirmed → Paid         │            │
│  │ • Payment Record with Receipt                │            │
│  └──────────────────────────────────────────────┘            │
└──────────────────────────────────────────────────────────────┘
```

#### Database Tables

**1. leads**
```sql
Core Fields:
- id, name, email, phone, phone_secondary
- source (website/facebook/referral/walk-in/call/whatsapp/email/other)
- referred_by (if source = referral)

Interest:
- interest_type (plot/house/apartment/commercial)
- society_id, property_id, plot_id (FK, interested in)
- budget_range, preferred_location

Status & Priority:
- status (new/contacted/qualified/negotiation/converted/lost)
- priority (low/medium/high/urgent)

Assignment:
- assigned_to (FK dealers/users)

Conversion Tracking:
- converted_to_client_id (FK clients)
- converted_at

Audit:
- remarks, created_by
- timestamps, soft_deletes

Indexes:
- status, priority, assigned_to
- created_at (for date range)
- COMPOSITE(status, assigned_to)

Relationships:
- belongsTo(User as assignedTo)
- belongsTo(Client as convertedToClient)
- belongsTo(Society, Property, Plot) - Interest
- morphMany(FollowUp)
- belongsTo(User as creator)
```

**2. clients**
```sql
Core Fields:
- id, name, email, phone, alternate_phone
- cnic (unique), address
- city, province, postal_code

Classification:
- client_type (buyer/seller/investor/tenant/landlord)
- is_active

Assignment:
- assigned_to (FK dealers) - Account manager

Conversion Tracking:
- converted_from_lead_id (FK leads)
- converted_at
- lead_source (original source attribution)

Financial:
- total_purchases, total_sales (auto-calculated)

Audit:
- remarks, notes
- created_by, updated_by
- timestamps, soft_deletes

Indexes:
- cnic (unique), phone, email
- assigned_to
- client_type, is_active
- FULLTEXT(name, cnic, phone)

Relationships:
- belongsTo(Lead as originalLead)
- belongsTo(User as dealer)
- hasMany(Deal)
- hasMany(PropertyFile)
- morphMany(FollowUp)
- morphMany(AccountPayment)
```

**3. dealers**
```sql
Core Fields:
- id, user_id (FK users, ONE-TO-ONE)
- cnic, license_number (unique)
- default_commission_rate (decimal 5,2)
- specialization (plots/residential/commercial/all)
- status (active/inactive/suspended)

Performance:
- total_deals (auto-calculated)
- total_commission (auto-calculated)

Banking:
- bank_name, account_title, account_number, iban

Dates:
- joined_date, leaving_date

Audit:
- remarks
- timestamps, soft_deletes

Indexes:
- user_id (unique), status, specialization
- cnic, license_number

Relationships:
- belongsTo(User) - ONE-TO-ONE
- hasMany(Lead as assignedLeads)
- hasMany(Client as assignedClients)
- hasMany(Deal)
```

**4. deals**
```sql
Core Fields:
- id, deal_number (UNIQUE, auto: DEAL-YYYY-NNNN)
- client_id (FK clients)
- dealer_id (FK users/dealers)

Polymorphic (Plot OR Property):
- dealable_type (App\Models\Plot or App\Models\Property)
- dealable_id

Deal Details:
- deal_type (purchase/sale/booking)
- deal_amount (total price)
- deal_date

Payment:
- payment_type (cash/installment)
- installment_months
- down_payment, monthly_installment (auto-calculated)

Commission:
- commission_amount (auto from dealer rate)
- commission_percentage

Status & Workflow:
- status (pending/confirmed/cancelled/completed)
- completion_date

Documents:
- terms_conditions, remarks
- documents (JSON)

Audit:
- created_by, approved_by, approved_at
- timestamps, soft_deletes

Indexes:
- deal_number (unique)
- COMPOSITE(dealable_type, dealable_id)
- COMPOSITE(status, deal_date)
- client_id, dealer_id

Relationships:
- belongsTo(Client)
- belongsTo(User as dealer)
- morphTo(dealable) - Plot OR Property
- hasOne(PropertyFile) - If installment payment
- hasMany(DealCommission)
- belongsTo(User as creator)
```

**5. follow_ups**
```sql
Core Fields:
- id
- followupable_type (App\Models\Lead or App\Models\Client)
- followupable_id

Follow-Up Details:
- dealer_id (FK users)
- follow_up_date, follow_up_time
- follow_up_type (call/meeting/email/site_visit/whatsapp)
- status (scheduled/completed/cancelled)
- outcome, notes
- next_follow_up_date

Audit:
- created_by
- timestamps

Indexes:
- COMPOSITE(followupable_type, followupable_id)
- dealer_id, status
- follow_up_date

Relationships:
- morphTo(followupable) - Lead OR Client
- belongsTo(User as dealer)
```

**6. deal_commissions**
```sql
Core Fields:
- id, deal_id (FK deals)
- dealer_id (FK users)
- commission_type (primary/referral/split)
- commission_percentage, commission_amount
- payment_status (pending/paid/cancelled)
- paid_at, payment_reference

Audit:
- notes
- timestamps

Relationships:
- belongsTo(Deal)
- belongsTo(User as dealer)
```

#### Key Features

✅ **Multi-Channel Lead Tracking**
- Source attribution (website, FB, referral, etc.)
- UTM parameter tracking (optional)
- Referral tracking with referred_by

✅ **Smart Lead Assignment**
- Auto-assign to dealer (round-robin or manual)
- Reassignment capability
- Dealer specialization matching

✅ **Status Flow Management**
```php
// Lead Status Flow
new → contacted → qualified → negotiation → converted (to client)
                                        ↓
                                      lost (with reason)
```

✅ **Lead-to-Client Conversion**
```php
// Conversion Process
1. Validate lead status (should be qualified/negotiation)
2. Create client from lead data
3. Copy dealer assignment
4. Link lead to client (converted_to_client_id)
5. Mark lead as converted with timestamp
6. Track original lead source in client record
```

✅ **Polymorphic Deal System**
```php
// Deal can link to Plot OR Property
$deal->dealable // Returns Plot or Property
$plot->deals    // All deals for this plot
$property->deals // All deals for this property

// Check if inventory available
if (!$plot->deals()->whereIn('status', ['pending', 'confirmed'])->exists()) {
    // Plot available for new deal
}
```

✅ **Auto Commission Calculation**
```php
// From dealer default rate
$commission = ($dealAmount * $dealer->default_commission_rate) / 100;

// Or custom rate per deal
$commission = ($dealAmount * $deal->commission_percentage) / 100;
```

✅ **Deal Lifecycle**
```php
// Status: pending → confirmed → completed
pending    // Deal created, awaiting confirmation
confirmed  // Deal confirmed, inventory booked
completed  // Deal completed, commission earned, inventory sold
cancelled  // Deal cancelled, inventory released
```

#### Controllers

**LeadController**
- `index()` - List with multi-filter (status, priority, source, assigned_to, date_range)
- `create()` - Form with interest selection
- `store()` - Save with auto-assignment
- `show()` - Details with follow-ups
- `edit()`, `update()`
- `destroy()`
- `convert()` - Convert lead to client
- `markAsLost()` - Mark lead as lost

**ClientController**
- Full CRUD
- `index()` - List with filters (type, assigned_to, converted_from_lead)
- Deal history, payment history
- File management

**DealController**
- Full CRUD with polymorphic handling
- `create()` - Select plot/property dynamically
- `store()` - Auto commission calculation
- `confirm()` - Confirm pending deal
- `complete()` - Complete deal, earn commission
- `cancel()` - Cancel with reason

**DealerController**
- Full CRUD
- `performance()` - Performance metrics
- `commissionReport()` - Commission breakdown

**FollowUpController**
- CRUD for follow-ups
- Calendar view
- Reminder system

---

### 5️⃣ FILE MANAGEMENT & PAYMENT MODULE

**Status:** ✅ **PRODUCTION READY**

#### Business Model

```
┌──────────────────────────────────────────────────────────────┐
│              PROPERTY FILE LIFECYCLE                         │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  STEP 1: FILE CREATION (After Deal Confirmation)             │
│  ┌──────────────────────────────────────────────┐            │
│  │ Property File                                │            │
│  │ • file_number: AUTO (FILE-YYYY-NNNNN)        │            │
│  │ • client_id: FK                              │            │
│  │ • fileable: Plot OR Property (Polymorphic)   │            │
│  │ • total_price, down_payment                  │            │
│  │ • remaining_balance: total - down_payment    │            │
│  │ • installment_frequency: monthly/quarterly   │            │
│  │ • total_installments: calculated             │            │
│  │ • start_date, end_date                       │            │
│  │ • late_fee_percentage, grace_period_days     │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STEP 2: AUTO INSTALLMENT GENERATION                         │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ Installment Engine                           │            │
│  │ • Calculate installment_amount               │            │
│  │ • Generate installment schedule              │            │
│  │ • Monthly: 1st of each month                 │            │
│  │ • Quarterly: Every 3 months                  │            │
│  │ • Assign installment numbers (1, 2, 3...)    │            │
│  │ • Set due_dates                              │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STEP 3: PAYMENT PROCESSING                                  │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ Payment Record                               │            │
│  │ • receipt_number: AUTO (RCT-YYYY-NNNNNN)     │            │
│  │ • file_payment_id, installment_id            │            │
│  │ • amount, payment_method                     │            │
│  │ • payment_date, received_by                  │            │
│  │ • bank_reference, cheque_number              │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STEP 4: INSTALLMENT STATUS UPDATE                           │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ • Mark installment as PAID                   │            │
│  │ • Update paid_amount, paid_date              │            │
│  │ • Increment file.paid_installments           │            │
│  │ • Check if file completed (all paid)         │            │
│  │ • Update file status                         │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STEP 5: LATE PAYMENT TRACKING (Daily Cron Job)              │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ Overdue Detection                            │            │
│  │ • Check installments with due_date < TODAY   │            │
│  │ • Status = pending                           │            │
│  │ • Calculate days_overdue                     │            │
│  │ • Calculate late_fee                         │            │
│  │ • Mark is_overdue = TRUE                     │            │
│  │ • Send reminder notifications                │            │
│  └────────────────────┬─────────────────────────┘            │
│                       │                                      │
│  STEP 6: FILE TRANSFER (Optional)                            │
│  ┌────────────────────▼─────────────────────────┐            │
│  │ File Transfer                                │            │
│  │ • from_client_id → to_client_id              │            │
│  │ • Transfer fee, remaining_balance            │            │
│  │ • Approval workflow                          │            │
│  │ • Update file.client_id                      │            │
│  │ • Mark file.status = transferred             │            │
│  │ • Transfer payment history                   │            │
│  └──────────────────────────────────────────────┘            │
└──────────────────────────────────────────────────────────────┘
```

#### Database Tables

**1. property_files**
```sql
Core Fields:
- id, file_number (UNIQUE, auto: FILE-YYYY-NNNNN)
- client_id (FK clients)

Polymorphic (Plot OR Property):
- fileable_type (App\Models\Plot or App\Models\Property)
- fileable_id

Financial:
- total_price, down_payment, remaining_balance
- installment_amount (per installment)
- installment_frequency (monthly/quarterly/semi-annually/annually)
- total_installments (calculated)
- paid_installments (auto-updated)

Dates:
- start_date, end_date (calculated from frequency)

Status:
- status (active/completed/defaulted/transferred/cancelled)

Late Payment Config:
- late_fee_percentage (default 2%)
- grace_period_days (default 7 days)

Assignment:
- dealer_id (FK users) - Who created the file

Audit:
- notes
- timestamps, soft_deletes

Indexes:
- file_number (unique)
- COMPOSITE(fileable_type, fileable_id)
- COMPOSITE(client_id, status)

Relationships:
- belongsTo(Client)
- morphTo(fileable) - Plot OR Property
- belongsTo(User as dealer)
- hasMany(Installment)
- hasMany(FilePayment)
- hasOne(FileTransfer)
```

**2. file_installments**
```sql
Core Fields:
- id, property_file_id (FK CASCADE)
- installment_number (1, 2, 3...)
- due_date
- amount

Status:
- status (pending/paid/partial/overdue/waived)
- paid_amount (default 0)
- paid_date (nullable)

Late Payment:
- late_fee (default 0)
- is_overdue (boolean)
- days_overdue (default 0)
- overdue_since (date)

Reminders:
- reminder_sent (boolean)
- reminder_sent_at

Audit:
- notes
- timestamps

Indexes:
- UNIQUE(property_file_id, installment_number)
- COMPOSITE(due_date, status)
- is_overdue

Relationships:
- belongsTo(PropertyFile)
- hasMany(FilePayment)
```

**3. file_payments**
```sql
Core Fields:
- id, property_file_id (FK CASCADE)
- file_installment_id (FK, nullable) - Links to installment
- client_id (FK clients)
- amount, payment_date
- payment_method (cash/cheque/bank_transfer/online/card)
- receipt_number (UNIQUE, auto: RCT-YYYY-NNNNNN)

Payment Details:
- bank_reference, cheque_number, transaction_id

Categorization:
- payment_type (installment/down_payment/late_fee/adjustment)

Receipt:
- received_by (FK users)

Audit:
- notes
- timestamps

Indexes:
- receipt_number (unique)
- COMPOSITE(property_file_id, payment_date)
- COMPOSITE(client_id, payment_date)

Relationships:
- belongsTo(PropertyFile)
- belongsTo(Installment)
- belongsTo(Client)
- belongsTo(User as receivedBy)
```

**4. file_transfers**
```sql
Core Fields:
- id, property_file_id (FK)
- from_client_id, to_client_id (FK clients)
- transfer_date, transfer_fee
- remaining_balance (at time of transfer)
- reason

Approval:
- approved_by (FK users)
- status (pending/approved/rejected)

Audit:
- notes
- timestamps

Relationships:
- belongsTo(PropertyFile)
- belongsTo(Client as fromClient)
- belongsTo(Client as toClient)
- belongsTo(User as approver)
```

**5. payment_receipts**
```sql
Core Fields:
- id, file_payment_id (FK)
- receipt_number (UNIQUE)
- receipt_path (PDF file path)
- generated_at, generated_by (FK users)
- is_sent, sent_at

Relationships:
- belongsTo(FilePayment)
- belongsTo(User as generator)
```

#### Key Features

✅ **Auto File Number Generation**
```php
// Format: FILE-YYYY-NNNNN
"FILE-2026-00001"
```

✅ **Auto Installment Generation**
```php
// Algorithm
remaining_balance = total_price - down_payment
installment_amount = remaining_balance / total_installments

// Monthly frequency (12 installments per year)
start_date = file start date (e.g., 2026-01-01)
due_dates = 1st of each month (2026-02-01, 2026-03-01, ...)

// Quarterly frequency (4 installments per year)
due_dates = Every 3 months (2026-04-01, 2026-07-01, ...)
```

✅ **Late Payment Detection (Automated)**
```php
// Daily Cron Job: php artisan installments:check-overdue

// Logic
foreach (Installment::where('status', 'pending')->where('due_date', '<', today()) as $inst) {
    $inst->days_overdue = today()->diffInDays($inst->due_date);

    // Grace period check
    if ($inst->days_overdue > $file->grace_period_days) {
        $inst->is_overdue = true;
        $inst->status = 'overdue';

        // Calculate late fee
        $inst->late_fee = ($inst->amount * $file->late_fee_percentage) / 100;
    }
}
```

✅ **Payment Processing Workflow**
```php
// 1. Record payment
$payment = FilePayment::create([
    'property_file_id' => $file->id,
    'file_installment_id' => $installment->id,
    'amount' => $amount,
    'payment_method' => 'cash',
    'receipt_number' => 'RCT-2026-000001', // Auto-generated
    'received_by' => auth()->id(),
]);

// 2. Update installment
if ($amount >= $installment->amount) {
    $installment->status = 'paid';
    $installment->paid_amount = $amount;
    $installment->paid_date = now();
    $file->increment('paid_installments');
}

// 3. Check file completion
if ($file->paid_installments == $file->total_installments) {
    $file->status = 'completed';
    $file->save();
}

// 4. Generate PDF receipt
$pdf = PDF::loadView('receipts.payment', ['payment' => $payment]);
$pdf->save(storage_path('receipts/' . $payment->receipt_number . '.pdf'));
```

✅ **File Transfer System**
```php
// 1. Create transfer request
$transfer = FileTransfer::create([
    'property_file_id' => $file->id,
    'from_client_id' => $oldClient->id,
    'to_client_id' => $newClient->id,
    'transfer_fee' => 50000,
    'remaining_balance' => $file->remaining_balance,
    'status' => 'pending',
]);

// 2. Approval by manager/admin
$transfer->approve(auth()->user());

// 3. Update file owner
$file->client_id = $newClient->id;
$file->status = 'transferred';
$file->save();
```

#### Controllers

**PropertyFileController**
- `index()` - List with filters (client, status, fileable_type)
- `create()` - Form with plot/property selection
- `store()` - Create file + auto-generate installments
- `show()` - Details with installment schedule, payment history
- `edit()`, `update()`
- `destroy()`
- `statement()` - Generate payment statement (PDF)
- `transfer()` - Initiate transfer
- `approveTransfer()` - Approve transfer request

**PaymentController**
- `index()` - List with filters (date, method, file)
- `create()` - Payment form with installment selection
- `store()` - Record payment + update installment + generate receipt
- `show()` - Payment details
- `receipt()` - Download PDF receipt
- `destroy()` - Cancel payment (with authorization)

---

### 6️⃣ EXPENSE & ACCOUNT PAYMENT MODULE

**Status:** ✅ **PRODUCTION READY**

#### Database Tables

**1. payment_types**
```sql
Core Fields:
- id, name, slug (unique)
- category (income/expense)
- description, is_active
- display_order

Examples:
Income: Commission, Rent, Deposit, Advance, Registration Fee
Expense: Office Rent, Utilities, Salaries, Marketing, Maintenance

Indexes:
- slug (unique)
- COMPOSITE(category, is_active)

Relationships:
- hasMany(AccountPayment)
- hasMany(Expense)
```

**2. account_payments** (Income)
```sql
Core Fields:
- id, payment_number (UNIQUE, auto: PMT-YYYY-NNNNNN)
- payment_type_id (FK payment_types)

Polymorphic (Client/Deal/PropertyFile/Dealer):
- payable_type, payable_id

Payment Details:
- amount, payment_date
- payment_method (cash/cheque/bank_transfer/online/card)
- reference_number, bank_name, account_number

Status:
- status (pending/received/cleared/bounced/cancelled)
- clearance_date

Payer Info:
- received_from, contact_number
- purpose, remarks

Documents:
- documents (JSON)

Audit:
- received_by (FK users)
- timestamps, soft_deletes

Indexes:
- payment_number (unique)
- COMPOSITE(payable_type, payable_id)
- COMPOSITE(payment_date, status)

Relationships:
- belongsTo(PaymentType)
- morphTo(payable)
- belongsTo(User as receiver)
```

**3. expenses**
```sql
Core Fields:
- id, expense_number (UNIQUE, auto: EXP-YYYY-NNNNNN)
- payment_type_id (FK payment_types)

Polymorphic (Property/Deal/Project):
- expensable_type, expensable_id

Expense Details:
- amount, expense_date
- payment_method
- reference_number, bank_name, account_number

Status:
- status (pending/paid/cleared/cancelled/refunded)
- payment_date, clearance_date

Vendor:
- paid_to, contact_number, address, tax_id

Recurring:
- is_recurring, recurring_frequency, next_due_date

Financial:
- tax_amount, discount_amount, net_amount

Documents:
- description, documents (JSON)

Audit:
- approved_by, paid_by (FK users)
- timestamps, soft_deletes

Indexes:
- expense_number (unique)
- COMPOSITE(expensable_type, expensable_id)
- COMPOSITE(expense_date, status)
- COMPOSITE(is_recurring, next_due_date)

Relationships:
- belongsTo(PaymentType)
- morphTo(expensable)
- belongsTo(User as approver)
- belongsTo(User as payer)
```

#### Key Features

✅ **Payment Type Categorization**
- Income types vs Expense types
- Organized by category for reporting

✅ **Polymorphic Payments**
```php
// Link payment to any entity
$client->accountPayments(); // Payments from client
$deal->accountPayments();   // Payments related to deal
$dealer->accountPayments(); // Commission payments to dealer
```

✅ **Recurring Expense Management**
```php
// Auto-generate next expense
if ($expense->is_recurring) {
    // Calculate next_due_date based on frequency
    // Monthly: +1 month
    // Quarterly: +3 months
    // Yearly: +1 year
}

// Cron job to create next expense
php artisan expenses:generate-recurring
```

✅ **Payment Status Workflow**
```php
// Account Payment
pending → received → cleared
                  ↓
                bounced (if cheque bounced)

// Expense
pending → paid → cleared
```

#### Controllers

**AccountPaymentController**
- Full CRUD with polymorphic handling
- `index()` - List with filters (type, method, status, date)
- `create()` - Form with entity selection
- `store()` - Record payment
- `show()` - Payment details
- `markAsCleared()` - Mark cheque cleared
- `markAsBounced()` - Handle bounced payment

**ExpenseController**
- Full CRUD
- `index()` - List with filters (type, vendor, status, date)
- `create()` - Form with recurring option
- `store()` - Record expense
- `approve()` - Approval workflow
- `pay()` - Mark as paid

---

### 7️⃣ REPORTING & ANALYTICS MODULE

**Status:** ✅ **PRODUCTION READY**

#### Reports Overview

**1. Plots Report (Available vs Sold)**
- Metrics: Total, Available, Booked, Sold
- Value by status
- Society-wise breakdown
- Charts: Doughnut (status distribution), Bar (value by status)

**2. Payments Report (Monthly Collections)**
- Metrics: Total received, transaction count, average payment
- Daily summary with method breakdown
- Charts: Line (monthly trend), Pie (payment methods)

**3. Dealer Commissions Report**
- Metrics: Total earned, paid, pending
- Dealer-wise breakdown
- Performance ratings
- Charts: Horizontal bar (top 10 dealers)

**4. Overdue Installments Report**
- Metrics: Total overdue, amount, average days
- Aging buckets (1-30, 31-60, 61-90, 90+ days)
- Client-wise list with late fees
- Action buttons for payment

**5. Society-wise Sales Report**
- Metrics: Total societies, sales value, deals completed
- Society performance with sales rate percentage
- Performance badges (Excellent/Good/Average/Poor)
- Charts: Bar (sales by society)

#### Key Features

✅ **Real-time Data Aggregation**
```sql
// Using raw SQL with aggregations
DB::table('plots')
    ->select(
        DB::raw('COUNT(*) as total'),
        DB::raw('SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available')
    )
    ->first();
```

✅ **Chart.js Integration**
- Responsive charts
- Interactive tooltips
- Multiple chart types (line, bar, pie, doughnut)

✅ **Advanced Filters**
- Date range picker
- Society filter
- Dealer filter
- Status filter
- Dynamic query building

✅ **Export Capabilities**
- Excel export (using Maatwebsite/Laravel-Excel)
- PDF export (using TCPDF/DomPDF)
- Print-friendly layouts

#### Controller

**ReportController**
- `index()` - Reports dashboard
- `plotsReport()` - Plots analysis
- `paymentsReport()` - Payment trends
- `commissionsReport()` - Dealer performance
- `overdueReport()` - Late payments
- `societySalesReport()` - Society comparison
- `exportExcel()` - Excel export
- `exportPDF()` - PDF export

---

## 🔐 SECURITY FEATURES

### Authentication
- ✅ Laravel Sanctum for API (optional)
- ✅ Session-based auth for web
- ✅ Password hashing (bcrypt)
- ✅ Remember me functionality
- ✅ Email verification (optional)
- ✅ Password reset flow

### Authorization
- ✅ Spatie Permission package
- ✅ Role-based access control (RBAC)
- ✅ Permission-based guards
- ✅ Scope-based data access (own vs all)
- ✅ Middleware protection on routes
- ✅ Policy-based authorization

### Data Protection
- ✅ CSRF protection on forms
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade escaping)
- ✅ Mass assignment protection ($fillable)
- ✅ Soft deletes for critical data
- ✅ Audit trails (created_by, updated_by)

### File Security
- ✅ File upload validation
- ✅ Secure storage (outside public folder)
- ✅ File type restrictions
- ✅ File size limits
- ✅ Virus scanning (optional)

---

## 📈 PERFORMANCE OPTIMIZATION

### Database
- ✅ Indexed columns (foreign keys, search fields)
- ✅ Composite indexes for common queries
- ✅ FULLTEXT indexes for search
- ✅ Eager loading (with, load)
- ✅ Query optimization (avoid N+1)
- ✅ Database connection pooling

### Caching
- ✅ Query result caching
- ✅ View caching
- ✅ Config caching
- ✅ Route caching
- ✅ Permission caching (Spatie)

### Frontend
- ✅ Asset minification (Vite)
- ✅ Lazy loading images
- ✅ Pagination (20 per page)
- ✅ Debouncing on search
- ✅ CDN for libraries (Chart.js, FontAwesome)

---

## 🚀 DEPLOYMENT CHECKLIST

### Environment Setup
- [ ] PHP 8.2+
- [ ] MySQL 8.0+
- [ ] Composer dependencies installed
- [ ] NPM dependencies installed
- [ ] .env configured
- [ ] APP_KEY generated
- [ ] Database created

### Database
- [ ] Run migrations: `php artisan migrate`
- [ ] Run seeders: `php artisan db:seed`
- [ ] Verify default users created

### Storage
- [ ] Link storage: `php artisan storage:link`
- [ ] Set permissions: `chmod -R 775 storage bootstrap/cache`

### Optimization
- [ ] Config cache: `php artisan config:cache`
- [ ] Route cache: `php artisan route:cache`
- [ ] View cache: `php artisan view:cache`
- [ ] Build assets: `npm run build`

### Scheduled Tasks
- [ ] Set up cron: `* * * * * php artisan schedule:run`
- [ ] Verify: `php artisan schedule:list`

### Security
- [ ] Change default passwords
- [ ] Set APP_DEBUG=false
- [ ] Configure CORS
- [ ] Set up SSL certificate
- [ ] Configure firewall

---

## 📝 MAINTENANCE

### Daily Tasks
- Monitor logs: `storage/logs/laravel.log`
- Check overdue installments (auto via cron)
- Verify payment entries

### Weekly Tasks
- Database backup
- Check system health
- Review error logs
- Performance monitoring

### Monthly Tasks
- Security updates
- Dependency updates
- User access review
- Data cleanup (soft deleted records)

---

## 🔮 FUTURE ENHANCEMENTS

### Phase 2
- [ ] SMS notifications (Twilio)
- [ ] Email notifications (SMTP)
- [ ] WhatsApp integration
- [ ] Mobile app (Flutter/React Native)
- [ ] Advanced analytics (Power BI)

### Phase 3
- [ ] Online payment gateway (Stripe/PayPal)
- [ ] Customer portal (self-service)
- [ ] Document e-signing (DocuSign)
- [ ] CRM automation workflows
- [ ] AI-powered lead scoring

---

**END OF PRODUCTION ARCHITECTURE DOCUMENT**
