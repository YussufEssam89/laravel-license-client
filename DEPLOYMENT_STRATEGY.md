# Jo-Tech Licensing — Deployment Strategy & Contract Integration

## Multi-Layer Protection Architecture

A single protection method is never enough. Professional agencies combine multiple layers so that bypassing one doesn't compromise the system.

| Layer | Method | Strength | Bypasses |
|-------|--------|----------|----------|
| 1 | **License Server** | ★★★★★ | Cannot bypass without new server |
| 2 | **Middleware** | ★★★☆☆ | Can be removed from code |
| 3 | **Service-level `abort_if`** | ★★★★☆ | Harder to find, scattered in code |
| 4 | **Hosting Control** | ★★★★★ | Cannot bypass — you own the server |
| 5 | **Contract Clause** | ★★★★☆ | Legal recourse if all else fails |

---

## Layer 1: License Server (Central Authority)

The license server at `license.jo-tech.org` is the single source of truth.

**How it works:**
- Client project sends `domain` + `license_key` (secret) via POST
- Server validates and returns a status: `ACTIVE`, `SUSPENDED`, `EXPIRED`, or `INVALID`
- Client caches the result for 24 hours (configurable)

**Why it's strong:**
- The client never has the full database — it only knows its own status
- You can suspend/activate/expire any project instantly from the admin panel
- Even if someone clones the code, the license server will not validate their domain

---

## Layer 2: Middleware (UX Control)

The `CheckLicense` middleware blocks all HTTP requests when the license is inactive.

**Limitations:** A developer can remove middleware from the kernel. That's why we need Layer 3.

---

## Layer 3: Service-Level Enforcement (Deep Protection)

Scatter `enforce()` calls throughout the codebase:

```php
// In AppServiceProvider::boot()
if (! app()->runningInConsole()) {
    app(\JoTech\License\LicenseManager::class)->enforce();
}

// In PaymentService
app(\JoTech\License\LicenseManager::class)->enforce();

// In ReportService
app(\JoTech\License\LicenseManager::class)->enforce();

// In ExportService
app(\JoTech\License\LicenseManager::class)->enforce();

// In critical Queue Jobs
app(\JoTech\License\LicenseManager::class)->enforce();
```

**Why it's strong:** Removing middleware won't help — enforcement is embedded in business logic. Finding and removing all instances requires deep code knowledge.

---

## Layer 4: Hosting Control (Strongest Leverage)

If you host client projects on your own servers (or manage their hosting):

### DNS Control
- You manage the domain's DNS records
- Suspending = changing DNS to point to a suspension page

### Server Access
- SSH access to the server
- You can take the application offline directly

### Database Access
- You can restrict database credentials
- You can revoke database access entirely

**Why it's strong:** No amount of code changes can overcome losing server or DNS access.

---

## Layer 5: Contract Protection (Legal)

### Recommended Contract Clauses

Include these in your client agreement:

#### 1. License Grant
```
The Developer grants the Client a non-exclusive, non-transferable license  
to use the Software on the specified domain(s) for the duration of the  
agreement. This license does not transfer ownership of the source code.
```

#### 2. License Verification
```
The Software includes a license verification system that periodically  
validates the license status. The Client agrees not to remove, disable,  
or circumvent this verification system. Attempting to do so constitutes  
a material breach of this agreement.
```

#### 3. Suspension Rights
```
The Developer reserves the right to suspend the Software license in the  
event of:
  a) Non-payment of agreed fees within [30] days of the invoice date
  b) Unauthorized modification or redistribution of the Software
  c) Use of the Software on unauthorized domains
  d) Material breach of this agreement
```

#### 4. Source Code Escrow (Optional)
```
Upon full and final payment, the Developer will [provide/escrow] the  
source code. Until that time, the source code remains the intellectual  
property of the Developer.
```

#### 5. Termination
```
Upon termination of this agreement:
  a) The Client's license is immediately revoked
  b) The Client must cease all use of the Software
  c) The Developer may remotely deactivate the Software
```

---

## Recommended Deployment Workflow

### For New Client Projects

1. **Create license** in the admin panel at `license.jo-tech.org/admin`
2. **Note the `license_key`** (UUID) — this is the client's secret
3. **Install the package** in the client project:
   ```bash
   composer require jotech/license-client
   php artisan vendor:publish --tag=license-config
   ```
4. **Configure `.env`**:
   ```env
   LICENSE_SECRET=<the-license-key-uuid>
   LICENSE_SERVER_URL=https://license.jo-tech.org/api/verify-license
   ```
5. **Add service-level enforcement** in `AppServiceProvider` and critical services
6. **Deploy** and verify the license check works

### For Suspending a Client

1. Go to `license.jo-tech.org/admin` → Licenses
2. Find the client → Click **Suspend**
3. Within 24 hours (or immediately if you clear their cache), the client app shows the suspension page

### For Extending a License

1. Go to `license.jo-tech.org/admin` → Licenses
2. Find the client → Click **Extend 30 Days**
3. The expiry date is pushed forward by 30 days

---

## Security Considerations

- **Per-license secrets**: Each license has its own UUID key. Compromising one doesn't affect others.
- **Timing-safe comparison**: The server uses `hash_equals()` to prevent timing attacks on the secret.
- **HTTPS only**: Always use HTTPS for the license server to prevent MITM attacks.
- **Cache poisoning**: Cache duration is a trade-off. Shorter = tighter control, longer = better offline tolerance.
- **Obfuscation**: Consider using a PHP obfuscation tool on the `LicenseManager` class before delivering client projects, making it harder to identify and remove.

---

## Future Enhancements

- **Client dashboard**: Let clients view their license status, expiry date, and renewal options
- **Invoice linking**: Connect licenses to payment/invoice records
- **Expiry automation**: Auto-send reminder emails before expiration
- **Usage analytics**: Track how often each client's app calls the verification endpoint
- **Multi-domain per license**: Allow a single license to cover multiple domains (staging, production)
