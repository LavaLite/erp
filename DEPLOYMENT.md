# Deployment Guide

This guide covers various deployment options for Lavalite Auth microservice.

## Table of Contents

- [Docker Deployment](#docker-deployment)
- [Traditional Server Deployment](#traditional-server-deployment)
- [Cloud Deployments](#cloud-deployments)
- [Production Checklist](#production-checklist)
- [Environment Variables](#environment-variables)

## Docker Deployment

### Quick Start with Docker Compose

1. **Clone and configure**
   ```bash
   git clone https://github.com/lavalite/erp.git
   cd auth
   cp .env.docker .env
   ```

2. **Update environment variables**
   ```bash
   # Generate secure passwords
   DB_PASSWORD=$(openssl rand -base64 32)
   JWT_SECRET=$(php artisan jwt:secret --show)
   
   # Update .env file with these values
   ```

3. **Build and start services**
   ```bash
   docker-compose up -d
   ```

4. **Initialize application**
   ```bash
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan jwt:secret
   docker-compose exec app php artisan migrate --force
   docker-compose exec app php artisan storage:link
   ```

5. **Access application**
   - Application: http://localhost:8000
   - Database: localhost:3306
   - Redis: localhost:6379

### Production Docker Deployment

For production, use the optimized Dockerfile with environment-specific settings:

```bash
# Build production image
docker build -t lavalite/auth:1.0.0 .

# Tag as latest
docker tag lavalite/auth:1.0.0 lavalite/auth:latest

# Push to registry (GitHub Container Registry example)
docker push ghcr.io/lavalite/erp:1.0.0
docker push ghcr.io/lavalite/erp:latest

# Run in production
docker run -d \
  --name lavalite-erp \
  --restart unless-stopped \
  -p 80:80 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e APP_KEY=base64:YOUR_APP_KEY \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=lavalite_auth \
  -e DB_USERNAME=lavalite \
  -e DB_PASSWORD=your-secure-password \
  -e JWT_SECRET=your-jwt-secret \
  -e REDIS_HOST=your-redis-host \
  ghcr.io/lavalite/erp:latest
```

### Docker Compose for Production

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    image: ghcr.io/lavalite/erp:latest
    restart: unless-stopped
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=db
      - REDIS_HOST=redis
    env_file:
      - .env
    ports:
      - "80:80"
    networks:
      - lavalite-network
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - db-data:/var/lib/mysql
    networks:
      - lavalite-network

  redis:
    image: redis:7-alpine
    restart: unless-stopped
    volumes:
      - redis-data:/data
    networks:
      - lavalite-network

networks:
  lavalite-network:
    driver: bridge

volumes:
  db-data:
  redis-data:
```

Deploy:
```bash
docker-compose -f docker-compose.prod.yml up -d
```

## Traditional Server Deployment

### Requirements

- Ubuntu 22.04 / Debian 12 / CentOS 8+
- PHP 8.2+ with extensions
- Nginx or Apache
- MySQL 8.0+ / PostgreSQL 16+
- Redis 7+
- Composer
- Supervisor (for queue workers)

### Step 1: Install Dependencies

**Ubuntu/Debian:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-pgsql \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd \
  php8.2-bcmath php8.2-redis

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Supervisor
sudo apt install -y supervisor
```

### Step 2: Clone and Setup Application

```bash
# Navigate to web root
cd /var/www

# Clone repository
sudo git clone https://github.com/lavalite/erp.git lavalite-erp
cd lavalite-erp

# Set permissions
sudo chown -R www-data:www-data /var/www/lavalite-erp
sudo chmod -R 755 /var/www/lavalite-erp/storage
sudo chmod -R 755 /var/www/lavalite-erp/bootstrap/cache

# Install dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Setup environment
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan jwt:secret
```

### Step 3: Configure Database

```bash
# Create database
mysql -u root -p

CREATE DATABASE lavalite_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lavalite'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON lavalite_auth.* TO 'lavalite'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Update .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lavalite_auth
DB_USERNAME=lavalite
DB_PASSWORD=secure_password

# Run migrations
sudo -u www-data php artisan migrate --force
```

### Step 4: Configure Nginx

Create `/etc/nginx/sites-available/lavalite-erp`:

```nginx
server {
    listen 80;
    server_name auth.yourdomain.com;
    root /var/www/lavalite-erp/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/lavalite-erp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 5: Configure Supervisor (Queue Workers)

Create `/etc/supervisor/conf.d/lavalite-erp-worker.conf`:

```ini
[program:lavalite-erp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lavalite-erp/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lavalite-erp/storage/logs/worker.log
stopwaitsecs=3600
```

Start workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start lavalite-erp-worker:*
```

### Step 6: Setup SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d auth.yourdomain.com

# Auto-renewal is configured automatically
```

## Cloud Deployments

### AWS EC2

1. **Launch EC2 instance** (t3.small or larger)
2. **Configure Security Group**
   - HTTP (80)
   - HTTPS (443)
   - SSH (22)
3. **Follow traditional server deployment**
4. **Use RDS for database** (recommended)
5. **Use ElastiCache for Redis** (recommended)

### Google Cloud Platform

```bash
# Deploy using Cloud Run
gcloud run deploy lavalite-erp \
  --image ghcr.io/lavalite/erp:latest \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated \
  --set-env-vars APP_ENV=production
```

### DigitalOcean

1. **Use App Platform**
2. **Connect GitHub repository**
3. **Configure environment variables**
4. **Add managed database**
5. **Deploy automatically**

### Kubernetes

Create `k8s/deployment.yaml`:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: lavalite-erp
spec:
  replicas: 3
  selector:
    matchLabels:
      app: lavalite-erp
  template:
    metadata:
      labels:
        app: lavalite-erp
    spec:
      containers:
      - name: lavalite-erp
        image: ghcr.io/lavalite/erp:latest
        ports:
        - containerPort: 80
        env:
        - name: APP_ENV
          value: "production"
        - name: DB_HOST
          valueFrom:
            secretKeyRef:
              name: lavalite-erp-secrets
              key: db-host
---
apiVersion: v1
kind: Service
metadata:
  name: lavalite-erp-service
spec:
  type: LoadBalancer
  ports:
  - port: 80
    targetPort: 80
  selector:
    app: lavalite-erp
```

Deploy:
```bash
kubectl apply -f k8s/deployment.yaml
```

## Production Checklist

### Security

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY`
- [ ] Generate strong `JWT_SECRET`
- [ ] Use strong database passwords
- [ ] Enable HTTPS/SSL
- [ ] Configure CORS properly
- [ ] Set up firewall rules
- [ ] Enable rate limiting
- [ ] Regular security updates

### Performance

- [ ] Enable OPcache
- [ ] Configure Redis caching
- [ ] Optimize Composer autoloader
- [ ] Enable gzip compression
- [ ] Configure CDN for assets
- [ ] Database indexing
- [ ] Query optimization
- [ ] Enable queue workers

### Monitoring

- [ ] Setup error logging (Sentry, Bugsnag)
- [ ] Configure application monitoring
- [ ] Setup uptime monitoring
- [ ] Database monitoring
- [ ] Server resource monitoring
- [ ] Log rotation configured

### Backup

- [ ] Database backup schedule
- [ ] File storage backup
- [ ] Environment file backup
- [ ] Test restore procedures

### Scalability

- [ ] Load balancer configured
- [ ] Auto-scaling setup
- [ ] Database replication
- [ ] Redis clustering
- [ ] CDN integration

## Environment Variables

### Required Variables

```env
APP_NAME=Lavalite Auth
APP_ENV=production
APP_KEY=base64:generated_key
APP_DEBUG=false
APP_URL=https://auth.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lavalite_auth
DB_USERNAME=lavalite
DB_PASSWORD=secure_password

JWT_SECRET=your_jwt_secret
JWT_TTL=60
```

### Optional Variables

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

## Troubleshooting

### Common Issues

**Permission errors:**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

**Queue not processing:**
```bash
sudo supervisorctl restart lavalite-erp-worker:*
```

**Database connection errors:**
- Check credentials in `.env`
- Verify database server is running
- Check firewall rules

**500 errors:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Support

For deployment assistance:
- GitHub Issues: https://github.com/lavalite/erp/issues
- Email: team@lavalite.org
