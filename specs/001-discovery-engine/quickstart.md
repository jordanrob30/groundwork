# Quickstart: Discovery Engine

**Branch**: `001-discovery-engine` | **Date**: 2025-12-08

## Prerequisites

- Docker Desktop installed and running
- Git
- Composer (installed locally or will use Sail)

## Initial Setup

### 1. Clone and Install Dependencies

```bash
# Clone the repository
git clone <repository-url> groundwork
cd groundwork

# Install PHP dependencies (creates vendor directory with Sail)
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
./vendor/bin/sail artisan key:generate
```

Edit `.env` with your settings:

```env
APP_NAME="Discovery Engine"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=groundwork
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis

# Claude API (required for AI analysis)
ANTHROPIC_API_KEY=your_api_key_here
```

### 3. Start Development Environment

```bash
# Start all containers (MySQL, Redis, Laravel)
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed initial data (optional)
./vendor/bin/sail artisan db:seed
```

### 4. Access the Application

- **Web Application**: http://localhost
- **MySQL**: localhost:3306
- **Redis**: localhost:6379
- **Mailpit (email testing)**: http://localhost:8025

---

## Development Commands

All commands should be prefixed with `sail` per constitution requirements.

### Artisan Commands

```bash
# Run migrations
sail artisan migrate

# Rollback migrations
sail artisan migrate:rollback

# Fresh migration with seeds
sail artisan migrate:fresh --seed

# Create model with migration, factory, and seeder
sail artisan make:model Campaign -mfs

# Create Livewire component
sail artisan make:livewire Campaign/CampaignList

# Clear all caches
sail artisan optimize:clear
```

### Queue Workers

```bash
# Start queue worker for emails
sail artisan queue:work --queue=emails

# Start queue worker for all queues
sail artisan queue:work

# Process a single job (useful for debugging)
sail artisan queue:work --once

# Monitor queue with Horizon (recommended)
sail artisan horizon
```

### Testing

```bash
# Run all tests
sail artisan test

# Run specific test file
sail artisan test --filter=MailboxTest

# Run with coverage
sail artisan test --coverage

# Run Pint for code style
sail pint

# Run static analysis (if configured)
sail artisan stan
```

### Asset Compilation

```bash
# Install npm dependencies
sail npm install

# Development build with hot reload
sail npm run dev

# Production build
sail npm run build
```

---

## Package Installation

Install the required packages from research phase:

```bash
# Email Integration
sail composer require webklex/laravel-imap

# AI Integration
sail composer require claude-php/claude-php-sdk-laravel

# CSV Processing
sail composer require league/csv

# Email Parsing
sail composer require zbateson/mail-mime-parser

# Publish Claude SDK config
sail artisan vendor:publish --provider="ClaudePHP\Laravel\ClaudeServiceProvider"

# Publish IMAP config
sail artisan vendor:publish --provider="Webklex\IMAP\Providers\LaravelServiceProvider"
```

---

## Key Configuration Files

### config/services.php

```php
'claude' => [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
],
```

### config/imap.php

```php
// Runtime configuration per mailbox - see research.md for details
```

### config/queue.php

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 180,
        'block_for' => null,
    ],
],
```

---

## Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Poll mailboxes for replies every 5 minutes
    $schedule->command('mailbox:poll')->everyFiveMinutes();

    // Update warm-up limits daily
    $schedule->command('mailbox:warmup')->dailyAt('00:05');

    // Schedule day's emails at start of business
    $schedule->command('emails:schedule')->dailyAt('08:00');

    // Generate insights for active campaigns
    $schedule->command('insights:generate')->hourly();
}
```

---

## Feature Development Workflow

### 1. Create Database Migration

```bash
sail artisan make:migration create_campaigns_table
```

### 2. Create Model

```bash
sail artisan make:model Campaign
```

### 3. Create Service Class

```bash
# Create manually in app/Services/CampaignService.php
```

### 4. Create Livewire Component

```bash
sail artisan make:livewire Campaign/CampaignList
```

### 5. Create Tests

```bash
# Feature test
sail artisan make:test Campaign/CampaignListTest

# Unit test
sail artisan make:test Services/CampaignServiceTest --unit
```

### 6. Run Tests

```bash
sail artisan test
sail pint
```

---

## Troubleshooting

### Docker Issues

```bash
# Rebuild containers
sail build --no-cache

# View container logs
sail logs

# Shell into container
sail shell
```

### Database Issues

```bash
# Reset database
sail artisan migrate:fresh --seed

# Check database connection
sail artisan db:show
```

### Queue Issues

```bash
# Check failed jobs
sail artisan queue:failed

# Retry failed jobs
sail artisan queue:retry all

# Clear all jobs
sail artisan queue:clear
```

### Cache Issues

```bash
# Clear all caches
sail artisan optimize:clear

# Clear specific cache
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear
```

---

## Production Deployment Notes

### Environment Variables

Ensure these are set in production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Strong encryption key
APP_KEY=base64:your_generated_key

# Database credentials
DB_PASSWORD=strong_production_password

# Redis configuration
REDIS_PASSWORD=strong_redis_password

# Claude API
ANTHROPIC_API_KEY=your_production_api_key
```

### Required Services

- MySQL 8.0+
- Redis 6.0+
- PHP 8.3+
- Supervisor (for queue workers)
- Nginx or Apache

### Supervisor Configuration

```ini
[program:groundwork-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/groundwork/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/groundwork/worker.log
stopwaitsecs=3600
```

### Cron Entry

```cron
* * * * * cd /var/www/groundwork && php artisan schedule:run >> /dev/null 2>&1
```
