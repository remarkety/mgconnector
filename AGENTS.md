# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is **Remarkety_Mgconnector**, a Magento 2 extension (v2.17.0) that integrates Magento stores with the Remarkety marketing automation platform. The extension syncs products, customers, orders, quotes, and subscribers between Magento and Remarkety via webhooks and REST APIs.

## Development Commands

### Installation & Setup
```bash
# Install via Composer
composer require remarkety/mgconnector

# Run Magento setup commands
php bin/magento setup:upgrade
php bin/magento setup:di:compile

# Deploy static content (if needed)
php bin/magento setup:static-content:deploy

# Clear cache
php bin/magento cache:clean
php bin/magento cache:flush
```

### Cron Setup
The extension requires Magento cron to run every minute for queue processing. The cron job `remarkety_queue` processes failed webhook events from the `mgconnector_queue` database table.

Verify cron is working:
```bash
php bin/magento cron:run
```

### Database
View queue status:
```bash
# Access MySQL and query the queue table
mysql -u [user] -p [database]
SELECT * FROM mgconnector_queue;
```

The queue table schema includes: `queue_id`, `event_type`, `payload`, `attempts`, `last_attempt`, `next_attempt`, `status`, `store_id`, `last_error_message`.

## Architecture

### Core Workflow

1. **Event Observation**: Magento events (customer save, order placed, product updated, etc.) trigger observers
2. **Webhook Dispatch**: Observers serialize data and send webhooks to Remarkety's API endpoint (`https://webhooks.remarkety.com/webhooks`)
3. **Queue Management**: Failed webhooks are stored in `mgconnector_queue` table and retried by cron
4. **REST API**: Remarkety can pull data from Magento via 17 REST API endpoints

### Key Components

**Observers (Observer/)**
- 20+ event observers handle Magento lifecycle events
- All extend `EventMethods` which contains shared webhook logic
- Events include: customer CRUD, order lifecycle, product updates, newsletter subscriptions, inventory changes
- Example: `TriggerOrderPlacedFinished` sends order data when `sales_order_place_after` fires

**Serializers (Serializer/)**
- Transform Magento models into Remarkety API format
- `ProductSerializer`: Handles products, configurable products, variants, categories, prices
- `CustomerSerializer`: Customer data, addresses, marketing consent
- `OrderSerializer`: Orders, line items, discounts, totals
- `AddressSerializer`: Billing/shipping addresses
- `CheckSubscriberTrait`: Shared newsletter subscription logic

**REST API (Api/, Model/Api/)**
- Interface: `Remarkety\Mgconnector\Api\DataInterface`
- Implementation: `Remarkety\Mgconnector\Model\Api\Data`
- Routes defined in `etc/webapi.xml`
- Endpoints: `/V1/mgconnector/products`, `/V1/mgconnector/customers`, `/V1/mgconnector/orders`, `/V1/mgconnector/carts`, `/V1/mgconnector/createCoupon`, `/V1/mgconnector/queue`, etc.

**Configuration (Helper/ConfigHelper.php)**
- 40+ configuration paths stored in `core_config_data` table
- Key settings: store ID, webhook toggles, async modes, category paths, consent options, reward points integration
- Async modes: OFF (0), ON (1), ON_CUSTOMERS_SYNC (2)
- Access via `ConfigHelper::getRemarketyPublicId()`, `ConfigHelper::isWebHooksEnabled()`, etc.

**Queue System (Model/Queue.php, Cron/Queue.php)**
- Failed webhooks stored in `mgconnector_queue` table
- Cron runs every minute (`* * * * *`) to retry failed items
- Retry logic includes exponential backoff and attempt tracking
- Queue manageable via admin UI (Remarkety > Queue Management) or REST API

**Admin Controllers (Controller/Adminhtml/)**
- Installation wizard: `Install/`
- Settings management: `Settings/`
- Queue monitoring: `Queue/`
- Routes defined in `etc/adminhtml/routes.xml`

**Frontend Features (Controller/Frontend/, Block/Frontend/)**
- Tracking pixels for analytics
- Auto-coupon application
- Cart recovery functionality
- Checkout modifications via `Plugin/CheckoutLayoutPlugin.php`

### Dependency Injection

Configuration in `etc/di.xml`:
- Interface preferences map API interfaces to implementations
- Plugin system: `CheckoutLayoutPlugin` modifies checkout layout processor
- All serializers, helpers, and repositories injected via constructor

### Event System

Defined in `etc/events.xml`:
- `customer_save_after` → `TriggerCustomerUpdateObserver`
- `sales_order_place_after` → `TriggerOrderPlacedFinished`
- `catalog_product_save_commit_after` → `TriggerProductUpdated`
- `newsletter_subscriber_save_commit_after` → `TriggerSubscribeUpdateObserver`
- Plus 13 more event-observer mappings

### Webhook Modes

Three webhook delivery modes:
1. **Synchronous**: Immediate HTTP POST to Remarkety (blocks request)
2. **Async (Queue)**: Adds to queue, cron sends later (default for most events)
3. **Async (Customers Sync)**: Special async mode for bulk customer syncs

Mode controlled by `ConfigHelper::FORCE_NON_ASYNC_WEBHOOKS` and `ConfigHelper::FORCE_ASYNC_WEBHOOKS`.

## Important Patterns

### Adding New Event Observers

1. Create observer class in `Observer/` extending `EventMethods`
2. Implement `execute(\Magento\Framework\Event\Observer $observer)` method
3. Use appropriate serializer to format data
4. Call `$this->makeRequest($eventType, $data, $storeId)` to send webhook
5. Register in `etc/events.xml` with event name and observer instance

### Working with Configuration

Always use `ConfigHelper` for configuration access:
```php
$configHelper->getRemarketyPublicId($storeId);
$configHelper->isWebHooksEnabled();
$configHelper->getAsyncMode();
```

Configuration paths follow pattern: `remarkety/mgconnector/[setting_name]`

### Serializer Usage

Each serializer has a `serialize()` method that accepts a Magento model and returns an array:
```php
$productSerializer->serialize($product, $storeId);
$customerSerializer->serialize($customer);
$orderSerializer->serialize($order);
```

Serializers handle complex relationships (categories, addresses, line items) and apply store-specific configuration.

### Queue Processing

Failed webhooks automatically enter the queue. Manual queue operations:
- View: Admin UI or `GET /V1/mgconnector/queue`
- Retry: Admin UI or `POST /V1/mgconnector/queue`
- Delete: Admin UI or `DELETE /V1/mgconnector/queue`

### AheadWorks Reward Points Integration

Optional integration with AheadWorks Reward Points module:
- Toggle via `ConfigHelper::ENABLE_AHEADWORKS_REWARD_POINTS`
- Observer: `TriggerAWRewardsPointsObserver`
- Event: `aheadworks_rewardpoints_api_data_transactioninterface_save_after`

## Module Structure

```
Remarkety/Mgconnector/
├── Api/                    # REST API interfaces and data contracts
├── Block/                  # UI components (admin & frontend)
├── Controller/             # HTTP request handlers
│   ├── Adminhtml/          # Admin controllers (install, settings, queue)
│   └── Frontend/           # Frontend controllers (recovery, tracking)
├── Cron/                   # Queue processor (runs every minute)
├── Helper/                 # Utility classes (ConfigHelper is critical)
├── Model/                  # Business logic, data models, repositories
│   ├── Api/                # REST API implementation
│   ├── ResourceModel/      # Database layer
│   └── Queue.php           # Queue model
├── Observer/               # Event observers (20+ files)
│   └── EventMethods.php    # Base class with webhook logic
├── Plugin/                 # Magento interceptors
├── Serializer/             # Data format converters (Magento → Remarkety)
├── Setup/                  # Installation/upgrade scripts
├── ViewModel/              # View models for templates
├── etc/                    # Magento configuration XML
│   ├── module.xml          # Module definition (v2.17.0)
│   ├── di.xml              # Dependency injection
│   ├── events.xml          # Event-observer mappings
│   ├── webapi.xml          # REST API routes (17 endpoints)
│   ├── crontab.xml         # Cron schedule
│   └── db_schema.xml       # Database schema
└── view/                   # Templates and frontend assets
    ├── adminhtml/          # Admin templates
    └── frontend/           # Storefront templates & JS
```

## Testing & Debugging

### No Automated Tests
This codebase has no PHPUnit tests or automated testing framework. Testing is manual via:
- Admin UI interaction
- API endpoint testing (Postman, curl)
- Store frontend testing
- Magento logs

### Logging & Debugging

Logs are in Magento's standard locations:
```bash
# View recent logs
tail -f var/log/system.log
tail -f var/log/exception.log
tail -f var/log/debug.log
```

Enable developer mode for better error messages:
```bash
php bin/magento deploy:mode:set developer
```

Debug webhooks:
1. Check queue table for failed items
2. Review `last_error_message` column
3. Enable webhook timer via `ConfigHelper::ENABLE_WEBHOOKS_TIMER`
4. Check Magento logs for HTTP errors

### Common Issues

- **Webhooks not sending**: Check `ConfigHelper::isWebHooksEnabled()` and store installation status
- **Queue not processing**: Verify Magento cron is configured and running
- **Missing data in Remarkety**: Check serializers and observer event registration
- **Performance issues**: Consider enabling async mode for all webhooks

## Branch & Version Info

- Main branch: `master`
- Current version: 2.17.0
- Latest feature branch: `NAM-35894-Fix-authentication-for-webhooks-from-Magento`
- Namespace: `Remarkety\Mgconnector`
- Composer package: `remarkety/mgconnector`