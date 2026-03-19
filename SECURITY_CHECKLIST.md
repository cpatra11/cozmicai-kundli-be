# Be-1 API Security Hardening Checklist

## Authentication & Authorization
- [ ] Every API endpoint requires authentication (API key, JWT, or similar)
- [ ] Implement rate limiting per client/API key
- [ ] Never allow anonymous access to calculate endpoints
- [ ] Use JWT with short expiration times (e.g., 1 hour)
- [ ] Validate token signature and expiration on every request

### Implementation (Choose One):

**Option A: API Key Authentication** (simplest)
```php
// In controller
$apiKey = $request->headers->get('X-API-Key');
if (!$this->validateApiKey($apiKey)) {
    return $this->json(['error' => 'Unauthorized'], 401);
}
```

**Option B: JWT Bearer Token** (recommended for services)
```php
// Use LexikJWTAuthenticationBundle (already in Symfony)
// Add JwtTokenAuthenticator to firewall
```

**Option C: mTLS (service-to-service)**
```yaml
# Use certificates for internal RAG -> Be-1 calls
```

---

## Input Validation
- [ ] Validate ALL query parameters and request body fields
- [ ] Enforce type checking (lat/lon are floats, year is int, etc.)
- [ ] Enforce range checking (lat: -90 to 90, lon: -180 to 180, year: valid range)
- [ ] Reject oversized requests (max payload < 10KB)
- [ ] Sanitize/escape any user input before passing to shell commands

### Example Validation (Add to Controller):
```php
// Before calling calculation engine
if (!is_float($latitude) || $latitude < -90 || $latitude > 90) {
    return $this->json(['error' => 'Invalid latitude'], 400);
}
if (!is_float($longitude) || $longitude < -180 || $longitude > 180) {
    return $this->json(['error' => 'Invalid longitude'], 400);
}
if (!is_int($year) || $year < 1900 || $year > 2100) {
    return $this->json(['error' => 'Invalid year'], 400);
}
// ... etc for time_zone, varga, infolevel
```

---

## Network & Transport Security
- [ ] Always enforce HTTPS in production (TLS 1.2+)
- [ ] Use security headers:
  - [ ] `X-Frame-Options: SAMEORIGIN` (prevent clickjacking)
  - [ ] `X-Content-Type-Options: nosniff` (prevent MIME-type sniffing)
  - [ ] `X-XSS-Protection: 1; mode=block` (prevent XSS)
  - [ ] `Content-Security-Policy` (restrict script/style sources)
  - [ ] `Strict-Transport-Security: max-age=31536000` (force HTTPS)
- [ ] Set `Cookie` attributes: `Secure`, `HttpOnly`, `SameSite=Strict`

✅ **Already configured in `Dockerfile.prod` Nginx config**

---

## Rate Limiting & Abuse Prevention
- [ ] Implement per-IP or per-API-key rate limiting (e.g., 100 requests/minute)
- [ ] Use exponential backoff for repeated failures
- [ ] Block IPs after N failed auth attempts
- [ ] Monitor for suspicious patterns (sudden traffic spikes, repeated errors)

### Example Rate Limiting (using Redis):
```php
// In middleware or Symfony security listener
$rateLimitKey = 'api:' . $clientIp . ':' . date('YmdHi');
$count = $redis->incr($rateLimitKey);
if ($count == 1) {
    $redis->expire($rateLimitKey, 60);  // 1-minute window
}
if ($count > 100) {  // 100 req/min
    return $this->json(['error' => 'Rate limit exceeded'], 429);
}
```

---

## Data Protection & Secrets
- [ ] Never commit secrets to git (APP_SECRET, API keys, DB credentials)
- [ ] Use AWS Secrets Manager for all sensitive data
- [ ] Rotate secrets regularly (at least quarterly)
- [ ] Don't log sensitive information (API keys, user IPs, personal data)
- [ ] Encrypt data in transit (HTTPS) and at rest (EBS encryption)

✅ **Already configured in `.env.prod` (secrets loaded from AWS Secrets Manager)**

---

## Execution Safety
- [ ] Run application as non-root user (www-data)
- [ ] Use resource limits (CPU, memory, execution time)
  - [ ] PHP `max_execution_time=120` (prevent runaway scripts)
  - [ ] `memory_limit=256M` (prevent OOM)
  - [ ] ECS/Docker `memory=2048` (hard limit)
- [ ] Set request timeout in web server (120s in Nginx)
- [ ] Validate and escape any shell command arguments

### Example Shell Command Safety:
```php
// ❌ UNSAFE
$result = shell_exec("swetest -p $planets -j$jd");

// ✅ SAFE
$jd = (float)$jd;  // Validate type
$planets = 'p';  // Hardcode; don't take from user
$result = shell_exec(escapeshellcmd("swetest -p $planets -j$jd"));
```

---

## Logging & Monitoring
- [ ] Log all authentication attempts (success + failure)
- [ ] Log all API requests (but NOT sensitive data like API keys)
- [ ] Log errors with stack traces (but not in responses to users)
- [ ] Set up alerts for:
  - [ ] High error rate (> 1% 5xx responses)
  - [ ] Many failed auth attempts (potential attack)
  - [ ] Unusual request patterns (spike in traffic)

### Example Log Format:
```json
{
  "timestamp": "2025-03-19T10:30:00Z",
  "level": "INFO",
  "message": "Chart calculation completed",
  "request_id": "abc123",
  "client_id": "user_42",
  "endpoint": "/api/calculate",
  "duration_ms": 250,
  "status_code": 200
}
```

---

## Infrastructure & Access Control
- [ ] Place API in private VPC subnet (no direct internet access)
- [ ] Expose ONLY via ALB with security groups
- [ ] Use SAecurity Groups to restrict traffic:
  - [ ] Inbound: Allow only ALB → instance (port 8080)
  - [ ] Outbound: Allow only necessary (CloudWatch, Secrets Manager, external APIs)
- [ ] Enable VPC Flow Logs (for debugging network issues)
- [ ] Use AWS WAF to protect ALB

### Security Group Rules:
```
Inbound:
  - HTTP 8080 from ALB security group
Outbound:
  - HTTPS 443 to anywhere (for CloudWatch, Bedrock, etc.)
  - DNS 53 to VPC resolver
```

---

## Software Supply Chain Security
- [ ] Keep PHP + extensions updated (especially security patches)
- [ ] Run `composer audit` regularly and update vulnerable packages
- [ ] Rebuild Docker images at least monthly
- [ ] Scan Docker images for CVEs before pushing to ECR

### Check for vulnerabilities:
```bash
composer audit
docker run --rm -v /var/run/docker.sock:/var/run/docker.sock aquasec/trivy image jyotish-api:prod
```

---

## Testing & Validation
- [ ] Test auth bypass attempts (missing header, invalid token, expired token)
- [ ] Test input validation (invalid types, out-of-range values, oversized payloads)
- [ ] Test rate limiting (send 200 requests in 1 minute, verify 429 response)
- [ ] Test error handling (trigger 500 error, verify no stack trace leaked)
- [ ] Perform load testing to verify you can handle expected peak traffic

### Example Security Tests (bash/curl):
```bash
# Test: Missing API key
curl http://localhost:8080/api/calculate

# Test: Invalid latitude
curl http://localhost:8080/api/calculate?latitude=95

# Test: Rate limiting
for i in {1..200}; do curl http://localhost:8080/api/ping; done

# Test: Long-running request
curl "http://localhost:8080/api/calculate?latitude=0&longitude=0&year=2025&..." --max-time 130
```

---

## Compliance & Audit
- [ ] Document all changes to production (CI/CD logs, deployment records)
- [ ] Keep security audit logs for at least 90 days
- [ ] Perform security reviews quarterly
- [ ] Keep incident response plan (what to do if breached?)
- [ ] Ensure PII is handled according to GDPR/CCPA if applicable

---

## Deployment Verification Checklist
Before pushing to production, verify:
- [ ] All tests pass locally (security tests included)
- [ ] Dockerfile builds without warnings
- [ ] Image scans for CVEs (no critical/high severity)
- [ ] Secrets are NOT hardcoded
- [ ] Environment variables are set correctly
- [ ] Health check endpoint works
- [ ] Rate limiting is enabled
- [ ] Logging goes to CloudWatch
- [ ] Monitoring/alerts are configured
- [ ] Rollback plan is documented

---

## Emergency Response
If a security incident occurs:
1. [ ] Immediately revoke compromised credentials (API keys, tokens)
2. [ ] Review CloudWatch logs for unauthorized access
3. [ ] Check for data exfiltration (suspicious outbound traffic)
4. [ ] Isolate affected instances (remove from ALB)
5. [ ] Roll back to last known good version
6. [ ] Post-incident: conduct root cause analysis
