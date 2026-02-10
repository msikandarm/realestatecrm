# DATABASE ENTITY-RELATIONSHIP DIAGRAM (ERD)
## Real Estate Management System

---

## 📊 COMPLETE DATABASE STRUCTURE

### Total Tables: 25
### Total Relationships: 50+
### Database Engine: InnoDB (MySQL 8.0+)

---

## 🗺️ VISUAL ENTITY-RELATIONSHIP DIAGRAM

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           USER MANAGEMENT LAYER                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                   │
│      ┌──────────┐         ┌────────────┐         ┌──────────┐                  │
│      │  users   │────────▶│ role_user  │◀────────│  roles   │                  │
│      │          │  Many   │  (pivot)   │  Many   │          │                  │
│      │ • id     │         │ • user_id  │         │ • id     │                  │
│      │ • name   │         │ • role_id  │         │ • name   │                  │
│      │ • email  │         └────────────┘         │ • slug   │                  │
│      │ • phone  │                                 └─────┬────┘                  │
│      │ • cnic   │                                       │                       │
│      └────┬─────┘                                       │                       │
│           │                                      ┌──────┴────────┐              │
│           │                                      │               │              │
│           │                                      ▼               ▼              │
│           │                              ┌────────────┐  ┌──────────────┐      │
│           │                              │permission_ │  │ permissions  │      │
│           │                              │    role    │  │              │      │
│           │                              │ (pivot)    │  │ • id         │      │
│           │                              │            │  │ • name       │      │
│           │                              │• role_id   │  │ • slug       │      │
│           │                              │• perm_id   │  │ • module     │      │
│           │                              └────────────┘  └──────────────┘      │
│           │                                                                     │
└───────────┼─────────────────────────────────────────────────────────────────────┘
            │
            │ (assigned_to, created_by, etc.)
            │
┌───────────┼─────────────────────────────────────────────────────────────────────┐
│           │                  PROPERTY & PLOT LAYER                               │
├───────────┼─────────────────────────────────────────────────────────────────────┤
│           │                                                                       │
│           │         ┌────────────┐                                               │
│           │         │ societies  │                                               │
│           │         │            │                                               │
│           │         │ • id       │                                               │
│           │         │ • name     │                                               │
│           │         │ • location │                                               │
│           │         │ • status   │                                               │
│           │         └─────┬──────┘                                               │
│           │               │                                                       │
│           │               ├──────────────┬──────────────┐                       │
│           │               │              │              │                       │
│           │               ▼              ▼              ▼                       │
│           │         ┌──────────┐   ┌──────────┐  ┌──────────┐                 │
│           │         │  blocks  │   │  plots   │  │properties│                 │
│           │         │          │   │          │  │          │                 │
│           │         │ • id     │   │ • id     │  │ • id     │                 │
│           │         │ • name   │   │ • number │  │ • title  │                 │
│           │         │• soc_id  │   │• soc_id  │  │• soc_id  │                 │
│           │         └────┬─────┘   │• block_id│  │• type_id │                 │
│           │              │          │• size    │  │• size    │                 │
│           │              │          │• status  │  │• status  │                 │
│           │              ▼          └────┬─────┘  └────┬─────┘                 │
│           │         ┌──────────┐        │             │                       │
│           │         │ streets  │        │             │                       │
│           │         │          │        │             │                       │
│           │         │ • id     │        │             │                       │
│           │         │ • name   │        │             │                       │
│           │         │• block_id│        │             │                       │
│           │         └────┬─────┘        │             │                       │
│           │              │              │             │                       │
│           │              └──────────────┼─────────────┘                       │
│           │                             │                                      │
│           │                             │                                      │
│           │              ┌──────────────┴──────────────┐                      │
│           │              │                             │                      │
│           │              ▼                             ▼                      │
│           │       ┌──────────┐                  ┌─────────────┐             │
│           │       │plot_maps │                  │property_    │             │
│           │       │          │                  │  images     │             │
│           │       │ • id     │                  │             │             │
│           │       │• plot_id │                  │ • id        │             │
│           │       │• path    │                  │• property_id│             │
│           │       │• type    │                  │• path       │             │
│           │       └──────────┘                  │• is_primary │             │
│           │                                      └─────────────┘             │
│           │                                                                   │
│           │              ┌────────────────┐                                  │
│           │              │property_types  │                                  │
│           │              │                │                                  │
│           │              │ • id           │                                  │
│           │              │ • name         │                                  │
│           │              │ • slug         │                                  │
│           │              └────────────────┘                                  │
│           │                                                                   │
└───────────┼───────────────────────────────────────────────────────────────────┘
            │
            │
┌───────────┼───────────────────────────────────────────────────────────────────┐
│           │                     CRM LAYER                                      │
├───────────┼───────────────────────────────────────────────────────────────────┤
│           │                                                                    │
│           │         ┌──────────┐                                              │
│           ├────────▶│ dealers  │                                              │
│           │         │          │                                              │
│           │         │ • id     │                                              │
│           │         │• user_id │                                              │
│           │         │ • code   │                                              │
│           │         │ • phone  │                                              │
│           │         └────┬─────┘                                              │
│           │              │                                                     │
│           │              │ (assigned_to)                                      │
│           │              │                                                     │
│           │              ├──────────────┬──────────────┐                     │
│           │              ▼              ▼              ▼                     │
│           │         ┌─────────┐   ┌─────────┐   ┌─────────┐                │
│           │         │  leads  │   │ clients │   │  deals  │                │
│           │         │         │   │         │   │         │                │
│           │         │ • id    │──▶│ • id    │◀──│ • id    │                │
│           │         │ • name  │   │ • name  │   │ • number│                │
│           │         │ • phone │   │ • cnic  │   │• client │                │
│           │         │ • source│   │ • type  │   │• dealer │                │
│           │         │ • status│   │         │   │• amount │                │
│           │         └────┬────┘   └────┬────┘   └────┬────┘                │
│           │              │             │             │                       │
│           │              │             │             │                       │
│           │              └─────┬───────┘             │                       │
│           │                    │                     │                       │
│           │                    ▼                     │                       │
│           │            ┌──────────────┐              │                       │
│           │            │  follow_ups  │              │                       │
│           │            │              │              │                       │
│           │            │ • id         │              │                       │
│           │            │• followable_ │              │                       │
│           │            │  type/id     │              │                       │
│           │            │  (POLY)      │              │                       │
│           │            │• scheduled   │              │                       │
│           │            │• status      │              │                       │
│           │            └──────────────┘              │                       │
│           │                                          │                       │
└──────────────────────────────────────────────────────┼───────────────────────┘
                                                       │
                                                       │
┌──────────────────────────────────────────────────────┼───────────────────────┐
│                              FILE & PAYMENT LAYER    │                        │
├──────────────────────────────────────────────────────┼───────────────────────┤
│                                                      │                        │
│                                              ┌───────┴─────────┐             │
│                                              │                 │             │
│         ┌──────────────────┐                 ▼                 ▼             │
│         │   plots/         │          ┌──────────────┐  ┌──────────┐        │
│         │   properties     │          │ property_    │  │ payments │        │
│         │   (from above)   │◀─────────│   files      │  │ (general)│        │
│         └──────────────────┘          │              │  │          │        │
│                 ▲                     │ • id         │  │ • id     │        │
│                 │                     │ • file_num   │  │ • receipt│        │
│                 │ fileable            │• client_id   │  │ • amount │        │
│                 │ (POLYMORPHIC)       │• deal_id     │  │ • type   │        │
│                 │                     │• fileable_   │  │ • date   │        │
│                 │                     │  type/id     │  └──────────┘        │
│                 │                     │  (POLY)      │                       │
│                 │                     │• total_amt   │                       │
│                 │                     │• paid_amt    │                       │
│                 └─────────────────────│• status      │                       │
│                                       └───┬──────────┘                       │
│                                           │                                  │
│                                           ├────────────┬─────────────┐       │
│                                           │            │             │       │
│                                           ▼            ▼             ▼       │
│                                   ┌────────────┐ ┌──────────┐ ┌──────────┐ │
│                                   │file_       │ │file_     │ │file_     │ │
│                                   │installments│ │payments  │ │transfers │ │
│                                   │            │ │          │ │          │ │
│                                   │ • id       │ │ • id     │ │ • id     │ │
│                                   │• file_id   │ │• file_id │ │• file_id │ │
│                                   │• inst_num  │ │• inst_id │ │• from    │ │
│                                   │• amount    │ │• amount  │ │• to      │ │
│                                   │• due_date  │ │• receipt │ │• date    │ │
│                                   │• status    │ │• date    │ │• status  │ │
│                                   └────────────┘ └──────────┘ └──────────┘ │
│                                                                              │
└──────────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────────┐
│                        FINANCIAL & REPORTING LAYER                            │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                                │
│         ┌──────────┐                          ┌──────────┐                   │
│         │ expenses │                          │ reports  │                   │
│         │          │                          │          │                   │
│         │ • id     │                          │ • id     │                   │
│         │ • number │                          │ • type   │                   │
│         │ • category                          │ • title  │                   │
│         │ • amount │                          │ • params │                   │
│         │ • date   │                          │ • path   │                   │
│         │• dealer_id                          │• gen_by  │───────┐           │
│         │• approved                           │ • dates  │       │           │
│         │  _by     │───────┐                  └──────────┘       │           │
│         └──────────┘       │                                     │           │
│                            │                                     │           │
│                            └─────────────────────────────────────┘           │
│                                            │                                  │
│                                            ▼                                  │
│                                       ┌─────────┐                            │
│                                       │  users  │                            │
│                                       │ (from   │                            │
│                                       │  above) │                            │
│                                       └─────────┘                            │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📋 TABLE DETAILS & RELATIONSHIPS

### 1. USER MANAGEMENT MODULE

#### users
- **Primary Key**: `id`
- **Unique**: `email`, `cnic`
- **Relationships**:
  - Many-to-Many with `roles` through `role_user`
  - One-to-Many with `dealers` (user_id)
  - One-to-Many with `leads` (assigned_to)
  - One-to-Many with `clients` (assigned_to)
  - One-to-Many with `follow_ups` (assigned_to)
  - One-to-Many with `deals` (dealer_id, approved_by)
  - One-to-Many with `expenses` (recorded_by, approved_by)
  - One-to-Many with `reports` (generated_by)

#### roles
- **Primary Key**: `id`
- **Unique**: `slug`
- **Relationships**:
  - Many-to-Many with `users` through `role_user`
  - Many-to-Many with `permissions` through `permission_role`

#### permissions
- **Primary Key**: `id`
- **Unique**: `slug`
- **Indexed**: `module`
- **Relationships**:
  - Many-to-Many with `roles` through `permission_role`

#### role_user (Pivot)
- **Foreign Keys**: `user_id`, `role_id`
- **Unique Constraint**: `(user_id, role_id)`

#### permission_role (Pivot)
- **Foreign Keys**: `permission_id`, `role_id`
- **Unique Constraint**: `(permission_id, role_id)`

---

### 2. PROPERTY & PLOT MODULE

#### societies
- **Primary Key**: `id`
- **Unique**: `slug`
- **Indexed**: `status`, `is_active`
- **Relationships**:
  - One-to-Many with `blocks`
  - One-to-Many with `plots`
  - One-to-Many with `properties`
  - One-to-Many with `leads` (interest_id)

#### blocks
- **Primary Key**: `id`
- **Foreign Keys**: `society_id`
- **Unique Constraint**: `(society_id, name)`
- **Relationships**:
  - Many-to-One with `societies`
  - One-to-Many with `streets`
  - One-to-Many with `plots`
  - One-to-Many with `properties`

#### streets
- **Primary Key**: `id`
- **Foreign Keys**: `block_id`
- **Unique Constraint**: `(block_id, name)`
- **Relationships**:
  - Many-to-One with `blocks`
  - One-to-Many with `plots`
  - One-to-Many with `properties`

#### plots
- **Primary Key**: `id`
- **Foreign Keys**: `society_id`, `block_id`, `street_id`
- **Unique Constraint**: `(society_id, block_id, plot_number)`
- **Indexed**: `status`, `category`
- **Relationships**:
  - Many-to-One with `societies`, `blocks`, `streets`
  - One-to-Many with `plot_maps`
  - Polymorphic One-to-Many with `deals` (dealable)
  - Polymorphic One-to-Many with `property_files` (fileable)

#### plot_maps
- **Primary Key**: `id`
- **Foreign Keys**: `plot_id`, `uploaded_by`
- **Indexed**: `map_type`, `is_primary`
- **Relationships**:
  - Many-to-One with `plots`
  - Many-to-One with `users` (uploaded_by)

#### property_types
- **Primary Key**: `id`
- **Unique**: `name`, `slug`
- **Relationships**:
  - One-to-Many with `properties`

#### properties
- **Primary Key**: `id`
- **Foreign Keys**: `property_type_id`, `society_id`, `block_id`, `street_id`, `plot_id`
- **Indexed**: `status`, `property_for`, `is_featured`
- **Relationships**:
  - Many-to-One with `property_types`
  - Many-to-One with `societies`, `blocks`, `streets`, `plots`
  - One-to-Many with `property_images`
  - Polymorphic One-to-Many with `deals` (dealable)
  - Polymorphic One-to-Many with `property_files` (fileable)

#### property_images
- **Primary Key**: `id`
- **Foreign Keys**: `property_id`, `uploaded_by`
- **Indexed**: `is_primary`, `sort_order`
- **Relationships**:
  - Many-to-One with `properties`
  - Many-to-One with `users` (uploaded_by)

---

### 3. CRM MODULE

#### dealers
- **Primary Key**: `id`
- **Foreign Keys**: `user_id`
- **Unique**: `user_id`, `dealer_code`
- **Indexed**: `status`
- **Relationships**:
  - One-to-One with `users`
  - One-to-Many with `deals`
  - One-to-Many with `expenses` (commission)

#### leads
- **Primary Key**: `id`
- **Foreign Keys**: `society_id`, `assigned_to`, `converted_to_client_id`
- **Indexed**: `status`, `priority`, `source`
- **Relationships**:
  - Many-to-One with `societies`
  - Many-to-One with `users` (assigned_to)
  - Many-to-One with `clients` (conversion)
  - Polymorphic One-to-Many with `follow_ups`

#### clients
- **Primary Key**: `id`
- **Unique**: `client_code`, `cnic`
- **Foreign Keys**: `assigned_to`
- **Indexed**: `client_type`, `is_active`
- **Relationships**:
  - Many-to-One with `users` (assigned_to)
  - One-to-Many with `deals`
  - One-to-Many with `property_files`
  - One-to-Many with `file_payments`
  - Polymorphic One-to-Many with `follow_ups`

#### follow_ups
- **Primary Key**: `id`
- **Foreign Keys**: `assigned_to`
- **Polymorphic**: `followable_type`, `followable_id`
- **Indexed**: `scheduled_at`, `status`
- **Relationships**:
  - Polymorphic Many-to-One with `leads` or `clients`
  - Many-to-One with `users` (assigned_to)

#### deals
- **Primary Key**: `id`
- **Unique**: `deal_number`
- **Foreign Keys**: `client_id`, `dealer_id`, `approved_by`
- **Polymorphic**: `dealable_type`, `dealable_id`
- **Indexed**: `status`, `deal_date`
- **Relationships**:
  - Many-to-One with `clients`
  - Many-to-One with `dealers`
  - Many-to-One with `users` (approved_by)
  - Polymorphic Many-to-One with `plots` or `properties`
  - One-to-Many with `property_files`

---

### 4. FILE & INSTALLMENT MODULE

#### property_files
- **Primary Key**: `id`
- **Unique**: `file_number`
- **Foreign Keys**: `client_id`, `deal_id`, `transferred_to_client_id`
- **Polymorphic**: `fileable_type`, `fileable_id`
- **Indexed**: `status`, `start_date`
- **Relationships**:
  - Many-to-One with `clients`
  - Many-to-One with `deals`
  - Polymorphic Many-to-One with `plots` or `properties`
  - One-to-Many with `file_installments`
  - One-to-Many with `file_payments`
  - One-to-Many with `file_transfers`

#### file_installments
- **Primary Key**: `id`
- **Foreign Keys**: `property_file_id`
- **Unique Constraint**: `(property_file_id, installment_number)`
- **Indexed**: `due_date`, `status`
- **Relationships**:
  - Many-to-One with `property_files`
  - One-to-Many with `file_payments`

#### file_payments
- **Primary Key**: `id`
- **Unique**: `receipt_number`
- **Foreign Keys**: `property_file_id`, `file_installment_id`, `client_id`, `received_by`
- **Indexed**: `payment_date`, `status`
- **Relationships**:
  - Many-to-One with `property_files`
  - Many-to-One with `file_installments`
  - Many-to-One with `clients`
  - Many-to-One with `users` (received_by)

#### file_transfers
- **Primary Key**: `id`
- **Foreign Keys**: `property_file_id`, `from_client_id`, `to_client_id`, `approved_by`
- **Indexed**: `transfer_date`, `status`
- **Relationships**:
  - Many-to-One with `property_files`
  - Many-to-One with `clients` (from/to)
  - Many-to-One with `users` (approved_by)

---

### 5. FINANCIAL MODULE

#### payments
- **Primary Key**: `id`
- **Unique**: `receipt_number`
- **Foreign Keys**: `client_id`, `deal_id`, `received_by`
- **Polymorphic**: `payable_type`, `payable_id`
- **Indexed**: `payment_date`, `payment_type`, `status`
- **Relationships**:
  - Many-to-One with `clients`, `deals`, `users`
  - Polymorphic Many-to-One with `plots`, `properties`, or `property_files`

#### expenses
- **Primary Key**: `id`
- **Unique**: `expense_number`
- **Foreign Keys**: `dealer_id`, `approved_by`, `recorded_by`
- **Indexed**: `category`, `expense_date`, `status`
- **Relationships**:
  - Many-to-One with `dealers`
  - Many-to-One with `users` (approved_by, recorded_by)

#### reports
- **Primary Key**: `id`
- **Foreign Keys**: `generated_by`
- **Indexed**: `report_type`, `created_at`, `status`
- **Relationships**:
  - Many-to-One with `users` (generated_by)

---

## 🔗 POLYMORPHIC RELATIONSHIPS EXPLAINED

### 1. follow_ups (Polymorphic to leads OR clients)
```sql
followable_type: 'Lead' or 'Client'
followable_id: ID of the lead or client

Example:
- followable_type = 'Lead', followable_id = 5
  → Follow-up for lead #5
- followable_type = 'Client', followable_id = 12
  → Follow-up for client #12
```

### 2. deals (Polymorphic to plots OR properties)
```sql
dealable_type: 'Plot' or 'Property'
dealable_id: ID of the plot or property

Example:
- dealable_type = 'Plot', dealable_id = 100
  → Deal for plot #100
- dealable_type = 'Property', dealable_id = 25
  → Deal for property #25
```

### 3. property_files (Polymorphic to plots OR properties)
```sql
fileable_type: 'Plot' or 'Property'
fileable_id: ID of the plot or property

Example:
- fileable_type = 'Plot', fileable_id = 100
  → File for plot #100
- fileable_type = 'Property', fileable_id = 25
  → File for property #25
```

### 4. payments (Polymorphic to plots, properties, OR property_files)
```sql
payable_type: 'Plot', 'Property', or 'PropertyFile'
payable_id: Corresponding ID

Example:
- payable_type = 'Plot', payable_id = 50
  → Token payment for plot #50
- payable_type = 'PropertyFile', payable_id = 10
  → Payment for file #10
```

---

## 📊 INDEXING STRATEGY

### Primary Indexes
- All tables have `id` as PRIMARY KEY (AUTO_INCREMENT)

### Unique Indexes
- `users.email`, `users.cnic`
- `roles.slug`
- `permissions.slug`
- `societies.slug`
- `dealers.dealer_code`
- `clients.client_code`, `clients.cnic`
- `deals.deal_number`
- `property_files.file_number`
- `file_payments.receipt_number`
- `payments.receipt_number`

### Composite Unique Indexes
- `role_user(user_id, role_id)`
- `permission_role(permission_id, role_id)`
- `blocks(society_id, name)`
- `streets(block_id, name)`
- `plots(society_id, block_id, plot_number)`
- `file_installments(property_file_id, installment_number)`

### Foreign Key Indexes
- All foreign key columns are indexed automatically

### Status/Type Indexes
- `users.is_active`
- `societies.status`, `societies.is_active`
- `plots.status`, `plots.category`
- `properties.status`, `properties.property_for`, `properties.is_featured`
- `leads.status`, `leads.priority`, `leads.source`
- `clients.client_type`, `clients.is_active`
- `deals.status`, `deals.deal_date`
- `file_installments.due_date`, `file_installments.status`
- `payments.payment_date`, `payments.payment_type`

### Fulltext Indexes
- `societies(name, location, city)`
- `properties(title, address, description)`
- `clients(name, email, phone, cnic)`

---

## 🔐 FOREIGN KEY CONSTRAINTS

### ON DELETE CASCADE
Used when child records should be deleted when parent is deleted:
- `role_user` → `users`, `roles`
- `permission_role` → `permissions`, `roles`
- `blocks` → `societies`
- `streets` → `blocks`
- `plots` → `societies`, `blocks`
- `plot_maps` → `plots`
- `properties` → `societies`
- `property_images` → `properties`
- `file_installments` → `property_files`
- `file_payments` → `property_files`, `clients`
- `file_transfers` → `property_files`, `clients`

### ON DELETE SET NULL
Used when reference should be cleared but record preserved:
- `plots.street_id` → `streets`
- `properties.block_id`, `street_id`, `plot_id`
- `leads.assigned_to`, `converted_to_client_id`
- `clients.assigned_to`
- `dealers.user_id`
- `deals.dealer_id`, `approved_by`
- Various `uploaded_by`, `received_by`, `approved_by` references

### ON DELETE RESTRICT
Used when deletion should be prevented if references exist:
- `properties.property_type_id` → `property_types`

---

## 📈 DATA GROWTH ESTIMATES

```
┌──────────────────┬──────────────┬─────────────┬──────────────┐
│ Table            │ Year 1       │ Year 3      │ Growth Rate  │
├──────────────────┼──────────────┼─────────────┼──────────────┤
│ users            │ 50           │ 100         │ Slow         │
│ roles            │ 5            │ 10          │ Static       │
│ permissions      │ 60           │ 100         │ Slow         │
│ dealers          │ 10           │ 30          │ Medium       │
│ societies        │ 20           │ 50          │ Slow         │
│ blocks           │ 200          │ 500         │ Medium       │
│ streets          │ 1,000        │ 3,000       │ Medium       │
│ plots            │ 10,000       │ 50,000      │ High         │
│ plot_maps        │ 20,000       │ 100,000     │ High         │
│ property_types   │ 10           │ 20          │ Static       │
│ properties       │ 2,000        │ 10,000      │ High         │
│ property_images  │ 10,000       │ 60,000      │ High         │
│ leads            │ 30,000       │ 150,000     │ Very High    │
│ clients          │ 5,000        │ 25,000      │ High         │
│ follow_ups       │ 50,000       │ 300,000     │ Very High    │
│ deals            │ 3,000        │ 15,000      │ High         │
│ property_files   │ 3,000        │ 15,000      │ High         │
│ file_installments│ 60,000       │ 360,000     │ Very High    │
│ file_payments    │ 60,000       │ 360,000     │ Very High    │
│ file_transfers   │ 500          │ 3,000       │ Medium       │
│ payments         │ 10,000       │ 50,000      │ High         │
│ expenses         │ 5,000        │ 20,000      │ Medium       │
│ reports          │ 1,000        │ 5,000       │ Medium       │
├──────────────────┼──────────────┼─────────────┼──────────────┤
│ TOTAL RECORDS    │ ~250,000     │ ~1,500,000  │              │
│ DATABASE SIZE    │ 500 MB       │ 5 GB        │              │
└──────────────────┴──────────────┴─────────────┴──────────────┘
```

---

## 🎯 PERFORMANCE OPTIMIZATION TIPS

### 1. Partitioning Strategy (Future)
- Partition `leads` by status (active vs converted/lost)
- Partition `follow_ups` by year
- Partition `payments` and `file_payments` by year

### 2. Archiving Strategy
- Archive completed `property_files` after 2 years
- Archive lost `leads` after 1 year
- Archive old `follow_ups` after 1 year

### 3. Caching Recommendations
- Cache frequently accessed societies/blocks
- Cache property types
- Cache role-permission mappings
- Cache dashboard statistics

### 4. Query Optimization
- Use eager loading for polymorphic relationships
- Add composite indexes for common WHERE clauses
- Use query result caching for reports
- Implement pagination on all list views

---

**For complete implementation details, see:**
- DATABASE-SCHEMA.sql - Complete SQL schema
- DOCUMENTATION.md - System documentation
- SYSTEM-SUMMARY.md - Architecture overview

---

**Database Schema Version**: 2.0
**Created**: January 28, 2026
**Database Engine**: InnoDB (MySQL 8.0+)
