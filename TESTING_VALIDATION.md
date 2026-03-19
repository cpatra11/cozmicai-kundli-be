# Production Testing & Validation Guide for Be-1

## Pre-deployment Testing

### 1. Build & Image Verification
```bash
cd /Users/cpun/Desktop/dev/xv/astro/2/be1/api

# Build the production image
docker build -f Dockerfile.prod -t jyotish-api:prod .

# Check image size (should be ~200-300MB)
docker images jyotish-api:prod

# Inspect layers (verify multi-stage optimization)
docker history jyotish-api:prod
```

### 2. Local Container Testing
```bash
# Start the production container locally
docker-compose -f ../docker-compose.prod.yml up -d

# Wait for startup
sleep 5

# Test health endpoint
curl http://localhost:9394/health

# Test ping endpoint (unauthenticated)
curl http://localhost:9394/api/ping

# Test calculate endpoint with sample data
curl "http://localhost:9394/api/calculate?latitude=28.6139&longitude=77.2090&year=1990&month=12&day=25&hour=12&min=0&sec=0&time_zone=%2B05:30&dst_hour=0&dst_min=0&varga=D1&infolevel=basic"

# View logs
docker logs jyotish_api_prod
```

### 3. Load Testing (Apache Bench)
```bash
# Install ab (if not already)
brew install httpd  # macOS
# or apt-get install apache2-utils  # Linux

# Run 100 requests with 10 concurrent connections
ab -n 100 -c 10 http://localhost:9394/api/ping

# Expected output should show:
# Requests per second: ~50-500 (depends on system)
# 99% response time < 100ms
# 0 failed requests
```

### 4. Load Testing (wrk - more realistic)
```bash
# Install wrk
brew install wrk  # macOS
# or download from https://github.com/wg/wrk

# Run 4 threads, 100 connections for 30 seconds
wrk -t4 -c100 -d30s http://localhost:9394/api/ping

# Or test the calculate endpoint (heavier workload)
cat > calculate-load.lua << 'EOF'
request = function()
   wrk.method = "GET"
   wrk.uri = "/api/calculate?latitude=28.6139&longitude=77.2090&year=2000&month=1&day=1&hour=12&min=0&sec=0&time_zone=%2B05:30&dst_hour=0&dst_min=0&varga=D1&infolevel=basic"
   return wrk.format()
end
EOF

wrk -t4 -c50 -d60s -s calculate-load.lua http://localhost:9394
```

### 5. Security Testing
```bash
# Test 1: Missing health endpoint (should return 200)
curl -I http://localhost:9394/health
# Expected: HTTP/1.1 200 OK

# Test 2: Security headers
curl -I http://localhost:9394/api/ping | grep -E "X-Frame|X-Content|CSP"
# Expected: Should see security headers

# Test 3: Hidden files (should return 404)
curl -I http://localhost:9394/.git/config
# Expected: HTTP/1.1 404 Not Found

# Test 4: Invalid input
curl "http://localhost:9394/api/calculate?latitude=95&longitude=200"
# Expected: HTTP/1.1 400 Bad Request (or your validation error response)

# Test 5: Large payload (should reject if > limit)
curl -X POST http://localhost:9394/api/calculate -d "$(python3 -c 'print("x" * 50000)')"
# Expected: Should be rejected or return 413 Payload Too Large
```

### 6. OPcache Verification
```bash
# Connect to container and check OPcache stats
docker exec jyotish_api_prod php -r "
if (extension_loaded('Zend OPcache')) {
    echo 'OPcache version: ' . phpversion('Zend OPcache') . PHP_EOL;
    \$stats = opcache_get_status();
    echo 'OPcache enabled: ' . (\$stats ? 'YES' : 'NO') . PHP_EOL;
    echo 'Memory used: ' . round(\$stats['memory_usage']['used_memory'] / 1024 / 1024, 2) . ' MB' . PHP_EOL;
    echo 'Cache hit ratio: ' . round(\$stats['opcache_statistics']['hits'] / (\$stats['opcache_statistics']['hits'] + \$stats['opcache_statistics']['misses']) * 100, 2) . '%' . PHP_EOL;
} else {
    echo 'OPcache not loaded!' . PHP_EOL;
}
"
```

### 7. PHP-FPM Status
```bash
# Connect and check PHP-FPM settings
docker exec jyotish_api_prod php -r "
echo 'PHP Version: ' . phpversion() . PHP_EOL;
echo 'Memory limit: ' . ini_get('memory_limit') . PHP_EOL;
echo 'Max execution time: ' . ini_get('max_execution_time') . 's' . PHP_EOL;
echo 'Upload max size: ' . ini_get('upload_max_filesize') . PHP_EOL;
"
```

### 8. Nginx Configuration Check
```bash
# Verify Nginx config is valid
docker exec jyotish_api_prod nginx -t

# Check listening ports
docker exec jyotish_api_prod netstat -tlnp | grep nginx
```

---

## Post-deployment Validation (on AWS)

### 1. Health Check
```bash
# Get the ALB DNS (replace with your actual ALB DNS)
ALB_DNS="jyotish-alb-1234567890.us-east-1.elb.amazonaws.com"

# Test health endpoint
curl http://$ALB_DNS/health
# Expected: healthy (or "ok" depending on your implementation)

# Test API endpoint
curl "http://$ALB_DNS/api/ping"
# Expected: {"pong": "success"}
```

### 2. CloudWatch Logs
```bash
# View real-time logs from all instances
aws logs tail /ecs/jyotish-api-prod --follow --region us-east-1

# Filter for errors
aws logs filter-log-events \
  --log-group-name /ecs/jyotish-api-prod \
  --filter-pattern "ERROR" \
  --region us-east-1
```

### 3. CloudWatch Metrics
```bash
# Check CPU utilization (should be low at start)
aws cloudwatch get-metric-statistics \
  --namespace AWS/ECS \
  --metric-name CPUUtilization \
  --dimensions Name=ServiceName,Value=jyotish-api-service Name=ClusterName,Value=jyotish-prod-cluster \
  --start-time $(date -u -d '1 hour ago' +%Y-%m-%dT%H:%M:%S) \
  --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
  --period 300 \
  --statistics Average,Maximum \
  --region us-east-1

# Check ALB target health
aws elbv2 describe-target-health \
  --target-group-arn arn:aws:elasticloadbalancing:us-east-1:ACCOUNT:targetgroup/jyotish-targets/ABC123 \
  --region us-east-1
```

### 4. Load Test Against Production (CAREFUL!)
```bash
# Small test first (1 minute, low load)
wrk -t2 -c10 -d60s https://$ALB_DNS/api/ping

# Monitor while running:
watch -n 5 'aws cloudwatch get-metric-statistics \
  --namespace AWS/ECS \
  --metric-name CPUUtilization \
  --dimensions Name=ServiceName,Value=jyotish-api-service Name=ClusterName,Value=jyotish-prod-cluster \
  --start-time $(date -u -d "5 minutes ago" +%Y-%m-%dT%H:%M:%S) \
  --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
  --period 60 \
  --statistics Average \
  --region us-east-1'
```

### 5. Response Time Analysis
```bash
# Check p50, p90, p99 latencies
aws cloudwatch get-metric-statistics \
  --namespace AWS/ApplicationELB \
  --metric-name TargetResponseTime \
  --dimensions Name=LoadBalancer,Value=app/jyotish-alb/ABC123 \
  --start-time $(date -u -d '1 hour ago' +%Y-%m-%dT%H:%M:%S) \
  --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
  --period 60 \
  --statistics Average,Maximum \
  --region us-east-1
```

---

## Rollback Procedure
If something goes wrong after deployment:

```bash
# Get the previous task definition
aws ecs list-task-definitions --family-prefix jyotish-api-prod --region us-east-1 | grep revision

# Update service to use previous task definition
aws ecs update-service \
  --cluster jyotish-prod-cluster \
  --service jyotish-api-service \
  --task-definition jyotish-api-prod:NNN \
  --region us-east-1

# Wait for tasks to be replaced
aws ecs wait services-stable \
  --cluster jyotish-prod-cluster \
  --services jyotish-api-service \
  --region us-east-1

# Verify rollback succeeded
aws ecs describe-services \
  --cluster jyotish-prod-cluster \
  --services jyotish-api-service \
  --region us-east-1 | jq '.services[0].taskDefinition'
```

---

## Success Criteria

✅ **Deployment is successful if:**
- [ ] Health check returns 200
- [ ] API ping endpoint works
- [ ] Calculate endpoint returns valid JSON (no 500 errors)
- [ ] Load test shows > 50 requests/second (or based on your baseline)
- [ ] p99 latency < 2 seconds
- [ ] Error rate < 0.1%
- [ ] CloudWatch shows logs with no persistent errors
- [ ] Auto-scaling works (instances scale up under load)
- [ ] Security headers are present
- [ ] CPU utilization remains < 80% under expected load

---

## Troubleshooting Common Issues

### Issue: 502 Bad Gateway
**Cause**: PHP-FPM is not responding  
**Fix**:
```bash
# Check if PHP-FPM is running
docker exec jyotish_api_prod ps aux | grep fpm

# Restart PHP-FPM
docker exec jyotish_api_prod service php8.1-fpm restart

# Check for errors
docker logs jyotish_api_prod | tail -50
```

### Issue: Slow Response Times
**Cause**: OPcache not working or too many processes  
**Fix**:
```bash
# Check OPcache hit ratio (should be > 90%)
docker exec jyotish_api_prod php -r "echo opcache_get_status()['opcache_statistics']['hits'] / (opcache_get_status()['opcache_statistics']['hits'] + opcache_get_status()['opcache_statistics']['misses']) * 100;"

# Check PHP-FPM process count
docker exec jyotish_api_prod ps aux | grep fpm | wc -l

# Increase pm.max_children if needed (in Dockerfile.php-fpm config section)
```

### Issue: High Memory Usage
**Cause**: PHP-FPM process count too high or memory leak  
**Fix**:
```bash
# Check memory per process
docker exec jyotish_api_prod ps aux | grep php

# Restart container to flush memory
docker restart jyotish_api_prod

# Lower pm.max_children in Dockerfile and rebuild
```

---

## Next Steps
1. **Document your baseline metrics** (before adding production traffic)
2. **Set up automated alerts** (errors, latency, CPU/memory)
3. **Plan runbook for common issues** (who responds, how to escalate)
4. **Schedule regular security audits** (monthly/quarterly)
