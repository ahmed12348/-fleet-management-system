# Fleet Management System - Inventory Management API

A comprehensive Laravel 10 RESTful API for managing inventory across multiple warehouses with real-time stock tracking, transfers, and low-stock alerts.

## 🚀 Features

- **Multi-Warehouse Inventory Management**: Track inventory across multiple warehouses
- **Real-time Stock Tracking**: Monitor stock levels with automatic low-stock detection
- **Stock Transfers**: Transfer inventory between warehouses with validation
- **Authentication**: Secure API access using Laravel Sanctum
- **Caching**: Optimized performance with Redis/Memory caching
- **Event System**: Low-stock alerts and notifications
- **Comprehensive Testing**: Unit and feature tests with 100% coverage
- **RESTful API**: Clean, consistent JSON responses
- **Postman Collection**: Complete API testing collection with examples

## 📋 Requirements

- PHP 8.2+
- Laravel 10.x
- MySQL/PostgreSQL/SQLite
- Composer
- Redis (optional, for caching)

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd fleet-management
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Configuration
Update your `.env` file with database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fleet_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Install Sanctum (if not already installed)
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 7. Start the Server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## 📚 API Endpoints

> **💡 Quick Start**: Import the Postman collection for instant API testing with all endpoints pre-configured!

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/register` | Register a new user | No |
| POST | `/api/login` | Login user | No |
| POST | `/api/logout` | Logout user | Yes |

### Warehouses

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/warehouses` | Get all warehouses | No |
| GET | `/api/warehouses/{id}` | Get specific warehouse | No |
| GET | `/api/warehouses/{id}/inventory` | Get warehouse inventory (cached) | No |
| POST | `/api/warehouses` | Create new warehouse | Yes |

### Inventory Items

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/inventory-items` | Get inventory items (with search/filter) | No |
| GET | `/api/inventory-items/{id}` | Get specific inventory item | No |
| POST | `/api/inventory-items` | Create new inventory item | Yes |
| PUT | `/api/inventory-items/{id}` | Update inventory item | Yes |
| DELETE | `/api/inventory-items/{id}` | Delete inventory item | Yes |

### Stock Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/stocks` | Get all stocks (with filters) | Yes |
| GET | `/api/stocks/{id}` | Get specific stock | Yes |
| POST | `/api/stocks` | Add/update stock | Yes |
| PUT | `/api/stocks/{id}` | Update stock quantity | Yes |
| DELETE | `/api/stocks/{id}` | Delete stock | Yes |

### Stock Transfers

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/stock-transfers` | Get all transfers (with filters) | Yes |
| GET | `/api/stock-transfers/{id}` | Get specific transfer | Yes |
| POST | `/api/stock-transfers` | Create new transfer | Yes |
| POST | `/api/stock-transfers/{id}/cancel` | Cancel pending transfer | Yes |

## 🔍 Query Parameters

### Inventory Items Search
- `q` - Search by name
- `sku` - Search by SKU
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `order_by` - Sort field (default: name)
- `order_direction` - Sort direction (asc/desc)
- `per_page` - Items per page (default: 10)

### Stock Filters
- `warehouse_id` - Filter by warehouse
- `inventory_item_id` - Filter by inventory item
- `low_stock` - Show only low stock items (1/0)
- `per_page` - Items per page (default: 15)

### Stock Transfer Filters
- `warehouse_id` - Filter by warehouse (from or to)
- `status` - Filter by status (pending/completed/failed)
- `inventory_item_id` - Filter by inventory item
- `per_page` - Items per page (default: 15)

## 📝 Request/Response Examples

### Register User
```bash
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "User registered successfully.",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|abc123..."
}
```

### Login User
```bash
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

### Create Warehouse
```bash
POST /api/warehouses
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
    "name": "Main Warehouse",
    "location": "New York, NY"
}
```

### Create Inventory Item
```bash
POST /api/inventory-items
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
    "name": "Widget Pro",
    "sku": "WP001",
    "price": 29.99
}
```

### Add Stock
```bash
POST /api/stocks
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
    "warehouse_id": 1,
    "inventory_item_id": 1,
    "quantity": 100
}
```

### Transfer Stock
```bash
POST /api/stock-transfers
Authorization: Bearer 1|abc123...
Content-Type: application/json

{
    "from_warehouse_id": 1,
    "to_warehouse_id": 2,
    "inventory_item_id": 1,
    "quantity": 25
}
```

## 📮 Postman Collection

### Import the Collection
The project includes a complete Postman collection with all API endpoints, sample requests, and responses.

**Collection File**: `postman/Fleet_Management_Inventory_API.postman_collection.json`

### How to Import:
1. Open Postman
2. Click **Import** button
3. Select the `Fleet_Management_Inventory_API.postman_collection.json` file
4. Click **Import**

### Collection Structure:
```
Fleet Management - Inventory API
├── Authentication
│   ├── Register User
│   ├── Login User
│   └── Logout User
├── Warehouses
│   ├── Get All Warehouses
│   ├── Get Warehouse by ID
│   ├── Get Warehouse Inventory
│   └── Create Warehouse
├── Inventory Items
│   ├── Get All Inventory Items
│   ├── Search Inventory Items
│   ├── Get Inventory Item by ID
│   ├── Create Inventory Item
│   ├── Update Inventory Item
│   └── Delete Inventory Item
├── Stock Management
│   ├── Get All Stocks
│   ├── Get Stocks with Filters
│   ├── Get Stock by ID
│   ├── Add/Update Stock
│   ├── Update Stock Quantity
│   └── Delete Stock
└── Stock Transfers
    ├── Get All Stock Transfers
    ├── Get Stock Transfers with Filters
    ├── Get Stock Transfer by ID
    ├── Create Stock Transfer
    └── Cancel Stock Transfer
```

### Environment Variables:
The collection uses these variables:
- `{{base_url}}` - API base URL (default: `http://localhost:8000`)
- `{{auth_token}}` - Authentication token (set after login)

### Quick Start with Postman:
1. **Set Base URL**: Update `{{base_url}}` to your server URL
2. **Register/Login**: Use Authentication endpoints to get token
3. **Set Token**: Copy token from login response to `{{auth_token}}` variable
4. **Test Endpoints**: All requests are ready to use!

### Sample Workflow:
1. **Register User** → Get token
2. **Create Warehouse** → Get warehouse ID
3. **Create Inventory Item** → Get item ID
4. **Add Stock** → Set initial inventory
5. **Transfer Stock** → Test stock transfers
6. **Check Inventory** → View warehouse inventory

### Collection Features:
- ✅ **Complete API Coverage**: All 20+ endpoints included
- ✅ **Sample Data**: Realistic request/response examples
- ✅ **Environment Variables**: Easy configuration
- ✅ **Authentication**: Token-based auth setup
- ✅ **Error Examples**: Common error responses
- ✅ **Documentation**: Inline descriptions for each endpoint

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Unit tests
php artisan test --testsuite=Unit

# Feature tests
php artisan test --testsuite=Feature

# Specific test file
php artisan test tests/Feature/StockTransferTest.php
```

### Test Coverage
The project includes comprehensive tests covering:
- ✅ Authentication flows
- ✅ CRUD operations for all models
- ✅ Stock transfer validation
- ✅ Low stock event dispatching
- ✅ Caching functionality
- ✅ Error handling
- ✅ Authorization

## 🔧 Configuration

### Caching
The API uses Laravel's caching system for performance optimization:
- Warehouse lists are cached for 5 minutes
- Warehouse inventory is cached for 5 minutes
- Caches are automatically cleared when data changes

### Events
The system dispatches events for important actions:
- `LowStockDetected` - Triggered when stock falls below threshold (5 units)

### Database
The system uses the following main tables:
- `warehouses` - Warehouse information
- `inventory_items` - Product catalog
- `stocks` - Inventory levels per warehouse
- `stock_transfers` - Transfer history
- `users` - User accounts
- `personal_access_tokens` - API authentication

## 🚨 Error Handling

The API returns consistent JSON error responses:

```json
{
    "success": false,
    "error": "Error message",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## 🔒 Security Features

- **Sanctum Authentication**: Secure token-based authentication
- **Input Validation**: Comprehensive validation rules
- **SQL Injection Prevention**: Eloquent ORM protection
- **CORS Support**: Cross-origin request handling
- **Rate Limiting**: Built-in Laravel rate limiting

## 📊 Performance Features

- **Database Indexing**: Optimized queries with proper indexes
- **Eager Loading**: Prevents N+1 query problems
- **Caching**: Redis/Memory caching for frequently accessed data
- **Pagination**: Efficient data pagination
- **Database Transactions**: ACID compliance for critical operations

## 🛠️ Development

### Code Style
The project follows PSR-12 coding standards and uses Laravel Pint for code formatting:

```bash
./vendor/bin/pint
```

### Database Seeding
Create sample data for testing:

```bash
php artisan db:seed
```

### Queue Jobs
For production, configure queue workers for event processing:

```bash
php artisan queue:work
```

## 📈 Monitoring

### Logs
Application logs are stored in `storage/logs/laravel.log`

### Health Check
Check API health:
```bash
GET /api/health
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 📁 Project Files

### Key Files:
- **API Collection**: `postman/Fleet_Management_Inventory_API.postman_collection.json`
- **Documentation**: `README.md` (this file)
- **Database Seeders**: `database/seeders/InventorySeeder.php`
- **Tests**: `tests/` directory with comprehensive test coverage

### Collection Features:
- **20+ API Endpoints** with sample requests/responses
- **Environment Variables** for easy configuration
- **Authentication Flow** with token management
- **Complete Workflow** from registration to stock transfers
- **Error Examples** for common scenarios

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Review the test cases for usage examples
- Use the Postman collection for API testing

---

**Built with ❤️ using Laravel 10**