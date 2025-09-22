# Car Park Booking API

A Laravel 12 (PHP 8.2) REST API for checking car park availability, calculating prices, and managing bookings with token-based authentication (Laravel Sanctum).

- Pricing rules and capacity are configurable via config/parking.php.
- Seasonal pricing (summer/winter), weekday/weekend rates.
- Authenticated users can create, update, list and delete their own bookings. Admins can manage all bookings.

## Requirements
- PHP >= 8.2
- Composer
- SQLite (default/dev) or another database supported by Laravel

## Quick start
1. Clone the repo, then install dependencies:
   - composer install
   - cp .env.example .env
2. Configure the database (SQLite by default):
   - touch database/database.sqlite
   - In .env set:
     - DB_CONNECTION=sqlite
     - DB_DATABASE=database/database.sqlite
3. Generate app key and run migrations:
   - php artisan key:generate
   - php artisan migrate
4. Run the app:
   - php artisan serve (API at http://127.0.0.1:8000)
   - or use the convenience dev script: composer run dev

Run tests:
- composer test

## Configuration
Edit config/parking.php
- total_spaces: integer (default 10)
- currency: ISO code (default GBP). API returns formatted currency strings using this setting.
- seasons:
  - summer: months 6–8 with weekday_price, weekend_price (in pence)
  - winter: months 11–12 with weekday_price, weekend_price (in pence)
- weekday_price, weekend_price: default prices (in pence) used outside seasons

Note on pricing window: price is calculated per night from from_date (inclusive) up to to_date (exclusive). Availability checks iterate from from_date to to_date (inclusive) per day to count occupied spaces.

## Authentication
Public endpoints allow registration and login. Authenticated routes use Bearer tokens via Laravel Sanctum.
- Obtain a token by registering or logging in, then send it via:
  - Authorization: Bearer YOUR_TOKEN

### Register
POST /api/v1/register
Body (JSON):
- name: string, required
- email: string, required, unique
- password: string, min 8, required
- password_confirmation: string, must match password
- date_of_birth: date, required, before today

Response 201:
- success: true
- data.token: string (Bearer token)
- data.user: user object

### Login
POST /api/v1/login
Body (JSON):
- email: string, required
- password: string, required

Response 200:
- success: true
- data.token: string (Bearer token)
- data.user: user object

### Logout (auth required)
POST /api/v1/logout
- Revokes the current access token

### Profile (auth required)
GET /api/v1/profile
- Returns the current authenticated user

### Demo users (seeded)
If you run `php artisan migrate --seed`, these demo accounts are created for quick testing:
- Admin: admin@carpark.com / password
- Customer: customer@example.com / customer123

## Public endpoints

### Check price
GET /api/v1/price?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD
- Validations: from_date required/date/after_or_equal:today; to_date required/date/after:from_date

Response 200:
- success: true
- total_price: formatted currency string (e.g., £20.00)
- currency: e.g., GBP

Example:
GET /api/v1/price?from_date=2025-10-01&to_date=2025-10-02 → total_price equals one weekday price.

### Check availability
GET /api/v1/availability?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD
- Validations: same as price

Response 200:
- success: true
- data: array of { date: YYYY-MM-DD, available_spaces: number }

Example for empty car park (total_spaces=10):
[
  { "date": "2025-10-01", "available_spaces": 10 },
  { "date": "2025-10-02", "available_spaces": 10 }
]

422 on validation error (e.g., identical dates) with standard validation error structure.

## Bookings (auth required)
All booking endpoints require Authorization: Bearer <token>

Resource: /api/v1/bookings

### List bookings
GET /api/v1/bookings
- Customers: only their own bookings
- Admins: all bookings
- Returns a paginated collection of booking resources

### Show booking
GET /api/v1/bookings/{id}
- Customers: can only view their own booking; Admins: any booking

### Create booking
POST /api/v1/bookings
Body (JSON):
- from_date: date, required, after_or_equal:today
- to_date: date, required, after:from_date
- notes: string, optional
- user_id: int, optional (Admins may create on behalf of a user)

Response 201:
- success: true
- data: booking resource

If capacity is full on any day in the period, response 422 with message under availability.

### Update booking
PUT /api/v1/bookings/{id}
Body (JSON):
- from_date: date, required
- to_date: date, required
- status: enum ACTIVE|CANCELLED|... (see App\Enums\BookingStatus), required
- notes: string, optional
- user_id: int, optional (Admins only)

Response 200 with updated booking resource. Customers cannot update others' bookings (404).

### Delete booking
DELETE /api/v1/bookings/{id}
- Customers: can delete their own booking
- Admins: can delete any booking

Response 200 on success; 404 when not permitted.

## Booking resource shape
Fields (selected):
- id, start_date, end_date
- total_price: formatted (currency from config)
- currency
- status (human-readable label)
- notes
- user_id
- user: included when loaded (BookingController loads it)
- created_at, updated_at

## Notes
- Prices are stored in pence internally and formatted for responses. Incoming values for price are never required in requests; server calculates them.
- Capacity and pricing rules are evaluated per calendar day.

## Error handling
Exceptions are handled globally at bootstrap/app.php using Laravel's withExceptions configuration. All API responses are JSON. Mappings:
- 401 Unauthenticated: when the user is not logged in
- 401 Unauthorized: when the user lacks permissions
- 422 Validation failed: returns message and errors[] from validator
- 404 Resource not found or Endpoint not found
- 405 Method not allowed
- 500 Internal server error

These handlers also log the exception and automatically force JSON for routes under api/*.

## Database seeding
You can populate the database with sample data and default users:
- php artisan migrate --seed
- or: php artisan db:seed
- Run a specific seeder: php artisan db:seed --class=Database\Seeders\UserSeeder

Default credentials seeded (see database/seeders):
- Admin: admin@carpark.com / password
- Customer: customer@example.com / customer123
- Plus 10 random customer accounts via factory.

## Tests
- composer test → clears config cache and runs the full Pest test suite
- Tests use RefreshDatabase to migrate the schema per test run. Ensure your test database is configured (by default SQLite works out of the box; optionally create a .env.testing with DB settings).

## Useful scripts
- composer run dev → runs server, queue listener, logs, and Vite concurrently (optional)
- composer test → clears config cache and runs the test suite
