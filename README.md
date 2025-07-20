# Fleet Management System Backend

A Laravel-based backend for managing bus trips, stations, seat bookings, and user authentication.

---

## 🚀 Project Overview
This project is a backend API for a bus fleet management system. It allows users to:
- Register and log in (with secure tokens)
- View available seats for any trip segment
- Book seats for any trip segment
- Prevent double-booking for overlapping segments

---

## 🛠️ Prerequisites
- PHP 8.2 or higher
- Composer (dependency manager for PHP)
- MySQL (for the database)
- Git (to clone the project)

---

## 📦 Setup Instructions

### 1. Clone the Repository
```
git clone https://github.com/ahmed12348/-fleet-management-system.git
cd -fleet-management-system
```

### 2. Install PHP Dependencies
```
composer install
```

### 3. Copy Environment File
```
cp .env.example .env
```

### 4. Generate Application Key
```
php artisan key:generate
```

### 5. Configure Database
- Open `.env` and set your database connection (MySQL recommended):
  - For MySQL, set `DB_CONNECTION=mysql` and update `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

### 6. Run Migrations & Seeders
```
php artisan migrate:refresh --seed
```
This will create all tables and fill them with demo data (stations, trips, buses, etc).



### 7. Start the Server
```
php artisan serve
```
The API will be available at `http://localhost:8000` by default.

---

## 🔑 Authentication (Sanctum)
- Register a new user: `POST /api/register`
- Log in: `POST /api/login`
- Use the returned token as a Bearer token in the `Authorization` header for all protected endpoints.

---

## 🚌 Booking & Seat Endpoints

### 1. Get Available Seats
```
POST /api/available-seats
Headers: Authorization: Bearer <token>
Body (JSON):
{
  "trip_id": 1,
  "from_station_id": 2,
  "to_station_id": 4
}
```
- Returns: `{ "available_seats": [1,2,3,...] }`

### 2. Book a Seat
```
POST /api/book
Headers: Authorization: Bearer <token>
Body (JSON):
{
  "trip_id": 1,
  "from_station_id": 2,
  "to_station_id": 4,
  "seat_number": 5
}
```
- Returns success or error if seat is already booked for that segment.

---

## 📥 Postman Collection (Bonus)

A ready-to-use Postman collection is included for easy API testing!

**How to use:**
1. Download the collection file: [Download Postman Collection](postman/Fleet Management System.postman_collection.json)
2. Open Postman and click "Import".
3. Select the downloaded `Fleet Management System.postman_collection.json` file.
4. Set your API base URL (e.g., `http://localhost:8000`).
5. Register/login to get your Bearer token, then set it in the collection's Authorization tab.
6. Use the provided requests to test:
   - Get Available Seats
   - Book a Seat

**This makes it easy for anyone to test the API without writing code!**

---

## 🧪 Testing
- Run all tests:
```
php artisan test
```
- Run only unit tests:
```
php artisan test --testsuite=Unit
```
- Run only feature tests:
```
php artisan test --testsuite=Feature
```
- Run a specific test class:
```
php artisan test --filter=AvailableSeatsTest
```

### 🎁 Bonus: Included Tests
- **Unit tests** for model relationships (e.g., `TripTest`)
- **Feature tests** for API endpoints (e.g., `AvailableSeatsTest`)
- These tests help ensure your app works as expected and make it easy to refactor or extend.

---

## ❓ Troubleshooting
- **Database errors:** Check your `.env` DB settings and run `php artisan migrate:fresh --seed`.
- **Token errors:** Make sure you include `Authorization: Bearer <token>` in your API requests.
- **Stations not found:** Only use station IDs that belong to the selected trip (see seed data).

---

## 📚 Further Help
- Laravel Docs: https://laravel.com/docs
- Sanctum Docs: https://laravel.com/docs/12.x/sanctum

---

## 📝 License
MIT
