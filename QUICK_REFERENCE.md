# Be-1 Production Deployment Quick Reference

## 📋 Pre-Deployment Checklist (Complete Before Any Deployment)

### Week 1: Preparation
- [ ] Review all documentation:
  - [ ] DEPLOYMENT_SUMMARY.md (overview)
  - [ ] PRODUCTION_DEPLOYMENT.md (detailed steps)
  - [ ] SECURITY_CHECKLIST.md (security requirements)
  - [ ] TESTING_VALIDATION.md (testing procedures)

### Week 1-2: Local Testing
- [ ] Test production Docker build
- [ ] Run load tests locally (baseline)
- [ ] Verify security headers
- [ ] Test with docker-compose.prod.yml
- [ ] Check OPcache is working
- [ ] Review logs for any issues

### Week 2: AWS Setup
- [ ] Create/verify AWS account and region
- [ ] Create IAM user in "jyotish-deployers" group
- [ ] Verify IAM permissions are correct
- [ ] Create ECR repository: `jyotish-api`
- [ ] Create CloudWatch log group: `/ecs/jyotish-api-prod`
- [ ] Create secrets in Secrets Manager:
  - [ ] `jyotish/prod/app-secret` (APP_SECRET)

### Week 2-3: Authentication & Secrets
- [ ] Implement API key / JWT validation (if not already done)
- [ ] Test authentication locally
- [ ] Store API keys in Secrets Manager (not code)
- [ ] Verify IAM task role permissions
- [ ] Test Secrets Manager access from ECS task role

### Week 3: Infrastructure
- [ ] Create VPC (or use default)
- [ ] Create 2+ private subnets (for AZ redundancy)
- [ ] Create security group for ALB
- [ ] Create Application Load Balancer (ALB)
- [ ] Create target group with health check (`/health`)
- [ ] Verify ALB can reach test instances

### Week 3-4: ECS Cluster
- [ ] Create ECS cluster: `jyotish-prod-cluster`
- [ ] If using EC2 launch type:
  - [ ] Create launch configuration with proper instance type
  - [ ] Create Auto Scaling Group (2-10 instances)
- [ ] Create CloudWatch log group for ECS

### Week 4: Deployment Readiness
- [ ] Review all configuration files
- [ ] Finalize IAM policies (least privilege)
- [ ] Set up CloudWatch alarms (error rate, CPU, latency)
- [ ] Document runbook for on-call
- [ ] Have rollback plan ready
- [ ] Notify team of deployment plan

---

## 🚀 Deployment Steps (Copy & Paste)

### Step 1: Build & Push Image (15 min)
```bash
cd /Users/cpun/Desktop/dev/xv/astro/2/be1

# Set AWS variables
export AWS_ACCOUNT=$(aws sts get-caller-identity --query Account --output text)
export AWS_REGION=us-east-1
export ECR_REPO=jyotish-api

# Build production image
docker build -f api/Dockerfile.prod -t jyotish-api:prod ./api

# Tag for ECR
docker tag jyotish-api:prod $AWS_ACCOUNT.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:prod
docker tag jyotish-api:prod $AWS_ACCOUNT.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:latest

# Login to ECR
aws ecr get-login-password --region $AWS_REGION | \
  docker login --username AWS --password-stdin $AWS_ACCOUNT.dkr.ecr.$AWS_REGION.amazonaws.com

# Push to ECR
docker push $AWS_ACCOUNT.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:prod
docker push $AWS_ACCOUNT.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:latest

echo "✅ Image pushed to ECR: $AWS_ACCOUNT.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:prod"
```

### Step 2: Create ECS Task Definition (10 min)
```bash
# Create task definition from template (see PRODUCTION_DEPLOYMENT.md)
# Replace placeholders:
# - ACCOUNT_ID with your AWS account
# - jyotish/prod/app-secret with your secret ARN

aws ecs register-task-definition \
  --family jyotish-api-prod \
  --network-mode awsvpc \
  --requires-compatibilities ["EC2"] \
  --cpu "1024" \
  --memory "2048" \
  --container-definitions file://task-definition.json \
  --region us-east-1

echo "✅ Task definition created: jyotish-api-prod"
```

### Step 3: Create ECS Service (10 min)
```bash
aws ecs create-service \
  --cluster jyotish-prod-cluster \
  --service-name jyotish-api-service \
  --task-definition jyotish-api-prod:1 \
  --desired-count 2 \
  --launch-type EC2 \
  --load-balancers targetGroupArn=arn:aws:...,containerName=jyotish-api,containerPort=8080 \
  --region us-east-1

echo "✅ ECS service created: jyotish-api-service"

# Wait for service to stabilize
aws ecs wait services-stable \
  --cluster jyotish-prod-cluster \
  --services jyotish-api-service \
  --region us-east-1

echo "✅ Service is stable and running"
```

### Step 4: Verify Deployment (10 min)
```bash
# Get ALB DNS
ALB_DNS=$(aws elbv2 describe-load-balancers \
  --query 'LoadBalancers[?LoadBalancerName==`jyotish-alb`].DNSName' \
  --output text \
  --region us-east-1)

echo "ALB DNS: $ALB_DNS"

# Test health
curl http://$ALB_DNS/health
# Expected output: "healthy" or "ok"

# Test API
curl "http://$ALB_DNS/api/ping"
# Expected output: {"pong": "success"}

# View logs
aws logs tail /ecs/jyotish-api-prod --follow --region us-east-1
```

### Step 5: Set Up Auto-Scaling (5 min)
```bash
# Register scalable target
aws application-autoscaling register-scalable-target \
  --service-namespace ecs \
  --resource-id service/jyotish-prod-cluster/jyotish-api-service \
  --scalable-dimension ecs:service:DesiredCount \
  --min-capacity 2 \
  --max-capacity 10 \
  --region us-east-1

# Create scaling policy
aws application-autoscaling put-scaling-policy \
  --policy-name jyotish-cpu-scaling \
  --service-namespace ecs \
  --resource-id service/jyotish-prod-cluster/jyotish-api-service \
  --scalable-dimension ecs:service:DesiredCount \
  --policy-type TargetTrackingScaling \
  --target-tracking-scaling-policy-configuration \
    "TargetValue=70.0,PredefinedMetricSpecification={PredefinedMetricType=ECSServiceAverageCPUUtilization},ScaleOutCooldown=60,ScaleInCooldown=300" \
  --region us-east-1

echo "✅ Auto-scaling configured"
```

### Step 6: Set Up CloudWatch Alarms (10 min)
```bash
# High error rate alarm
aws cloudwatch put-metric-alarm \
  --alarm-name jyotish-api-high-errors \
  --alarm-description "Alert if error rate > 1%" \
  --metric-name HTTPCode_Target_5XX_Count \
  --namespace AWS/ApplicationELB \
  --statistic Sum \
  --period 300 \
  --threshold 50 \
  --comparison-operator GreaterThanThreshold \
  --evaluation-periods 2 \
  --alarm-actions arn:aws:sns:us-east-1:ACCOUNT:on-call-team \
  --region us-east-1

# High CPU alarm
aws cloudwatch put-metric-alarm \
  --alarm-name jyotish-api-high-cpu \
  --alarm-description "Alert if CPU > 80% for 10 min" \
  --metric-name CPUUtilization \
  --namespace AWS/ECS \
  --dimensions Name=ServiceName,Value=jyotish-api-service Name=ClusterName,Value=jyotish-prod-cluster \
  --statistic Average \
  --period 300 \
  --threshold 80 \
  --comparison-operator GreaterThanThreshold \
  --evaluation-periods 2 \
  --alarm-actions arn:aws:sns:us-east-1:ACCOUNT:on-call-team \
  --region us-east-1

echo "✅ CloudWatch alarms configured"
```

---

## 🔍 Verification Checklist (After Deployment)

Run these checks immediately after deployment:

```bash
# 1. Health check
curl -I http://$ALB_DNS/health
# Expected: HTTP/1.1 200 OK

# 2. API endpoint
curl "http://$ALB_DNS/api/ping"
# Expected: {"pong": "success"}

# 3. Calculate endpoint
curl "http://$ALB_DNS/api/calculate?latitude=28.6139&longitude=77.2090&year=2000&month=1&day=1&hour=12&min=0&sec=0&time_zone=%2B05:30&varga=D1&infolevel=basic"
# Expected: Full chart JSON, HTTP 200

# 4. Security headers
curl -I http://$ALB_DNS/api/ping | grep -E "X-Frame|X-Content|CSP"
# Expected: Should see security headers

# 5. CloudWatch logs (should have an entry within 30s)
aws logs filter-log-events \
  --log-group-name /ecs/jyotish-api-prod \
  --filter-pattern "api/ping" \
  --start-time $(date -d "1 minute ago" +%s)000 \
  --region us-east-1 | jq '.events | length'
# Expected: > 0

# 6. Check task count
aws ecs describe-services \
  --cluster jyotish-prod-cluster \
  --services jyotish-api-service \
  --region us-east-1 | jq '.services[0] | {desiredCount, runningCount, pendingCount}'
# Expected: desiredCount == runningCount

# 7. Run light load test
ab -n 50 -c 10 http://$ALB_DNS/api/ping
# Expected: 0 failed requests, response time < 100ms
```

---

## 🆘 Common Issues & Quick Fixes

### Issue: Tasks won't start (status: stopped)
```bash
# Check logs
docker logs $(docker ps -a -q --filter ancestor=jyotish-api:prod)

# Common causes:
# 1. APP_SECRET not found in Secrets Manager
# 2. Memory limit too low (increase to 2048)
# 3. Image doesn't exist in ECR
# 4. IAM task role missing permissions
```

### Issue: Health check failing (unhealthy)
```bash
# SSH into instance / container and test
curl http://localhost:8080/health

# Check if Nginx/PHP-FPM are running
ps aux | grep -E "nginx|php-fpm"

# Restart service
docker restart <container-id>
```

### Issue: 502 Bad Gateway
```bash
# Check PHP-FPM status in container
php -r "echo opcache_get_status() ? 'OK' : 'ERROR';"

# Check Nginx error log
tail -20 /var/log/nginx/error.log
```

### Issue: High latency (p99 > 2 sec)
```bash
# Check CPU/Memory utilization
aws cloudwatch get-metric-statistics \
  --namespace AWS/ECS \
  --metric-name CPUUtilization \
  --dimensions Name=ServiceName,Value=jyotish-api-service Name=ClusterName,Value=jyotish-prod-cluster \
  --statistics Average,Maximum \
  --period 60 \
  --start-time $(date -u -d "5 min ago" +%Y-%m-%dT%H:%M:%S) \
  --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
  --region us-east-1

# If CPU > 70%, auto-scaling should kick in (wait 1-2 min)
# If persists, increase task CPU/memory or improve code
```

---

## 📞 Rollback (If Needed)

```bash
# Get previous task definition revision
aws ecs list-task-definitions \
  --family-prefix jyotish-api-prod \
  --sort DESCENDING \
  --region us-east-1 | jq '.taskDefinitionArns | .[0:3]'

# Revert to previous version (e.g., :2 instead of :3)
aws ecs update-service \
  --cluster jyotish-prod-cluster \
  --service jyotish-api-service \
  --task-definition jyotish-api-prod:2 \
  --region us-east-1

# Wait for rollback
aws ecs wait services-stable \
  --cluster jyotish-prod-cluster \
  --services jyotish-api-service \
  --region us-east-1

echo "✅ Rolled back to previous version"
```

---

## 📊 Monitor After Deployment

Keep an eye on these metrics for 24 hours:

```bash
# Watch error rate (should be < 0.1%)
watch -n 5 'aws cloudwatch get-metric-statistics \
  --namespace AWS/ApplicationELB \
  --metric-name HTTPCode_Target_5XX_Count \
  --start-time $(date -u -d "5 min ago" +%Y-%m-%dT%H:%M:%S) \
  --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
  --period 60 \
  --statistics Sum \
  --region us-east-1 | jq ".Datapoints"'

# Watch latency (should be < 500ms p99)
watch -n 5 'aws cloudwatch get-metric-statistics \
  --namespace AWS/ApplicationELB \
  --metric-name TargetResponseTime \
  --start-time $(date -u -d "5 min ago" +%Y-%m-%dT%H:%M:%S) \
  --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
  --period 60 \
  --statistics Maximum \
  --region us-east-1 | jq ".Datapoints"'

# Check auto-scaling (should scale up if hits > 70% CPU)
watch -n 30 'aws ecs describe-services \
  --cluster jyotish-prod-cluster \
  --services jyotish-api-service \
  --region us-east-1 | jq ".services[0] | {desiredCount, runningCount, taskStatus: .deployments}"'
```

---

## 📝 Documentation & References

- **Full deployment guide**: [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md)
- **Security checklist**: [SECURITY_CHECKLIST.md](SECURITY_CHECKLIST.md)
- **Testing guide**: [TESTING_VALIDATION.md](TESTING_VALIDATION.md)
- **Deployment summary**: [DEPLOYMENT_SUMMARY.md](DEPLOYMENT_SUMMARY.md)

---

**Timeline**: Total deployment time ~1-2 hours (first time), then ~30 min for updates.

**Success criteria**: Health check returns 200, API responds in < 500ms, error rate < 0.1%, auto-scaling works.
