# Leads Module - Quick Reference

## Quick Access

### Constants
```php
// Status
Lead::STATUS_NEW          Lead::STATUS_CONTACTED
Lead::STATUS_QUALIFIED    Lead::STATUS_NEGOTIATION
Lead::STATUS_CONVERTED    Lead::STATUS_LOST

// Priority
Lead::PRIORITY_LOW        Lead::PRIORITY_MEDIUM
Lead::PRIORITY_HIGH       Lead::PRIORITY_URGENT

// Source
Lead::SOURCE_WEBSITE      Lead::SOURCE_FACEBOOK
Lead::SOURCE_REFERRAL     Lead::SOURCE_WALKIN
Lead::SOURCE_CALL         Lead::SOURCE_WHATSAPP
Lead::SOURCE_EMAIL        Lead::SOURCE_OTHER

// Interest
Lead::INTEREST_PLOT       Lead::INTEREST_HOUSE
Lead::INTEREST_APARTMENT  Lead::INTEREST_COMMERCIAL
```

---

## Scopes Cheatsheet

### Status Scopes
```php
Lead::new()                // WHERE status = 'new'
Lead::contacted()          // WHERE status = 'contacted'
Lead::qualified()          // WHERE status = 'qualified'
Lead::negotiation()        // WHERE status = 'negotiation'
Lead::converted()          // WHERE status = 'converted'
Lead::lost()               // WHERE status = 'lost'
Lead::active()             // WHERE status NOT IN ('converted', 'lost')
```

### Priority Scopes
```php
Lead::highPriority()       // WHERE priority IN ('high', 'urgent')
Lead::urgent()             // WHERE priority = 'urgent'
```

### Filter Scopes
```php
Lead::bySource('website')         // WHERE source = 'website'
Lead::byInterestType('plot')      // WHERE interest_type = 'plot'
Lead::assignedTo($userId)         // WHERE assigned_to = $userId
Lead::unassigned()                // WHERE assigned_to IS NULL
Lead::withPendingFollowUps()      // Has pending follow-ups
```

### Scope Combinations
```php
// Hot leads assigned to me
Lead::active()
    ->urgent()
    ->assignedTo(auth()->id())
    ->with('society', 'followUps')
    ->get();

// New unassigned leads
Lead::new()
    ->unassigned()
    ->orderBy('created_at', 'desc')
    ->get();

// Qualified leads for plots
Lead::qualified()
    ->byInterestType(Lead::INTEREST_PLOT)
    ->with('society', 'plot')
    ->get();
```

---

## Helper Methods Cheatsheet

### Status Checks
```php
$lead->isNew()             // bool
$lead->isConverted()       // bool
$lead->isLost()            // bool
$lead->isActive()          // bool (not converted/lost)
$lead->isHot()             // bool (high priority + qualified/negotiation)
```

### Attribute Accessors
```php
$lead->status_color        // Badge color for UI
$lead->priority_color      // Badge color for UI
```

### Utility Methods
```php
$lead->getDaysSinceCreation()    // int (days old)
$lead->getFollowUpCount()        // int (total follow-ups)
$lead->getLastFollowUpDate()     // Carbon|null
$lead->getNextFollowUpDate()     // Carbon|null (next pending)
```

---

## Action Methods Cheatsheet

### Convert Lead to Client
```php
$client = Client::create([/* ... */]);
$lead->convertToClient($client);
// Sets: status='converted', converted_to_client_id, converted_at
```

### Mark as Lost
```php
$lead->markAsLost('Price too high');
// Sets: status='lost', appends reason to remarks
```

### Assign to User
```php
$lead->assignTo($user);
// Sets: assigned_to = $user->id
```

### Update Status
```php
$lead->updateStatus(Lead::STATUS_QUALIFIED);
// Sets: status = 'qualified'
```

### Update Priority
```php
$lead->updatePriority(Lead::PRIORITY_URGENT);
// Sets: priority = 'urgent'
```

---

## Relationships Cheatsheet

```php
$lead->assignedTo          // BelongsTo User (agent)
$lead->convertedToClient   // BelongsTo Client (if converted)
$lead->society             // BelongsTo Society (interest)
$lead->property            // BelongsTo Property (interest)
$lead->plot                // BelongsTo Plot (interest)
$lead->creator             // BelongsTo User (who created)
$lead->followUps           // MorphMany FollowUp (all, latest first)
$lead->pendingFollowUps    // MorphMany FollowUp (pending only)
```

---

## Controller Routes Cheatsheet

```php
GET    /leads                           leads.index         List leads
GET    /leads/stats                     leads.stats         Statistics
GET    /leads/create                    leads.create        Create form
POST   /leads                           leads.store         Store lead
GET    /leads/{lead}                    leads.show          Show lead
GET    /leads/{lead}/edit               leads.edit          Edit form
PUT    /leads/{lead}                    leads.update        Update lead
DELETE /leads/{lead}                    leads.destroy       Delete lead
POST   /leads/{lead}/assign             leads.assign        Assign to agent
POST   /leads/{lead}/convert            leads.convert       Convert to client
POST   /leads/{lead}/mark-lost          leads.markAsLost    Mark as lost
```

---

## Common Queries

### My Active Leads
```php
Lead::active()
    ->assignedTo(auth()->id())
    ->orderBy('priority', 'desc')
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

### Hot Leads Needing Attention
```php
Lead::urgent()
    ->active()
    ->withPendingFollowUps()
    ->with('assignedTo', 'pendingFollowUps')
    ->get();
```

### Leads by Status Count
```php
[
    'new' => Lead::new()->count(),
    'contacted' => Lead::contacted()->count(),
    'qualified' => Lead::qualified()->count(),
    'negotiation' => Lead::negotiation()->count(),
    'converted' => Lead::converted()->count(),
    'lost' => Lead::lost()->count(),
]
```

### Conversion Rate
```php
$total = Lead::count();
$converted = Lead::converted()->count();
$rate = $total > 0 ? round(($converted / $total) * 100, 2) : 0;
```

### Leads with Overdue Follow-Ups
```php
Lead::active()
    ->whereHas('pendingFollowUps', function($query) {
        $query->where('follow_up_date', '<', now()->toDateString());
    })
    ->with('assignedTo', 'pendingFollowUps')
    ->get();
```

### Agent Performance
```php
User::dealers()
    ->withCount([
        'assignedLeads as total',
        'assignedLeads as converted' => fn($q) => $q->converted(),
        'assignedLeads as active' => fn($q) => $q->active(),
    ])
    ->get()
    ->map(function($user) {
        $user->conversion_rate = $user->total > 0
            ? round(($user->converted / $user->total) * 100, 2)
            : 0;
        return $user;
    });
```

### Leads by Source
```php
Lead::selectRaw('source, COUNT(*) as count')
    ->groupBy('source')
    ->orderBy('count', 'desc')
    ->pluck('count', 'source');
```

### Recent Lead Activity
```php
Lead::with('assignedTo', 'society')
    ->orderBy('updated_at', 'desc')
    ->take(10)
    ->get();
```

---

## Validation Rules Quick Reference

### Store/Update Lead
```php
[
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
]
```

### Assign Lead
```php
['user_id' => 'required|exists:users,id']
```

### Mark as Lost
```php
['reason' => 'required|string|max:500']
```

### Convert to Client
```php
[
    'client_name' => 'required|string|max:255',
    'client_email' => 'nullable|email|max:255',
    'client_phone' => 'required|string|max:20',
    'client_type' => 'required|in:buyer,seller,both',
    'cnic' => 'nullable|string|max:20',
    'address' => 'nullable|string',
    'city' => 'nullable|string|max:255',
    'province' => 'nullable|string|max:255',
]
```

---

## Permissions Quick Reference

```php
leads.view          // View leads (own or all based on view_all)
leads.view_all      // View all leads (not just assigned)
leads.create        // Create new leads
leads.edit          // Edit leads, assign, mark lost
leads.delete        // Delete leads
leads.convert       // Convert leads to clients
```

---

## Index Filters

```php
// URL query parameters for leads.index
?search=john               // Search by name, email, phone
?status=qualified          // Filter by status
?priority=urgent           // Filter by priority
?source=website            // Filter by source
?interest_type=plot        // Filter by interest type
?assigned_to=5             // Filter by assigned agent
?assigned_to=unassigned    // Show unassigned leads
?date_from=2024-01-01      // Created after date
?date_to=2024-12-31        // Created before date
?show_all=1                // Include converted/lost
?sort_by=priority          // Sort column
?sort_dir=desc             // Sort direction
```

---

## UI Badge Colors

### Status Colors
```php
'new' => 'blue'
'contacted' => 'info'
'qualified' => 'success'
'negotiation' => 'warning'
'converted' => 'success'
'lost' => 'danger'
```

### Priority Colors
```php
'low' => 'secondary'
'medium' => 'primary'
'high' => 'warning'
'urgent' => 'danger'
```

---

## Example: Complete Lead Flow

```php
// 1. Create lead from website
$lead = Lead::create([
    'name' => 'John Smith',
    'email' => 'john@example.com',
    'phone' => '03001234567',
    'source' => Lead::SOURCE_WEBSITE,
    'interest_type' => Lead::INTEREST_PLOT,
    'society_id' => 5,
    'budget_range' => '5-7 Million',
    'status' => Lead::STATUS_NEW,
    'priority' => Lead::PRIORITY_MEDIUM,
    'created_by' => auth()->id()
]);
// Auto-assigned to creator

// 2. Add follow-up
$lead->followUps()->create([
    'follow_up_date' => now()->addDay(),
    'follow_up_time' => '14:00',
    'type' => 'call',
    'status' => 'pending',
    'notes' => 'Initial call to discuss requirements',
    'assigned_to' => $lead->assigned_to,
    'created_by' => auth()->id()
]);

// 3. Update after contact
$lead->updateStatus(Lead::STATUS_CONTACTED);
$lead->updatePriority(Lead::PRIORITY_HIGH);

// 4. Qualify lead
$lead->updateStatus(Lead::STATUS_QUALIFIED);

// 5. Negotiation
$lead->updateStatus(Lead::STATUS_NEGOTIATION);
$lead->updatePriority(Lead::PRIORITY_URGENT);

// 6. Convert to client
$client = Client::create([
    'name' => $lead->name,
    'email' => $lead->email,
    'phone' => $lead->phone,
    'phone_secondary' => $lead->phone_secondary,
    'client_type' => 'buyer',
    'client_status' => 'active',
    'assigned_to' => $lead->assigned_to,
    'created_by' => auth()->id()
]);

$lead->convertToClient($client);

// 7. Create deal
Deal::create([
    'client_id' => $client->id,
    'plot_id' => $lead->plot_id,
    'deal_type' => 'sale',
    'deal_status' => 'in_progress',
    'created_by' => auth()->id()
]);
```

---

## Testing Checklist

- [ ] Create lead with all fields
- [ ] Create lead with minimum fields (name, phone, source, interest_type)
- [ ] Search leads by name, email, phone
- [ ] Filter by status, priority, source, interest
- [ ] Assign lead to agent
- [ ] Update lead status/priority
- [ ] Add follow-up to lead
- [ ] Convert lead to client
- [ ] Mark lead as lost with reason
- [ ] View lead statistics
- [ ] Check permission restrictions
- [ ] Test overdue follow-up detection
- [ ] Verify auto-assignment on create
- [ ] Test conversion rate calculation
- [ ] Check hot lead detection

---

**Quick Tip**: Use `Lead::active()->urgent()->get()` to find leads needing immediate attention!
