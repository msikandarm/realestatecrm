# Leads Management Module - Complete Reference

## Table of Contents
1. [Overview](#overview)
2. [Database Schema](#database-schema)
3. [Model Reference](#model-reference)
4. [Controller Methods](#controller-methods)
5. [Routes](#routes)
6. [Business Logic](#business-logic)
7. [Integration with Other Modules](#integration-with-other-modules)
8. [Usage Examples](#usage-examples)
9. [Best Practices](#best-practices)

---

## Overview

The Leads Management Module is the entry point for the Real Estate CRM funnel. It tracks potential clients from initial contact through conversion, managing the entire lead lifecycle from first inquiry to successful conversion into a client.

### Key Features
- **Multi-channel lead capture** (website, Facebook, referrals, walk-ins, calls, WhatsApp, email)
- **Lead status tracking** (new → contacted → qualified → negotiation → converted/lost)
- **Priority management** (low, medium, high, urgent)
- **Interest-based categorization** (plot, house, apartment, commercial)
- **Automatic assignment** (round-robin or manual)
- **Conversion tracking** (lead → client with audit trail)
- **Follow-up integration** (polymorphic relationship)
- **Detailed reporting** (conversion rates, source analysis, agent performance)

---

## Database Schema

### Table: `leads`

```sql
CREATE TABLE `leads` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    -- Contact Information
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NOT NULL,
    `phone_secondary` VARCHAR(20) NULL,

    -- Source Tracking
    `source` ENUM('website','facebook','referral','walk-in','call','whatsapp','email','other') NOT NULL,
    `referred_by` VARCHAR(255) NULL,

    -- Interest Details
    `interest_type` ENUM('plot','house','apartment','commercial') NOT NULL,
    `society_id` BIGINT UNSIGNED NULL,
    `property_id` BIGINT UNSIGNED NULL,
    `plot_id` BIGINT UNSIGNED NULL,
    `budget_range` VARCHAR(255) NULL,
    `preferred_location` VARCHAR(255) NULL,

    -- Status Management
    `status` ENUM('new','contacted','qualified','negotiation','converted','lost') DEFAULT 'new',
    `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',

    -- Assignment
    `assigned_to` BIGINT UNSIGNED NULL,

    -- Conversion Tracking
    `converted_to_client_id` BIGINT UNSIGNED NULL,
    `converted_at` TIMESTAMP NULL,

    -- Additional Information
    `remarks` TEXT NULL,
    `created_by` BIGINT UNSIGNED NOT NULL,

    -- Timestamps
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,

    -- Indexes
    INDEX `idx_status` (`status`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_assigned_to` (`assigned_to`),
    INDEX `idx_created_at` (`created_at`),

    -- Foreign Keys
    FOREIGN KEY (`society_id`) REFERENCES `societies` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`converted_to_client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
);
```

---

## Model Reference

### Constants

```php
// Status Constants
Lead::STATUS_NEW          // 'new'
Lead::STATUS_CONTACTED    // 'contacted'
Lead::STATUS_QUALIFIED    // 'qualified'
Lead::STATUS_NEGOTIATION  // 'negotiation'
Lead::STATUS_CONVERTED    // 'converted'
Lead::STATUS_LOST         // 'lost'

// Priority Constants
Lead::PRIORITY_LOW        // 'low'
Lead::PRIORITY_MEDIUM     // 'medium'
Lead::PRIORITY_HIGH       // 'high'
Lead::PRIORITY_URGENT     // 'urgent'

// Source Constants
Lead::SOURCE_WEBSITE      // 'website'
Lead::SOURCE_FACEBOOK     // 'facebook'
Lead::SOURCE_REFERRAL     // 'referral'
Lead::SOURCE_WALKIN       // 'walk-in'
Lead::SOURCE_CALL         // 'call'
Lead::SOURCE_WHATSAPP     // 'whatsapp'
Lead::SOURCE_EMAIL        // 'email'
Lead::SOURCE_OTHER        // 'other'

// Interest Constants
Lead::INTEREST_PLOT       // 'plot'
Lead::INTEREST_HOUSE      // 'house'
Lead::INTEREST_APARTMENT  // 'apartment'
Lead::INTEREST_COMMERCIAL // 'commercial'
```

### Relationships

```php
// Direct Relationships
$lead->assignedTo;         // BelongsTo User (agent/dealer)
$lead->convertedToClient;  // BelongsTo Client (if converted)
$lead->society;            // BelongsTo Society (interest)
$lead->property;           // BelongsTo Property (interest)
$lead->plot;               // BelongsTo Plot (interest)
$lead->creator;            // BelongsTo User (who created)

// Polymorphic Relationships
$lead->followUps;          // MorphMany FollowUp (all follow-ups, latest first)
$lead->pendingFollowUps;   // MorphMany FollowUp (pending only, by date)
```

### Query Scopes

#### Status Scopes
```php
Lead::new()            // status = 'new'
Lead::contacted()      // status = 'contacted'
Lead::qualified()      // status = 'qualified'
Lead::negotiation()    // status = 'negotiation'
Lead::converted()      // status = 'converted'
Lead::lost()           // status = 'lost'
Lead::active()         // status NOT IN ('converted', 'lost')
```

#### Priority Scopes
```php
Lead::highPriority()   // priority IN ('high', 'urgent')
Lead::urgent()         // priority = 'urgent'
```

#### Filter Scopes
```php
Lead::bySource($source)           // WHERE source = $source
Lead::byInterestType($type)       // WHERE interest_type = $type
Lead::assignedTo($userId)         // WHERE assigned_to = $userId
Lead::unassigned()                // WHERE assigned_to IS NULL
```

#### Special Scopes
```php
Lead::withPendingFollowUps()      // Has pending follow-ups due
```

### Helper Methods

#### Status Checks
```php
$lead->isNew()         // Returns true if status is 'new'
$lead->isConverted()   // Returns true if status is 'converted'
$lead->isLost()        // Returns true if status is 'lost'
$lead->isActive()      // Returns true if not converted/lost
$lead->isHot()         // Returns true if high priority + (qualified or negotiation)
```

#### Attribute Accessors
```php
$lead->status_color    // Badge color: new=blue, contacted=info, qualified=success, etc.
$lead->priority_color  // Badge color: low=secondary, medium=primary, high=warning, urgent=danger
```

#### Utility Methods
```php
$lead->getDaysSinceCreation()  // Days since lead created
$lead->getFollowUpCount()      // Total follow-ups count
$lead->getLastFollowUpDate()   // Last follow-up date (Carbon)
$lead->getNextFollowUpDate()   // Next pending follow-up date (Carbon)
```

### Action Methods

```php
// Convert lead to client
$lead->convertToClient($client);
// Sets status='converted', converted_to_client_id, converted_at

// Mark lead as lost
$lead->markAsLost('Reason here');
// Sets status='lost', appends reason to remarks

// Assign to user
$lead->assignTo($user);
// Sets assigned_to

// Update status
$lead->updateStatus(Lead::STATUS_QUALIFIED);

// Update priority
$lead->updatePriority(Lead::PRIORITY_URGENT);
```

---

## Controller Methods

### LeadController

#### 1. index(Request $request)
**Purpose**: List all leads with filters and stats
**Permissions**: `leads.view`
**Features**:
- Search by name, email, phone
- Filter by status, priority, source, interest_type, assigned_to
- Date range filtering
- Active leads only (default) or show all
- Statistics dashboard
- Permission-based access (own leads vs all leads)

**Query Parameters**:
```php
?search=john           // Search term
?status=new            // Filter by status
?priority=urgent       // Filter by priority
?source=website        // Filter by source
?interest_type=plot    // Filter by interest
?assigned_to=5         // Filter by agent
?assigned_to=unassigned // Unassigned leads
?date_from=2024-01-01  // Date range start
?date_to=2024-12-31    // Date range end
?show_all=1            // Include converted/lost
?sort_by=created_at    // Sort column
?sort_dir=desc         // Sort direction
```

**Returns**: `leads.index` view with:
- `$leads` - Paginated collection
- `$stats` - Dashboard statistics
- `$dealers` - Available agents
- `$societies` - Active societies

#### 2. create()
**Purpose**: Show lead creation form
**Permissions**: `leads.create`
**Returns**: `leads.create` view with:
- `$societies` - Active societies
- `$properties` - Available properties
- `$plots` - Available plots
- `$dealers` - Active agents

#### 3. store(Request $request)
**Purpose**: Create new lead
**Permissions**: `leads.create`
**Validation**:
```php
'name' => 'required|string|max:255',
'email' => 'nullable|email|max:255',
'phone' => 'required|string|max:20',
'phone_secondary' => 'nullable|string|max:20',
'source' => 'required|in:website,facebook,referral,walk-in,call,whatsapp,email,other',
'referred_by' => 'nullable|string|max:255',
'interest_type' => 'required|in:plot,house,apartment,commercial',
'society_id' => 'nullable|exists:societies,id',
'property_id' => 'nullable|exists:properties,id',
'plot_id' => 'nullable|exists:plots,id',
'budget_range' => 'nullable|string|max:255',
'preferred_location' => 'nullable|string|max:255',
'status' => 'required|in:new,contacted,qualified,negotiation,converted,lost',
'priority' => 'required|in:low,medium,high,urgent',
'assigned_to' => 'nullable|exists:users,id',
'remarks' => 'nullable|string',
```
**Auto-Behaviors**:
- `created_by` set to authenticated user
- `assigned_to` defaults to creator (via model boot method)

#### 4. show(Lead $lead)
**Purpose**: Display lead details
**Permissions**: `leads.view` (own lead or has view_all permission)
**Eager Loads**:
- assignedTo, society, property, plot
- followUps.user, pendingFollowUps
- convertedToClient, creator

#### 5. edit(Lead $lead)
**Purpose**: Show lead edit form
**Permissions**: `leads.edit`
**Returns**: Similar dropdowns as create

#### 6. update(Request $request, Lead $lead)
**Purpose**: Update lead
**Permissions**: `leads.edit`
**Validation**: Same as store

#### 7. destroy(Lead $lead)
**Purpose**: Soft delete lead
**Permissions**: `leads.delete`

#### 8. assign(Request $request, Lead $lead)
**Purpose**: Assign lead to agent
**Permissions**: `leads.edit`
**Route**: `POST /leads/{lead}/assign`
**Validation**:
```php
'user_id' => 'required|exists:users,id'
```

#### 9. convert(Request $request, Lead $lead)
**Purpose**: Convert lead to client
**Permissions**: `leads.convert`
**Route**: `POST /leads/{lead}/convert`
**Validation**:
```php
'client_name' => 'required|string|max:255',
'client_email' => 'nullable|email|max:255',
'client_phone' => 'required|string|max:20',
'client_type' => 'required|in:buyer,seller,both',
'cnic' => 'nullable|string|max:20',
'address' => 'nullable|string',
'city' => 'nullable|string|max:255',
'province' => 'nullable|string|max:255',
```
**Process**:
1. Create new client from form data
2. Call `$lead->convertToClient($client)`
3. Redirect to client profile

#### 10. markAsLost(Request $request, Lead $lead)
**Purpose**: Mark lead as lost with reason
**Permissions**: `leads.edit`
**Route**: `POST /leads/{lead}/mark-lost`
**Validation**:
```php
'reason' => 'required|string|max:500'
```

#### 11. stats()
**Purpose**: Lead statistics and analytics
**Permissions**: `leads.view`
**Route**: `GET /leads/stats`
**Returns**:
- `by_status` - Count by status
- `by_priority` - Count by priority
- `by_source` - Count by source (top 10)
- `by_interest` - Count by interest type
- `conversion_rate` - Overall conversion percentage
- `recent` - Last 10 leads
- `hot` - Top 10 urgent active leads

---

## Routes

```php
// View leads and stats
Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
Route::get('/leads/stats', [LeadController::class, 'stats'])->name('leads.stats');
Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');

// Create lead
Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');

// Edit lead
Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');

// Additional actions
Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
Route::post('/leads/{lead}/mark-lost', [LeadController::class, 'markAsLost'])->name('leads.markAsLost');

// Convert lead
Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');

// Delete lead
Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
```

### Required Permissions
- `leads.view` - View leads list and details
- `leads.view_all` - View all leads (otherwise only assigned leads)
- `leads.create` - Create new leads
- `leads.edit` - Edit leads, assign, mark as lost
- `leads.delete` - Delete leads
- `leads.convert` - Convert leads to clients

---

## Business Logic

### Lead Lifecycle

```
┌─────────┐
│   NEW   │ ← Initial state (auto-assigned to creator)
└────┬────┘
     │
     ↓
┌─────────────┐
│  CONTACTED  │ ← First contact made
└──────┬──────┘
       │
       ↓
┌──────────────┐
│  QUALIFIED   │ ← Budget confirmed, genuine interest
└──────┬───────┘
       │
       ↓
┌───────────────┐
│ NEGOTIATION   │ ← Discussing terms, prices
└───────┬───────┘
        │
    ┌───┴───┐
    │       │
    ↓       ↓
┌───────────┐  ┌──────┐
│ CONVERTED │  │ LOST │
└───────────┘  └──────┘
```

### Priority System

- **Low**: Standard inquiry, no urgency
- **Medium**: Interested buyer, normal timeline
- **High**: Serious buyer, ready to purchase soon
- **Urgent**: Hot lead, immediate action required

### Hot Lead Detection

A lead is considered "HOT" when:
```php
$lead->isHot() // Returns true if:
// - Priority is 'high' OR 'urgent'
// - AND Status is 'qualified' OR 'negotiation'
```

### Auto-Assignment Logic

When a lead is created:
1. If `assigned_to` is provided, use it
2. Otherwise, auto-assign to the user who created the lead
3. This happens in `Lead::boot()` method

---

## Integration with Other Modules

### 1. User Module
```php
// Get all leads assigned to a user
$user->assignedLeads;

// Get leads created by user
$user->createdLeads();
```

### 2. Client Module
```php
// Get original lead for a client
$client->convertedFromLeads;

// Check if client was converted from lead
$client->convertedFromLeads()->exists();
```

### 3. Society Module
```php
// Leads interested in a society
$society->leads;

// Count leads by society
Lead::where('society_id', $society->id)->count();
```

### 4. Property Module
```php
// Leads interested in specific property
$property->leads;
```

### 5. Plot Module
```php
// Leads interested in specific plot
$plot->leads;
```

### 6. FollowUp Module (Polymorphic)
```php
// Add follow-up to lead
$lead->followUps()->create([
    'follow_up_date' => '2024-02-01',
    'follow_up_time' => '10:00',
    'type' => 'call',
    'status' => 'pending',
    'notes' => 'Discuss pricing options',
    'assigned_to' => $user->id,
    'created_by' => auth()->id()
]);

// Get pending follow-ups
$lead->pendingFollowUps;

// Check if follow-up is overdue
$lead->pendingFollowUps->filter(function($followUp) {
    return Carbon::parse($followUp->follow_up_date)->isPast();
});
```

---

## Usage Examples

### Example 1: Create Lead from Website Form

```php
$lead = Lead::create([
    'name' => 'John Smith',
    'email' => 'john@example.com',
    'phone' => '03001234567',
    'source' => Lead::SOURCE_WEBSITE,
    'interest_type' => Lead::INTEREST_PLOT,
    'society_id' => 5,
    'budget_range' => '5-7 Million',
    'preferred_location' => 'Sector A',
    'status' => Lead::STATUS_NEW,
    'priority' => Lead::PRIORITY_MEDIUM,
    'created_by' => auth()->id()
]);
// Note: assigned_to will be auto-set to auth()->id() by model boot
```

### Example 2: Query Hot Leads

```php
$hotLeads = Lead::active()
    ->urgent()
    ->with('assignedTo', 'society')
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($hotLeads as $lead) {
    echo "{$lead->name} - {$lead->status_color} - {$lead->getDaysSinceCreation()} days old\n";
}
```

### Example 3: Assign Lead to Best Agent

```php
// Find agent with least active leads
$agent = User::dealers()
    ->withCount(['assignedLeads' => function($query) {
        $query->active();
    }])
    ->orderBy('assigned_leads_count', 'asc')
    ->first();

$lead->assignTo($agent);
```

### Example 4: Convert Lead to Client

```php
DB::transaction(function() use ($lead) {
    $client = Client::create([
        'name' => $lead->name,
        'email' => $lead->email,
        'phone' => $lead->phone,
        'phone_secondary' => $lead->phone_secondary,
        'client_type' => 'buyer',
        'client_status' => 'active',
        'assigned_to' => $lead->assigned_to,
        'remarks' => $lead->remarks,
        'created_by' => auth()->id()
    ]);

    $lead->convertToClient($client);

    // Transfer follow-ups if needed
    // $lead->followUps()->update(['followable_id' => $client->id, 'followable_type' => Client::class]);
});
```

### Example 5: Lead Reporting

```php
// Conversion rate by source
$conversionBySource = Lead::selectRaw('
    source,
    COUNT(*) as total,
    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as converted,
    ROUND((SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as rate
', [Lead::STATUS_CONVERTED, Lead::STATUS_CONVERTED])
->groupBy('source')
->orderBy('rate', 'desc')
->get();

// Agent performance
$agentPerformance = User::dealers()
    ->withCount([
        'assignedLeads as total_leads',
        'assignedLeads as converted_leads' => function($query) {
            $query->converted();
        }
    ])
    ->get()
    ->map(function($agent) {
        $agent->conversion_rate = $agent->total_leads > 0
            ? round(($agent->converted_leads / $agent->total_leads) * 100, 2)
            : 0;
        return $agent;
    });

// Lost leads analysis
$lostReasons = Lead::lost()
    ->whereNotNull('remarks')
    ->pluck('remarks')
    ->map(function($remark) {
        // Extract lost reason from remarks
        preg_match('/Lost Reason: (.+?)(?:\n|$)/', $remark, $matches);
        return $matches[1] ?? 'Unknown';
    })
    ->countBy()
    ->sortDesc();
```

### Example 6: Follow-Up Management

```php
// Create follow-up for new lead
$lead = Lead::create([/* ... */]);

$lead->followUps()->create([
    'follow_up_date' => now()->addDays(1),
    'follow_up_time' => '14:00',
    'type' => 'call',
    'status' => 'pending',
    'notes' => 'Initial call to verify interest',
    'assigned_to' => $lead->assigned_to,
    'created_by' => auth()->id()
]);

// Get leads with overdue follow-ups
$overdueLeads = Lead::active()
    ->withPendingFollowUps()
    ->whereHas('pendingFollowUps', function($query) {
        $query->where('follow_up_date', '<', now()->toDateString());
    })
    ->with('assignedTo', 'pendingFollowUps')
    ->get();
```

### Example 7: Lead Scoring

```php
function calculateLeadScore(Lead $lead) {
    $score = 0;

    // Source weight
    $sourceWeights = [
        Lead::SOURCE_REFERRAL => 30,
        Lead::SOURCE_WALKIN => 25,
        Lead::SOURCE_CALL => 20,
        Lead::SOURCE_WEBSITE => 15,
        Lead::SOURCE_FACEBOOK => 10,
    ];
    $score += $sourceWeights[$lead->source] ?? 5;

    // Status weight
    $statusWeights = [
        Lead::STATUS_NEGOTIATION => 40,
        Lead::STATUS_QUALIFIED => 30,
        Lead::STATUS_CONTACTED => 20,
        Lead::STATUS_NEW => 10,
    ];
    $score += $statusWeights[$lead->status] ?? 0;

    // Priority weight
    $priorityWeights = [
        Lead::PRIORITY_URGENT => 30,
        Lead::PRIORITY_HIGH => 20,
        Lead::PRIORITY_MEDIUM => 10,
        Lead::PRIORITY_LOW => 5,
    ];
    $score += $priorityWeights[$lead->priority] ?? 0;

    // Budget specified (+10)
    if ($lead->budget_range) $score += 10;

    // Has follow-ups (+5 per follow-up, max 20)
    $score += min($lead->getFollowUpCount() * 5, 20);

    return $score; // Max: 100
}
```

---

## Best Practices

### 1. Lead Capture
- ✅ **Always specify source** for tracking effectiveness
- ✅ **Capture phone as minimum** (email optional for walk-ins)
- ✅ **Set priority based on urgency** expressed by lead
- ✅ **Record budget range** when mentioned
- ✅ **Link to specific interest** (society, property, plot) if known

### 2. Lead Assignment
- ✅ **Use auto-assignment** for quick response
- ✅ **Balance workload** across agents
- ✅ **Consider specialization** (residential vs commercial)
- ✅ **Reassign if no response** within 24 hours

### 3. Lead Follow-Up
- ✅ **Create follow-up immediately** after each interaction
- ✅ **Set realistic dates** for next contact
- ✅ **Update status** after each follow-up
- ✅ **Escalate priority** if lead shows strong interest
- ✅ **Mark as lost with reason** if lead goes cold

### 4. Lead Conversion
- ✅ **Only convert qualified leads** (not directly from new)
- ✅ **Capture complete client info** during conversion
- ✅ **Set proper client_type** (buyer, seller, both)
- ✅ **Transfer follow-ups** if maintaining history
- ✅ **Create deal immediately** after conversion

### 5. Data Quality
- ✅ **Validate phone numbers** (format, uniqueness)
- ✅ **Avoid duplicate leads** (check phone/email before create)
- ✅ **Update remarks regularly** with interaction notes
- ✅ **Keep budget_range updated** as discussed
- ✅ **Clean up old lost leads** (archive after 6 months)

### 6. Performance Tracking
- ✅ **Monitor conversion rates** by source
- ✅ **Track agent performance** (response time, conversion)
- ✅ **Analyze lost reasons** to improve process
- ✅ **Review lead aging** (days in each status)
- ✅ **Identify bottlenecks** in funnel

### 7. Security & Permissions
- ✅ **Restrict lead access** (own leads vs all leads)
- ✅ **Audit lead transfers** (track reassignments)
- ✅ **Protect sensitive data** (CNIC, financial info)
- ✅ **Log conversions** for audit trail

---

## Common Queries

### Find Leads Needing Attention
```php
// New leads not contacted in 24 hours
Lead::new()
    ->where('created_at', '<', now()->subDay())
    ->unassigned()
    ->orWhere(function($query) {
        $query->assignedTo(auth()->id())
              ->where('created_at', '<', now()->subDay());
    })
    ->get();
```

### Best Performing Sources
```php
Lead::selectRaw('source, COUNT(*) as total,
    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as converted,
    ROUND(AVG(DATEDIFF(converted_at, created_at))) as avg_days_to_convert
', [Lead::STATUS_CONVERTED])
->groupBy('source')
->havingRaw('converted > 0')
->orderBy('converted', 'desc')
->get();
```

### Agent Workload Distribution
```php
User::dealers()
    ->withCount([
        'assignedLeads as new_leads' => fn($q) => $q->new(),
        'assignedLeads as active_leads' => fn($q) => $q->active(),
        'assignedLeads as overdue_followups' => function($q) {
            $q->whereHas('pendingFollowUps', fn($fq) =>
                $fq->where('follow_up_date', '<', now())
            );
        }
    ])
    ->orderBy('active_leads', 'desc')
    ->get();
```

---

**Last Updated**: January 2024
**Module Version**: 1.0
**Laravel Version**: 11.x
