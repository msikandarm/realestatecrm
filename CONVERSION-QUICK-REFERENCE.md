# Lead-to-Client Conversion - Quick Reference

## Quick Setup

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Basic Usage
```php
// Convert lead to client
$lead->convertToClient($client);

// Check if client is from lead
$client->isConvertedFromLead();  // true/false

// Get original lead
$client->originalLead;

// Get all merged leads
$client->convertedFromLeads;
```

---

## Model Methods Cheatsheet

### Client Model

#### Query Scopes
```php
Client::convertedFromLead()->get();      // Only converted clients
Client::directClients()->get();          // Only direct clients
Client::byLeadSource('website')->get();  // By lead source
Client::active()->get();                 // Active clients
Client::buyers()->get();                 // Buyer clients
Client::sellers()->get();                // Seller clients
```

#### Helper Methods
```php
$client->isConvertedFromLead()           // bool
$client->isDirectClient()                // bool
$client->getDaysSinceConversion()        // int|null
$client->lead_source_color               // string (badge color)
$client->status_color                    // string (badge color)
$client->getTypeLabel()                  // string
$client->isBuyer()                       // bool
$client->isSeller()                      // bool
$client->isActive()                      // bool
$client->isBlacklisted()                 // bool
$client->getTotalDealsValue()            // float
$client->getActiveDealsCount()           // int
$client->getCompletedDealsCount()        // int
```

#### Relationships
```php
$client->originalLead                    // BelongsTo Lead (primary lead)
$client->convertedFromLeads              // HasMany Lead (all merged leads)
$client->assignedTo                      // BelongsTo User
$client->creator                         // BelongsTo User
$client->deals                           // HasMany Deal
$client->properties                      // HasMany Property
$client->followUps                       // MorphMany FollowUp
```

### Lead Model

#### Action Method
```php
$lead->convertToClient($client);
// Updates BOTH lead and client:
// Lead: status='converted', converted_to_client_id, converted_at
// Client: converted_from_lead_id, converted_from_lead_at, lead_source
```

---

## Controller Routes

```php
GET    /clients                              // List clients
GET    /clients/conversion-analytics         // Analytics dashboard
GET    /clients/{client}                     // Show client
GET    /clients/{client}/lead-history        // Lead history
POST   /clients                              // Create client
PUT    /clients/{client}                     // Update client
DELETE /clients/{client}                     // Delete client
```

---

## Common Queries

### Filter Clients
```php
// URL parameters
?conversion_type=converted   // Only from leads
?conversion_type=direct       // Only direct
?lead_source=website          // By source
?client_type=buyer            // By type
?client_status=active         // By status
```

### Analytics Queries
```php
// Conversion overview
Client::convertedFromLead()->count();
Client::directClients()->count();

// By source
Client::byLeadSource('website')->count();

// Recent conversions
Client::convertedFromLead()
    ->where('converted_from_lead_at', '>=', now()->subDays(30))
    ->with('originalLead')
    ->get();

// Conversion rate
$total = Lead::count();
$converted = Lead::converted()->count();
$rate = round(($converted / $total) * 100, 2);
```

---

## Conversion Flow Example

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function() use ($lead, $request) {
    // 1. Create client
    $client = Client::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'client_type' => $request->client_type,
        'client_status' => 'active',
        'assigned_to' => $lead->assigned_to,
        'created_by' => auth()->id(),
    ]);

    // 2. Convert (bidirectional tracking)
    $lead->convertToClient($client);

    // 3. Optional: Transfer follow-ups
    $lead->followUps()->update([
        'followable_type' => Client::class,
        'followable_id' => $client->id
    ]);
});
```

---

## Statistics Queries

### Dashboard Stats
```php
[
    'total_clients' => Client::count(),
    'converted' => Client::convertedFromLead()->count(),
    'direct' => Client::directClients()->count(),
    'active' => Client::active()->count(),
    'buyers' => Client::buyers()->count(),
]
```

### Conversion by Source
```php
Client::selectRaw('lead_source, COUNT(*) as count')
    ->whereNotNull('lead_source')
    ->groupBy('lead_source')
    ->orderBy('count', 'desc')
    ->get();
```

### Monthly Trends
```php
Client::convertedFromLead()
    ->selectRaw('DATE_FORMAT(converted_from_lead_at, "%Y-%m") as month, COUNT(*) as conversions')
    ->where('converted_from_lead_at', '>=', now()->subYear())
    ->groupBy('month')
    ->get();
```

### Average Conversion Time
```php
Client::selectRaw('
    lead_source,
    ROUND(AVG(DATEDIFF(converted_from_lead_at, created_at))) as avg_days
')
->whereNotNull('converted_from_lead_id')
->groupBy('lead_source')
->get();
```

---

## View Data Example

```php
// In ClientController@show
$client->load([
    'originalLead.creator',
    'originalLead.followUps',
    'convertedFromLeads',
    'deals',
    'properties'
]);

// Pass to view
$conversionInfo = $client->isConvertedFromLead() ? [
    'lead_id' => $client->converted_from_lead_id,
    'source' => $client->lead_source,
    'source_color' => $client->lead_source_color,
    'converted_at' => $client->converted_from_lead_at->format('M d, Y'),
    'days_ago' => $client->getDaysSinceConversion(),
    'original_status' => $client->originalLead->status ?? 'N/A',
    'follow_ups' => $client->originalLead->getFollowUpCount() ?? 0,
] : null;
```

---

## Blade Template Examples

### Show Conversion Badge
```blade
@if($client->isConvertedFromLead())
    <span class="badge bg-{{ $client->lead_source_color }}">
        From Lead: {{ ucfirst($client->lead_source) }}
    </span>
    <small class="text-muted">
        Converted {{ $client->getDaysSinceConversion() }} days ago
    </small>
@else
    <span class="badge bg-secondary">Direct Client</span>
@endif
```

### Lead History Link
```blade
@if($client->isConvertedFromLead())
    <a href="{{ route('clients.leadHistory', $client) }}" class="btn btn-sm btn-info">
        <i class="fas fa-history"></i> View Lead History
    </a>
@endif
```

### Conversion Stats Card
```blade
<div class="card">
    <div class="card-header">Conversion Statistics</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>{{ $stats['converted_from_leads'] }}</h5>
                <small>Converted from Leads</small>
            </div>
            <div class="col-md-6">
                <h5>{{ $stats['direct_clients'] }}</h5>
                <small>Direct Clients</small>
            </div>
        </div>
    </div>
</div>
```

---

## Testing Examples

### Feature Test
```php
public function test_lead_converts_to_client()
{
    $lead = Lead::factory()->create(['status' => 'qualified']);
    $client = Client::factory()->create();

    $lead->convertToClient($client);

    $this->assertEquals('converted', $lead->fresh()->status);
    $this->assertEquals($client->id, $lead->converted_to_client_id);
    $this->assertEquals($lead->id, $client->fresh()->converted_from_lead_id);
    $this->assertEquals($lead->source, $client->fresh()->lead_source);
}

public function test_client_can_have_multiple_leads()
{
    $client = Client::factory()->create();
    $leads = Lead::factory()->count(3)->create();

    foreach ($leads as $lead) {
        $lead->convertToClient($client);
    }

    $this->assertEquals(3, $client->convertedFromLeads()->count());
}
```

---

## Database Fields

### Clients Table
```
id                        BIGINT UNSIGNED
converted_from_lead_id    BIGINT UNSIGNED NULL    (FK leads.id)
converted_from_lead_at    TIMESTAMP NULL
lead_source              VARCHAR(255) NULL
```

### Leads Table
```
id                        BIGINT UNSIGNED
converted_to_client_id    BIGINT UNSIGNED NULL    (FK clients.id)
converted_at             TIMESTAMP NULL
source                   ENUM(...)
```

---

## Permission Requirements

```php
'clients.view'        // View clients and analytics
'clients.create'      // Create clients
'clients.edit'        // Edit clients
'clients.delete'      // Delete clients
```

---

## Best Practices

✅ **DO:**
- Use `convertToClient()` method for bidirectional tracking
- Wrap conversion in DB transaction
- Preserve lead source for analytics
- Check for duplicates before conversion
- Transfer follow-ups if maintaining history

❌ **DON'T:**
- Manually update conversion fields
- Delete leads after conversion (use soft deletes)
- Forget to validate client data
- Convert without checking lead status
- Skip permission checks

---

## Troubleshooting

**Q: Conversion not saving client fields**
A: Check fillable array includes new fields:
```php
'converted_from_lead_id',
'converted_from_lead_at',
'lead_source',
```

**Q: Analytics showing null sources**
A: Run backfill script for existing data (see full guide)

**Q: Relationship returns null**
A: Verify foreign key exists and migration ran successfully

---

**Quick Tip:** Use `Client::convertedFromLead()->with('originalLead')` for efficient queries!

**Documentation:** See [LEAD-CLIENT-CONVERSION-GUIDE.md](LEAD-CLIENT-CONVERSION-GUIDE.md) for complete reference.
