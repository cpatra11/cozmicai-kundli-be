# Production Deployment Guide for Be-1 Jyotish API

## Pre-deployment Checklist

### 1. Environment & Secrets
- [ ] Store `APP_SECRET` in **AWS Secrets Manager** (not in `.env`)
  ```bash
  aws secretsmanager create-secret \
    --name jyotish/prod/app-secret \
    --secret-string "$(openssl rand -hex 16)" \
    --region us-east-1
  ```
- [ ] Set `CORS_ALLOW_ORIGIN` based on your frontend domain(s)
- [ ] Configure `BEDROCK_REGION` if using Bedrock
- [ ] Set `SWETEST_PATH` in deployment environment (default: `/var/www/api/swetest/src`)

### 2. IAM & Permissions (Deployment User Group)
Required permissions for your deployment IAM group:
- `AmazonEC2ContainerRegistryFullAccess` (push Docker images to ECR)
- `AmazonECS_FullAccess` (deploy to ECS)
- `CloudWatchLogsFullAccess` (view logs)
- `SecretsManagerReadWrite` (access secrets)
- `ElasticLoadBalancingFullAccess` (manage load balancers)
- `AutoScalingFullAccess` (configure scaling)

### 3. Build Production Image
Use the multi-stage Dockerfile (`Dockerfile.prod`):

```bash
# Build locally to test
docker build -f Dockerfile.prod -t jyotish-api:prod .

# Or push directly to ECR (recommended)
AWS_ACCOUNT_ID=123456789012
AWS_REGION=us-east-1
ECR_REPO=jyotish-api

# Login to ECR
aws ecr get-login-password --region $AWS_REGION | \
  docker login --username AWS --password-stdin $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com

# Tag and push
docker tag jyotish-api:prod $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:prod
docker push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:prod
```

### 4. Image Optimization Summary
✅ **Multi-stage build**: Build stage removes ~500MB of dev/build tools  
✅ **OPcache enabled**: 256MB memory, preloading, validation disabled (for production)  
✅ **PHP-FPM tuned**: 50 max children, dynamic pool, 120s request timeout  
✅ **Nginx hardened**: Security headers, health check endpoint, timeouts  
✅ **Minimal base**: Only runtime dependencies, no git/make/build-essential  

### 5. Network & Security
- [ ] Place API in **private VPC subnet** (not public internet)
- [ ] Expose via **Application Load Balancer (ALB)** with:
  - Health check: `GET /health` (returns 200)
  - Security group: Allow only port 8080 from within VPC
- [ ] Use **WAF (AWS WAF)** on ALB to protect against:
  - SQL injection
  - XSS attacks
  - Bot traffic
  - Rate limiting rules (optional)

### 6. Auto-Scaling Configuration
For ECS Fargate or EC2-backed ECS:

```json
{
  "MinCapacity": 2,
  "MaxCapacity": 10,
  "DesiredCount": 2,
  "ScalingPolicy": {
    "TargetValue": 70.0,
    "PredefinedMetricSpecification": {
      "PredefinedMetricType": "ECSServiceAverageCPUUtilization"
    },
    "ScaleOutCooldown": 60,
    "ScaleInCooldown": 300
  }
}
```

### 7. Container Resources (per task / instance)
- **CPU**: 1024 (1 vCPU) for moderate load; scale from 512-2048 based on traffic
- **Memory**: 1024-2048 MB (depends on expected concurrent users)
- **Disk**: Minimal; app is stateless

### 8. Logging & Monitoring
- [ ] All logs go to **CloudWatch Logs** via stdout/stderr
- [ ] Set up **CloudWatch alarms** for:
  - High error rates (> 1% 5xx responses)
  - High CPU (> 80%)
  - High memory (> 80%)
  - Slow response times (p99 latency > 5s)
- [ ] Optional: Enable **X-Ray tracing** for request tracing
- [ ] Optional: Use **APM agents** (New Relic, Datadog) for deep monitoring

### 9. Database (if needed later)
If you add RDS for results caching or metrics:
- Use **AWS Secrets Manager** for DB credentials
- Enable **Performance Insights** for query analysis
- Enable **automated backups** (7+ days retention)
- Use **Read Replicas** for scaling reads

### 10. Caching Layer (Optional but Recommended)
For scaling to high request volumes:
- Deploy **Amazon ElastiCache for Redis**
  - Node type: `cache.t3.small` or larger
  - Multi-AZ enabled for availability
  - Automatic failover enabled
- Use for:
  - OPcache distribution (via Redis adapter)
  - Chart result caching (24-hour TTL)
  - Rate-limit counters

---

## Deployment Steps (EC2-based ECS)

### Step 1: Create ECS Cluster
```bash
aws ecs create-cluster --cluster-name jyotish-prod-cluster --region us-east-1
```

### Step 2: Create ECS Task Definition
```json
{
  "family": "jyotish-api-prod",
  "networkMode": "awsvpc",
  "requiresCompatibilities": ["EC2"],
  "cpu": "1024",
  "memory": "2048",
  "containerDefinitions": [
    {
      "name": "jyotish-api",
      "image": "123456789012.dkr.ecr.us-east-1.amazonaws.com/jyotish-api:prod",
      "portMappings": [
        {
          "containerPort": 8080,
          "hostPort": 8080,
          "protocol": "tcp"
        }
      ],
      "environment": [
        {
          "name": "APP_ENV",
          "value": "prod"
        },
        {
          "name": "CORS_ALLOW_ORIGIN",
          "value": "https://yourfrontend.com"
        }
      ],
      "secrets": [
        {
          "name": "APP_SECRET",
          "valueFrom": "arn:aws:secretsmanager:us-east-1:123456789012:secret:jyotish/prod/app-secret"
        }
      ],
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-group": "/ecs/jyotish-api-prod",
          "awslogs-region": "us-east-1",
          "awslogs-stream-prefix": "ecs"
        }
      },
      "healthCheck": {
        "command": ["CMD-SHELL", "curl -f http://localhost:8080/health || exit 1"],
        "interval": 30,
        "timeout": 5,
        "retries": 3,
        "startPeriod": 60
      }
    }
  ]
}
```

### Step 3: Create ECS Service
```bash
aws ecs create-service \
  --cluster jyotish-prod-cluster \
  --service-name jyotish-api-service \
  --task-definition jyotish-api-prod \
  --desired-count 2 \
  --launch-type EC2 \
  --region us-east-1
```

### Step 4: Attach Auto Scaling
```bash
# Register scalable target
aws application-autoscaling register-scalable-target \
  --service-namespace ecs \
  --resource-id service/jyotish-prod-cluster/jyotish-api-service \
  --scalable-dimension ecs:service:DesiredCount \
  --min-capacity 2 \
  --max-capacity 10 \
  --region us-east-1

# Create scaling policy (scale by CPU)
aws application-autoscaling put-scaling-policy \
  --policy-name jyotish-cpu-scaling \
  --service-namespace ecs \
  --resource-id service/jyotish-prod-cluster/jyotish-api-service \
  --scalable-dimension ecs:service:DesiredCount \
  --policy-type TargetTrackingScaling \
  --target-tracking-scaling-policy-configuration TargetValue=70.0,PredefinedMetricSpecification={PredefinedMetricType=ECSServiceAverageCPUUtilization},ScaleOutCooldown=60,ScaleInCooldown=300 \
  --region us-east-1
```

---

## Testing Before Production

### 1. Load testing
```bash
# Use Apache Bench or wrk to simulate production traffic
ab -n 1000 -c 50 http://localhost:8080/api/ping

# Or use k6 for more realistic scenarios
k6 run load-test.js
```

### 2. Security scanning
```bash
# Scan image for vulnerabilities
aws ecr describe-image-scan-findings \
  --repository-name jyotish-api \
  --image-id imageTag=prod

# Or use Trivy locally
trivy image jyotish-api:prod
```

### 3. Network connectivity
- Verify health check endpoint responds: `curl http://localhost:8080/health`
- Verify API works: `curl http://localhost:8080/api/ping`
- Test from ALB: `curl http://<ALB-DNS>/api/calculate?...`

---

## Rollback Plan
- [ ] Keep previous image tag (e.g., `:prod-v1`, `:prod-v2`)
- [ ] If deployment fails, revert task definition to previous version
- [ ] Automated alerts for high error rates (trigger manual review)

---

## Post-Deployment Validation
1. Check CloudWatch Logs for errors
2. Verify latency metrics (target < 500ms for `/api/calculate`)
3. Monitor error rate (target < 0.1%)
4. Test a few chart calculations manually against production
5. Verify caching is working (repeated requests should be faster)

---

## Useful Commands
```bash
# View logs (real-time)
aws logs tail /ecs/jyotish-api-prod --follow --region us-east-1

# List running tasks
aws ecs list-tasks --cluster jyotish-prod-cluster --region us-east-1

# Update service with new image
aws ecs update-service \
  --cluster jyotish-prod-cluster \
  --service jyotish-api-service \
  --force-new-deployment \
  --region us-east-1
```

---

## Summary of Production Changes
✅ Multi-stage Docker build (smaller, faster)  
✅ OPcache + PHP-FPM tuning (5-10x speed improvement)  
✅ CloudWatch logging (JSON structured logs)  
✅ Security headers + CORS hardening  
✅ Health checks + auto-scaling ready  
✅ Secrets managed via AWS Secrets Manager  
✅ Non-root runtime user  
✅ Resource limits (CPU/memory)  
✅ Nginx hardened config  

**Expected performance improvements:**  
- **Deployment time**: ~2-3 min (multi-stage build)
- **Container size**: ~150-200 MB (down from 1+ GB)
- **Response time**: 2-5x faster with OPcache
- **Availability**: 99.9%+ with auto-scaling + ALB health checks
