# Car Park Booking API

A Laravel 12 (PHP 8.2) REST API for checking car park availability, calculating prices, and managing bookings with token-based authentication (Laravel Sanctum).

## Features

- Pricing rules and capacity are configurable via `config/parking.php`
- Seasonal pricing (summer/winter), weekday/weekend rates
- Authenticated users can create, update, list and delete their own bookings
- Admins can manage all bookings

## Requirements

- PHP >= 8.2
- Composer
- SQLite (default/dev) or another database supported by Laravel

## Quick Start

1. **Clone and install dependencies:**
   ```bash
   git clone <repository-url>
   cd car-park-booking-api
   composer install
   cp .env.example .env
   ```

2. **Configure the database** (SQLite by default):
   ```bash
   touch database/database.sqlite
   ```

   In `.env` set:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

3. **Generate app key and run migrations:**
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```

4. **Run the application:**
   ```bash
   php artisan serve
   # API available at http://127.0.0.1:8000
   
   # Or use the convenience dev script:
   composer run dev
   ```

5. **Run tests:**
   ```bash
   composer test
   ```

## Configuration

Edit `config/parking.php` to customize:

- **`total_spaces`**: integer (default 10)
- **`currency`**: ISO code (default GBP). API returns formatted currency strings using this setting
- **`seasons`**:
    - **summer**: months 6–8 with `weekday_price`, `weekend_price` (in pence)
    - **winter**: months 11–12 with `weekday_price`, `weekend_price` (in pence)
- **`weekday_price`, `weekend_price`**: default prices (in pence) used outside seasons

> **Note on pricing window:** Price is calculated per night from `from_date` (inclusive) up to `to_date` (exclusive). Availability checks iterate from `from_date` to `to_date` (inclusive) per day to count occupied spaces.

## Authentication

Public endpoints allow registration and login. Authenticated routes use Bearer tokens via Laravel Sanctum.

Obtain a token by registering or logging in, then send it via:
```
Authorization: Bearer YOUR_TOKEN
```

### Register
**POST** `/api/v1/register`

**Body (JSON):**
```json
{
  "name": "string (required)",
  "email": "string (required, unique)",
  "password": "string (min 8, required)",
  "password_confirmation": "string (must match password)",
  "date_of_birth": "date (required, before today)"
}
```

**Response 201:**
```json
{
  "success": true,
  "data": {
    "token": "Bearer token string",
    "user": { /* user object */ }
  }
}
```

### Login
**POST** `/api/v1/login`

**Body (JSON):**
```json
{
  "email": "string (required)",
  "password": "string (required)"
}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "token": "Bearer token string",
    "user": { /* user object */ }
  }
}
```

### Logout (auth required)
**POST** `/api/v1/logout`

Revokes the current access token.

### Profile (auth required)
**GET** `/api/v1/profile`

Returns the current authenticated user.

### Demo Users (seeded)

If you run `php artisan migrate --seed`, these demo accounts are created for quick testing:

- **Admin**: `admin@carpark.com` / `password`
- **Customer**: `customer@example.com` / `customer123`

## Postman Collection

A ready-to-use Postman collection is included in the repository root:
`Car Park Book API.postman_collection.json`

### How to use:

1. **Import**: Open Postman → File → Import → Choose the JSON file above
2. **Set base URL**: `http://127.0.0.1:8000` (or your own host/port)
3. **Authenticate**:
    - First call **POST** `/api/v1/login` (or `/api/v1/register`) using demo credentials to get a token
    - Copy the returned token value
    - In Postman, set Authorization to Bearer Token and paste the token
4. **Use endpoints**: Call public endpoints (price, availability) and authenticated booking endpoints

> **Tip:** Create a Postman Environment with variables like `base_url` and `token`, then reference them as `{{base_url}}` and `{{token}}`.

## Project Structure

### Routes
- `routes/api.php` — API routes (grouped under `api/v1` in `bootstrap/app.php`)

### Controllers
- `app/Http/Controllers/API/v1/`
    - `AuthController.php` — register, login, logout, profile
    - `BookingController.php` — bookings CRUD
    - `PricingController.php` — price calculator (invokable)
    - `AvailabilityController.php` — space availability (invokable)

### Requests
- `app/Http/Requests/v1/`
    - `RegisterRequest.php`
    - `LoginRequest.php`
    - `PricingRequest.php`
    - `AvailabilityRequest.php`
    - `CreateBookingRequest.php`
    - `UpdateBookingRequest.php`

### Actions
- `app/Actions/`
    - `RegisterUserAction.php`
    - `LoginUserAction.php`
    - `CreateCarParkBookingAction.php`
    - `UpdateCarParkBookingAction.php`
    - `CheckAvailabilityAction.php`
    - `PriceCalculatorAction.php`

### Resources
- `app/Http/Resources/`
    - `UserResource.php`
    - `BookingResource.php`

> **Note:** Exceptions are handled globally in `bootstrap/app.php` and JSON is forced for `api/*`.

## API Endpoints

### Public Endpoints

#### Check Price
**GET** `/api/v1/price?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD`

**Validations:**
- `from_date`: required, date, after_or_equal:today
- `to_date`: required, date, after:from_date

**Response 200:**
```json
{
  "success": true,
  "total_price": "£20.00",
  "currency": "GBP"
}
```

**Example:**
```
GET /api/v1/price?from_date=2025-10-01&to_date=2025-10-02
```
Returns total_price equals one weekday price.

#### Check Availability
**GET** `/api/v1/availability?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD`

**Validations:** Same as price endpoint

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "date": "2025-10-01", "available_spaces": 10 },
    { "date": "2025-10-02", "available_spaces": 10 }
  ]
}
```

**422** on validation error (e.g., identical dates) with standard validation error structure.

### Bookings (auth required)

All booking endpoints require `Authorization: Bearer <token>`

**Resource:** `/api/v1/bookings`

#### List Bookings
**GET** `/api/v1/bookings`

- **Customers**: only their own bookings
- **Admins**: all bookings
- Returns a paginated collection of booking resources

#### Show Booking
**GET** `/api/v1/bookings/{id}`

- **Customers**: can only view their own booking
- **Admins**: can view any booking

#### Create Booking
**POST** `/api/v1/bookings`

**Body (JSON):**
```json
{
  "from_date": "date (required, after_or_equal:today)",
  "to_date": "date (required, after:from_date)",
  "notes": "string (optional)",
  "user_id": "int (optional, Admins only)"
}
```

**Response 201:**
```json
{
  "success": true,
  "data": { /* booking resource */ }
}
```

If capacity is full on any day in the period, returns **422** with message under availability.

#### Update Booking
**PUT** `/api/v1/bookings/{id}`

**Body (JSON):**
```json
{
  "from_date": "date (required)",
  "to_date": "date (required)",
  "status": "enum (ACTIVE|CANCELLED|..., required)",
  "notes": "string (optional)",
  "user_id": "int (optional, Admins only)"
}
```

**Response 200** with updated booking resource. Customers cannot update others' bookings (404).

#### Delete Booking
**DELETE** `/api/v1/bookings/{id}`

- **Customers**: can delete their own booking
- **Admins**: can delete any booking

**Response 200** on success; **404** when not permitted.

### Booking Resource Shape

**Fields:**
- `id`, `start_date`, `end_date`
- `total_price`: formatted (currency from config)
- `currency`
- `status`: human-readable label
- `notes`
- `user_id`
- `user`: included when loaded (BookingController loads it)
- `created_at`, `updated_at`

## Error Handling

Exceptions are handled globally at `bootstrap/app.php` using Laravel's `withExceptions` configuration. All API responses are JSON.

### HTTP Status Codes:
- **401 Unauthenticated**: when the user is not logged in
- **401 Unauthorized**: when the user lacks permissions
- **422 Validation failed**: returns message and errors[] from validator
- **404 Resource not found** or Endpoint not found
- **405 Method not allowed**
- **500 Internal server error**

These handlers also log the exception and automatically force JSON for routes under `api/*`.

## Database Seeding

Populate the database with sample data and default users:

```bash
# Run migrations with seeders
php artisan migrate --seed

# Or run seeders separately
php artisan db:seed

# Run a specific seeder
php artisan db:seed --class=Database\\Seeders\\UserSeeder
```

### Default Credentials (seeded):
- **Admin**: `admin@carpark.com` / `password`
- **Customer**: `customer@example.com` / `customer123`
- Plus 10 random customer accounts via factory

## Testing

```bash
composer test
```

- Clears config cache and runs the full Pest test suite
- Tests use `RefreshDatabase` to migrate the schema per test run
- Ensure your test database is configured (SQLite works out of the box by default)
- Optionally create a `.env.testing` with custom DB settings

## Useful Scripts

```bash
# Run server with concurrent processes (optional)
composer run dev

# Run test suite
composer test
```

## Notes

- Prices are stored in pence internally and formatted for responses
- Incoming values for price are never required in requests; server calculates them
- Capacity and pricing rules are evaluated per calendar day
