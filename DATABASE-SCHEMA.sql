-- ============================================================
-- REAL ESTATE MANAGEMENT SYSTEM - COMPLETE DATABASE SCHEMA
-- ============================================================
-- Database: realestatecrm
-- Created: January 28, 2026
-- MySQL Version: 8.0+
-- ============================================================

-- ============================================================
-- SECTION 1: USER MANAGEMENT & AUTHENTICATION
-- ============================================================

-- Users Table
-- Stores all system users (admin, managers, dealers, accountants, staff)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    cnic VARCHAR(20) UNIQUE COMMENT 'National ID Card Number',
    address TEXT,
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles Table
-- Defines user roles (admin, manager, dealer, accountant, staff)
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions Table
-- Defines granular permissions for each module
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) COMMENT 'Module name: users, properties, plots, crm, files, reports',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role-User Pivot Table (Many-to-Many)
-- Links users to multiple roles
CREATE TABLE role_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permission-Role Pivot Table (Many-to-Many)
-- Links permissions to roles
CREATE TABLE permission_role (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_permission_role (permission_id, role_id),
    INDEX idx_permission_id (permission_id),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SECTION 2: SOCIETY & PLOT MANAGEMENT
-- ============================================================

-- Societies Table
-- Housing societies/projects
CREATE TABLE societies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(255),
    city VARCHAR(100),
    total_area DECIMAL(15,2) COMMENT 'Total area in square feet',
    description TEXT,
    map_location VARCHAR(255) COMMENT 'Google Maps coordinates',
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    status ENUM('planning', 'under_development', 'developed', 'completed') DEFAULT 'planning',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_is_active (is_active),
    FULLTEXT idx_search (name, location, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blocks Table
-- Subdivisions within societies (Block A, B, C, etc.)
CREATE TABLE blocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    block_number VARCHAR(50),
    total_plots INT UNSIGNED DEFAULT 0,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (society_id) REFERENCES societies(id) ON DELETE CASCADE,
    INDEX idx_society_id (society_id),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_society_block (society_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Streets Table
-- Streets within blocks
CREATE TABLE streets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    block_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    street_number VARCHAR(50),
    width DECIMAL(8,2) COMMENT 'Width in feet',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (block_id) REFERENCES blocks(id) ON DELETE CASCADE,
    INDEX idx_block_id (block_id),
    UNIQUE KEY unique_block_street (block_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plots Table
-- Individual plots/land parcels
CREATE TABLE plots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id BIGINT UNSIGNED NOT NULL,
    block_id BIGINT UNSIGNED NOT NULL,
    street_id BIGINT UNSIGNED,
    plot_number VARCHAR(50) NOT NULL,
    size_marla DECIMAL(10,2) COMMENT '1 Marla = 272.25 sqft',
    size_kanal DECIMAL(10,2) COMMENT '1 Kanal = 5445 sqft = 20 Marla',
    size_in_sqft DECIMAL(12,2) NOT NULL COMMENT 'Converted to sqft for standardization',
    category ENUM('residential', 'commercial', 'agricultural') DEFAULT 'residential',
    plot_type ENUM('corner', 'boulevard', 'park_facing', 'main_road', 'normal') DEFAULT 'normal',
    is_corner BOOLEAN DEFAULT FALSE,
    is_park_facing BOOLEAN DEFAULT FALSE,
    is_main_road_facing BOOLEAN DEFAULT FALSE,
    price_per_marla DECIMAL(15,2),
    total_price DECIMAL(15,2),
    status ENUM('available', 'booked', 'sold', 'reserved') DEFAULT 'available',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (society_id) REFERENCES societies(id) ON DELETE CASCADE,
    FOREIGN KEY (block_id) REFERENCES blocks(id) ON DELETE CASCADE,
    FOREIGN KEY (street_id) REFERENCES streets(id) ON DELETE SET NULL,
    INDEX idx_society_id (society_id),
    INDEX idx_block_id (block_id),
    INDEX idx_street_id (street_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    UNIQUE KEY unique_plot (society_id, block_id, plot_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plot Maps Table
-- Store plot map images and documents
CREATE TABLE plot_maps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plot_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255),
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) COMMENT 'image/pdf/dwg',
    file_size INT UNSIGNED COMMENT 'Size in bytes',
    map_type ENUM('site_plan', 'survey', 'boundary', 'location', 'other') DEFAULT 'site_plan',
    uploaded_by BIGINT UNSIGNED,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_plot_id (plot_id),
    INDEX idx_map_type (map_type),
    INDEX idx_is_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SECTION 3: PROPERTY MANAGEMENT
-- ============================================================

-- Property Types Table
-- Categories of properties (house, apartment, commercial, etc.)
CREATE TABLE property_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100) COMMENT 'Icon class or path',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Properties Table
-- Built properties (houses, apartments, commercial buildings)
CREATE TABLE properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_type_id BIGINT UNSIGNED NOT NULL,
    society_id BIGINT UNSIGNED NOT NULL,
    block_id BIGINT UNSIGNED,
    street_id BIGINT UNSIGNED,
    plot_id BIGINT UNSIGNED COMMENT 'Reference to plot if property is built on a plot',
    property_number VARCHAR(50),
    title VARCHAR(255) NOT NULL,
    address TEXT,
    size_marla DECIMAL(10,2),
    size_kanal DECIMAL(10,2),
    size_in_sqft DECIMAL(12,2) NOT NULL,
    bedrooms TINYINT UNSIGNED,
    bathrooms TINYINT UNSIGNED,
    kitchens TINYINT UNSIGNED DEFAULT 1,
    floors TINYINT UNSIGNED DEFAULT 1,
    property_for ENUM('sale', 'rent', 'both') DEFAULT 'sale',
    price_sale DECIMAL(15,2),
    price_rent DECIMAL(15,2),
    status ENUM('available', 'sold', 'rented', 'reserved') DEFAULT 'available',
    is_featured BOOLEAN DEFAULT FALSE,
    year_built YEAR,
    description TEXT,
    amenities TEXT COMMENT 'Parking, Garden, Security, etc. (JSON or comma-separated)',
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (society_id) REFERENCES societies(id) ON DELETE CASCADE,
    FOREIGN KEY (block_id) REFERENCES blocks(id) ON DELETE SET NULL,
    FOREIGN KEY (street_id) REFERENCES streets(id) ON DELETE SET NULL,
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE SET NULL,
    INDEX idx_property_type_id (property_type_id),
    INDEX idx_society_id (society_id),
    INDEX idx_status (status),
    INDEX idx_property_for (property_for),
    INDEX idx_is_featured (is_featured),
    FULLTEXT idx_search (title, address, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property Images Table
-- Store multiple images per property
CREATE TABLE property_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255),
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED,
    image_type ENUM('exterior', 'interior', 'kitchen', 'bedroom', 'bathroom', 'other') DEFAULT 'other',
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    uploaded_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_property_id (property_id),
    INDEX idx_is_primary (is_primary),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SECTION 4: CRM SYSTEM (Leads, Clients, Deals)
-- ============================================================

-- Dealers Table
-- Sales team members (can also be users with dealer role)
CREATE TABLE dealers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE COMMENT 'Link to users table',
    dealer_code VARCHAR(50) UNIQUE,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    cnic VARCHAR(20),
    address TEXT,
    commission_percentage DECIMAL(5,2) DEFAULT 2.00 COMMENT 'Default commission %',
    total_deals INT UNSIGNED DEFAULT 0,
    total_commission DECIMAL(15,2) DEFAULT 0,
    join_date DATE,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_dealer_code (dealer_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Leads Table
-- Potential customers/prospects
CREATE TABLE leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    whatsapp VARCHAR(20),
    cnic VARCHAR(20),
    address TEXT,
    source ENUM('walk_in', 'phone_call', 'website', 'referral', 'social_media', 'other') DEFAULT 'walk_in',
    interest_type ENUM('plot', 'property', 'both') DEFAULT 'plot',
    society_id BIGINT UNSIGNED COMMENT 'Interested society',
    budget_min DECIMAL(15,2),
    budget_max DECIMAL(15,2),
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('new', 'contacted', 'qualified', 'negotiation', 'converted', 'lost') DEFAULT 'new',
    assigned_to BIGINT UNSIGNED COMMENT 'Assigned dealer/user',
    converted_to_client_id BIGINT UNSIGNED COMMENT 'If converted to client',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (society_id) REFERENCES societies(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (converted_to_client_id) REFERENCES clients(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_source (source),
    FULLTEXT idx_search (name, email, phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clients Table
-- Confirmed customers (buyers/sellers)
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_code VARCHAR(50) UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    whatsapp VARCHAR(20),
    cnic VARCHAR(20) UNIQUE NOT NULL,
    address TEXT,
    city VARCHAR(100),
    client_type ENUM('buyer', 'seller', 'both') DEFAULT 'buyer',
    assigned_to BIGINT UNSIGNED COMMENT 'Assigned dealer/user',
    total_purchases DECIMAL(15,2) DEFAULT 0,
    total_sales DECIMAL(15,2) DEFAULT 0,
    documents TEXT COMMENT 'CNIC copy, address proof (JSON)',
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_client_code (client_code),
    INDEX idx_client_type (client_type),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_is_active (is_active),
    FULLTEXT idx_search (name, email, phone, cnic)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Follow-ups Table
-- Track interactions with leads and clients
CREATE TABLE follow_ups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    followable_type VARCHAR(50) NOT NULL COMMENT 'Lead or Client (polymorphic)',
    followable_id BIGINT UNSIGNED NOT NULL COMMENT 'ID of lead or client',
    assigned_to BIGINT UNSIGNED NOT NULL COMMENT 'User responsible for follow-up',
    follow_up_type ENUM('call', 'meeting', 'email', 'whatsapp', 'site_visit', 'other') DEFAULT 'call',
    scheduled_at DATETIME NOT NULL,
    completed_at DATETIME,
    status ENUM('pending', 'completed', 'cancelled', 'rescheduled') DEFAULT 'pending',
    notes TEXT,
    outcome TEXT COMMENT 'Result of the follow-up',
    next_action TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_followable (followable_type, followable_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deals Table
-- Finalized transactions
CREATE TABLE deals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deal_number VARCHAR(50) UNIQUE NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    dealer_id BIGINT UNSIGNED COMMENT 'Dealer who closed the deal',
    dealable_type VARCHAR(50) NOT NULL COMMENT 'Plot or Property (polymorphic)',
    dealable_id BIGINT UNSIGNED NOT NULL COMMENT 'ID of plot or property',
    deal_type ENUM('sale', 'purchase', 'rent') DEFAULT 'sale',
    deal_amount DECIMAL(15,2) NOT NULL,
    commission_percentage DECIMAL(5,2) DEFAULT 2.00,
    commission_amount DECIMAL(15,2),
    payment_terms ENUM('cash', 'installment', 'mixed') DEFAULT 'installment',
    down_payment DECIMAL(15,2) DEFAULT 0,
    installment_months TINYINT UNSIGNED,
    status ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED COMMENT 'Manager/Admin who approved',
    approved_at DATETIME,
    completed_at DATETIME,
    deal_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (dealer_id) REFERENCES dealers(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_deal_number (deal_number),
    INDEX idx_client_id (client_id),
    INDEX idx_dealer_id (dealer_id),
    INDEX idx_dealable (dealable_type, dealable_id),
    INDEX idx_status (status),
    INDEX idx_deal_date (deal_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SECTION 5: FILE MANAGEMENT & INSTALLMENTS
-- ============================================================

-- Property Files Table
-- Property ownership files with payment tracking
CREATE TABLE property_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_number VARCHAR(50) UNIQUE NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    deal_id BIGINT UNSIGNED COMMENT 'Reference to the deal',
    fileable_type VARCHAR(50) NOT NULL COMMENT 'Plot or Property (polymorphic)',
    fileable_id BIGINT UNSIGNED NOT NULL COMMENT 'ID of plot or property',
    total_amount DECIMAL(15,2) NOT NULL,
    down_payment DECIMAL(15,2) DEFAULT 0,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    remaining_amount DECIMAL(15,2),
    installment_plan ENUM('monthly', 'quarterly', 'half_yearly', 'yearly') DEFAULT 'monthly',
    total_installments TINYINT UNSIGNED,
    paid_installments TINYINT UNSIGNED DEFAULT 0,
    status ENUM('active', 'completed', 'defaulted', 'transferred') DEFAULT 'active',
    start_date DATE NOT NULL,
    completion_date DATE,
    transferred_to_client_id BIGINT UNSIGNED COMMENT 'If file is transferred',
    transfer_date DATE,
    transfer_fee DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE SET NULL,
    FOREIGN KEY (transferred_to_client_id) REFERENCES clients(id) ON DELETE SET NULL,
    INDEX idx_file_number (file_number),
    INDEX idx_client_id (client_id),
    INDEX idx_fileable (fileable_type, fileable_id),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File Installments Table
-- Individual installment schedule for each file
CREATE TABLE file_installments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_file_id BIGINT UNSIGNED NOT NULL,
    installment_number TINYINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    status ENUM('pending', 'paid', 'partial', 'overdue', 'waived') DEFAULT 'pending',
    days_overdue INT DEFAULT 0,
    late_fee DECIMAL(10,2) DEFAULT 0 COMMENT 'Late payment fee',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_file_id) REFERENCES property_files(id) ON DELETE CASCADE,
    INDEX idx_file_id (property_file_id),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status),
    UNIQUE KEY unique_file_installment (property_file_id, installment_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File Payments Table
-- Payment records for installments
CREATE TABLE file_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(50) UNIQUE NOT NULL,
    property_file_id BIGINT UNSIGNED NOT NULL,
    file_installment_id BIGINT UNSIGNED COMMENT 'Specific installment if applicable',
    client_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'cheque', 'bank_transfer', 'online', 'card') DEFAULT 'cash',
    cheque_number VARCHAR(100),
    bank_name VARCHAR(255),
    transaction_reference VARCHAR(255),
    received_by BIGINT UNSIGNED COMMENT 'User who received payment',
    status ENUM('pending', 'completed', 'bounced', 'cancelled') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_file_id) REFERENCES property_files(id) ON DELETE CASCADE,
    FOREIGN KEY (file_installment_id) REFERENCES file_installments(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_receipt_number (receipt_number),
    INDEX idx_file_id (property_file_id),
    INDEX idx_client_id (client_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File Transfers Table
-- Track file ownership transfers
CREATE TABLE file_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_file_id BIGINT UNSIGNED NOT NULL,
    from_client_id BIGINT UNSIGNED NOT NULL,
    to_client_id BIGINT UNSIGNED NOT NULL,
    transfer_date DATE NOT NULL,
    transfer_fee DECIMAL(15,2) DEFAULT 0,
    outstanding_amount DECIMAL(15,2) COMMENT 'Amount pending at time of transfer',
    transfer_reason TEXT,
    approved_by BIGINT UNSIGNED,
    approved_at DATETIME,
    status ENUM('pending', 'approved', 'completed', 'rejected') DEFAULT 'pending',
    documents TEXT COMMENT 'Transfer documents (JSON)',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_file_id) REFERENCES property_files(id) ON DELETE CASCADE,
    FOREIGN KEY (from_client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (to_client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_file_id (property_file_id),
    INDEX idx_from_client (from_client_id),
    INDEX idx_to_client (to_client_id),
    INDEX idx_transfer_date (transfer_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SECTION 6: FINANCIAL MANAGEMENT
-- ============================================================

-- Payments Table
-- General payment records (not tied to installments)
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(50) UNIQUE NOT NULL,
    payment_type ENUM('token', 'booking', 'down_payment', 'full_payment', 'other') DEFAULT 'other',
    client_id BIGINT UNSIGNED,
    deal_id BIGINT UNSIGNED,
    payable_type VARCHAR(50) COMMENT 'Plot/Property/File (polymorphic)',
    payable_id BIGINT UNSIGNED,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'cheque', 'bank_transfer', 'online', 'card') DEFAULT 'cash',
    cheque_number VARCHAR(100),
    bank_name VARCHAR(255),
    transaction_reference VARCHAR(255),
    received_by BIGINT UNSIGNED,
    status ENUM('pending', 'completed', 'bounced', 'cancelled') DEFAULT 'completed',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_receipt_number (receipt_number),
    INDEX idx_client_id (client_id),
    INDEX idx_payable (payable_type, payable_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_type (payment_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expenses Table
-- Business expenses and operational costs
CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_number VARCHAR(50) UNIQUE,
    category ENUM('salary', 'commission', 'utilities', 'marketing', 'maintenance', 'office', 'legal', 'other') DEFAULT 'other',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    expense_date DATE NOT NULL,
    payment_method ENUM('cash', 'cheque', 'bank_transfer', 'card') DEFAULT 'cash',
    paid_to VARCHAR(255) COMMENT 'Vendor/Employee name',
    dealer_id BIGINT UNSIGNED COMMENT 'If expense is dealer commission',
    receipt_file VARCHAR(500) COMMENT 'Scanned receipt',
    approved_by BIGINT UNSIGNED,
    recorded_by BIGINT UNSIGNED,
    status ENUM('pending', 'approved', 'paid', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (dealer_id) REFERENCES dealers(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_expense_number (expense_number),
    INDEX idx_category (category),
    INDEX idx_expense_date (expense_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SECTION 7: REPORTING & ANALYTICS
-- ============================================================

-- Reports Table
-- Store generated reports for audit and reference
CREATE TABLE reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_type ENUM('sales', 'revenue', 'commission', 'inventory', 'client', 'payment', 'expense', 'custom') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    parameters TEXT COMMENT 'Report filters/parameters (JSON)',
    file_path VARCHAR(500) COMMENT 'PDF/Excel file path',
    generated_by BIGINT UNSIGNED NOT NULL,
    date_from DATE,
    date_to DATE,
    total_records INT UNSIGNED,
    total_amount DECIMAL(15,2),
    status ENUM('generating', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_report_type (report_type),
    INDEX idx_generated_by (generated_by),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RELATIONSHIPS SUMMARY
-- ============================================================

/*
═══════════════════════════════════════════════════════════════
PRIMARY RELATIONSHIPS:
═══════════════════════════════════════════════════════════════

1. USERS & ROLES (Many-to-Many)
   - users ←→ role_user ←→ roles
   - users can have multiple roles

2. ROLES & PERMISSIONS (Many-to-Many)
   - roles ←→ permission_role ←→ permissions
   - Each role has specific permissions

3. SOCIETIES HIERARCHY (One-to-Many)
   - societies → blocks → streets → plots
   - societies → blocks → properties

4. PLOTS & MAPS (One-to-Many)
   - plots → plot_maps
   - Each plot can have multiple map images

5. PROPERTIES & TYPES (Many-to-One)
   - properties → property_types
   - properties → property_images (One-to-Many)

6. CRM FLOW
   - leads → clients (conversion)
   - users (dealers) → leads (assignment)
   - users (dealers) → clients (assignment)
   - leads/clients → follow_ups (polymorphic)

7. DEALS (Polymorphic)
   - deals → plots/properties (dealable)
   - deals → clients
   - deals → dealers

8. FILES & PAYMENTS
   - property_files → plots/properties (polymorphic fileable)
   - property_files → file_installments (One-to-Many)
   - property_files → file_payments (One-to-Many)
   - file_installments → file_payments (One-to-Many)
   - property_files → file_transfers (One-to-Many)

9. FINANCIAL
   - payments → clients, deals, plots/properties (polymorphic)
   - expenses → dealers (for commissions)

10. REPORTS
    - reports → users (generated_by)

═══════════════════════════════════════════════════════════════
POLYMORPHIC RELATIONSHIPS:
═══════════════════════════════════════════════════════════════

1. follow_ups
   - followable_type: 'Lead' or 'Client'
   - followable_id: ID of lead or client

2. deals
   - dealable_type: 'Plot' or 'Property'
   - dealable_id: ID of plot or property

3. property_files
   - fileable_type: 'Plot' or 'Property'
   - fileable_id: ID of plot or property

4. payments
   - payable_type: 'Plot', 'Property', or 'PropertyFile'
   - payable_id: Corresponding ID

═══════════════════════════════════════════════════════════════
SOFT DELETE TABLES:
═══════════════════════════════════════════════════════════════

Tables with deleted_at column for soft deletes:
- users
- societies
- blocks
- streets
- plots
- properties
- leads
- clients
- dealers
- deals
- property_files
- expenses

═══════════════════════════════════════════════════════════════
*/

-- ============================================================
-- END OF SCHEMA
-- ============================================================
