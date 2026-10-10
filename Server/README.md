# Noras Backend API - Laravel 13

RESTful API backend for the Noras academic platform built with Laravel 13.

## Current Status

✅ **Phase 1 Complete**: Laravel foundation, health endpoints, automated testing  
✅ **Phase 2 Complete**: Authentication, student verification, role-based authorization

## Requirements

- PHP 8.5+
- MySQL 8.0+
- Composer 2.9+

## Installation

### 1. Install Dependencies

```bash
composer install
```

### 2. Environment Configuration

Copy `.env.example` to `.env`:

```bash
copy .env.example .env
```

Update the following environment variables in `.env`:

```env
APP_NAME=Noras
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=noras
DB_USERNAME=noras_user
DB_PASSWORD=your_secure_password_here
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Database Setup

Create the MySQL database and user:

```sql
CREATE DATABASE noras;
CREATE USER 'noras_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON noras.* TO 'noras_user'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Seed Admin User (Development Only)

```bash
php artisan db:seed --class=AdminUserSeeder
```

**Default Admin Credentials** (Development):
- Email: `admin@noras.test`
- Password: `admin123`
- **⚠️ Change these credentials in production!**

### 7. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`

## API Endpoints

### Base URL
```
http://127.0.0.1:8000/api/v1
```

### Health Check
**GET** `/api/v1/health` (Public)

Returns application health status and database connectivity.

### Authentication

#### Register Student
**POST** `/api/v1/auth/register` (Public)

Register a new student account (status: pending).

**Request Body**:
```json
{
  "name": "Student Name",
  "email": "student@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "student_id": "WU2024001"
}
```

**Response (201 Created)**:
```json
{
  "status": "success",
  "message": "Registration successful. Your account is pending approval.",
  "data": {
    "user": {
      "id": 1,
      "name": "Student Name",
      "email": "student@example.com",
      "student_id": "WU2024001",
      "account_status": "pending"
    }
  }
}
```

#### Login
**POST** `/api/v1/auth/login` (Public)

Login with email and password (approved accounts only).

**Request Body**:
```json
{
  "email": "student@example.com",
  "password": "password123"
}
```

**Response (200 OK)**:
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "name": "Student Name",
      "email": "student@example.com",
      "student_id": "WU2024001",
      "role": "student",
      "account_status": "approved"
    }
  }
}
```

#### Get Current User
**GET** `/api/v1/auth/me` (Authenticated)

Get authenticated user's profile.

**Headers**: `Authorization: Bearer {token}`

**Response (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "name": "Student Name",
      "email": "student@example.com",
      "student_id": "WU2024001",
      "role": "student",
      "account_status": "approved",
      "approved_at": "2026-10-10T17:00:00+00:00"
    }
  }
}
```

#### Logout
**POST** `/api/v1/auth/logout` (Authenticated)

Revoke current access token.

**Headers**: `Authorization: Bearer {token}`

### Admin - Student Management

#### List Students
**GET** `/api/v1/admin/students` (Admin Only)

List all students with optional status filter.

**Query Parameters**:
- `status` (optional): `pending`, `approved`, `rejected`, `suspended`

**Headers**: `Authorization: Bearer {admin_token}`

#### Approve Student
**POST** `/api/v1/admin/students/{id}/approve` (Admin Only)

Approve a pending student account.

#### Reject Student
**POST** `/api/v1/admin/students/{id}/reject` (Admin Only)

Reject a student account and revoke all tokens.

#### Suspend Student
**POST** `/api/v1/admin/students/{id}/suspend` (Admin Only)

Suspend an approved student and revoke all tokens.

#### Reactivate Student
**POST** `/api/v1/admin/students/{id}/reactivate` (Admin Only)

Reactivate a suspended or rejected student.

### Error Responses

**401 Unauthenticated**:
```json
{
  "status": "error",
  "message": "Unauthenticated"
}
```

**403 Forbidden**:
```json
{
  "status": "error",
  "message": "Unauthorized. Admin access required."
}
```

**422 Validation Error**:
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

## Authentication

### Token-Based Authentication
This API uses Laravel Sanctum personal access tokens for authentication.

1. Register or login to receive a token
2. Include token in `Authorization: Bearer {token}` header
3. Token remains valid until explicitly revoked via logout

### Account Status

- **pending**: Newly registered, awaiting admin approval
- **approved**: Can login and access student features
- **rejected**: Cannot login, requires reactivation
- **suspended**: Cannot login, requires reactivation

### Roles

- **student**: Default role, access to student features (when approved)
- **admin**: Full access including student management

## Testing

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
php artisan test --filter=AuthTest
php artisan test --filter=StudentApprovalTest
php artisan test --filter=AuthorizationTest
```

### Test Database

Tests use a separate `noras_test` database configured in `phpunit.xml`. Create it with:

```sql
CREATE DATABASE noras_test;
GRANT ALL PRIVILEGES ON noras_test.* TO 'noras_user'@'localhost';
FLUSH PRIVILEGES;
```

### Test Coverage (Phase 2)

- ✅ Student registration with validation
- ✅ Login/logout functionality
- ✅ Account status enforcement
- ✅ Token management
- ✅ Role-based authorization
- ✅ Admin approval workflow
- ✅ Student suspension and reactivation

## Project Structure

```
Server/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       ├── AuthController.php
│   │   │       ├── HealthController.php
│   │   │       └── Admin/
│   │   │           └── StudentApprovalController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureAdmin.php
│   │   │   └── EnsureAccountApproved.php
│   │   └── Requests/
│   │       ├── LoginRequest.php
│   │       └── RegisterRequest.php
│   └── Models/
│       └── User.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_10_10_171216_create_personal_access_tokens_table.php
│   │   └── 2026_10_10_171258_add_student_fields_to_users_table.php
│   └── seeders/
│       └── AdminUserSeeder.php
├── routes/
│   └── api.php
├── tests/
│   └── Feature/Api/V1/
│       ├── AuthTest.php
│       ├── AuthorizationTest.php
│       ├── HealthTest.php
│       └── StudentApprovalTest.php
└── README.md
```

## Architecture

### API Versioning
All API endpoints are versioned under `/api/v1/` to allow future changes without breaking existing clients.

### Authentication
- Laravel Sanctum personal access tokens
- Token-based stateless authentication
- Tokens stored in `personal_access_tokens` table
- Explicit logout revokes tokens

### Authorization
- Role-based access control (student/admin)
- Account status enforcement (pending/approved/rejected/suspended)
- Middleware: `auth:sanctum`, `admin`, `approved`

### Error Handling
- Consistent JSON responses for all API requests
- HTTP status codes follow REST standards
- Detailed validation error messages
- No sensitive information exposed

### Database
- MySQL 8.0 with dedicated `noras_user` account
- Separate `noras_test` database for testing
- Migrations for schema version control
- Factory patterns for test data

### Security
- Passwords hashed with Laravel's secure hashing
- Least-privilege database user
- Environment variables for credentials
- CSRF exempt for stateless API
- SQL injection prevention via Eloquent ORM
- Token revocation on logout/suspension/rejection

## Development

### Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### View Routes

```bash
php artisan route:list --path=api
```

### Database Commands

```bash
# Fresh migration
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback

# Seed admin user
php artisan db:seed --class=AdminUserSeeder
```

## Postman Collection

Import `../Noras-API.postman_collection.json` into Postman for API testing.

**Environment Variables**:
- `base_url`: `http://127.0.0.1:8000`
- `auth_token`: Auto-populated after login

## Security Notes

### Development vs Production

**Development**:
- Admin credentials: `admin@noras.test` / `admin123`
- Detailed error messages enabled
- Local database connection

**Production** (Future):
- Change all default credentials
- Use strong database passwords
- Set `APP_DEBUG=false`
- Configure CORS for frontend domain
- Use HTTPS only
- Implement rate limiting
- Monitor for suspicious activity

## Next Steps (Future Phases)

- ✅ Phase 1: Laravel foundation
- ✅ Phase 2: Authentication & Student Verification
- ⏳ Phase 3: Department & Course Management
- ⏳ Phase 4: Materials & Quiz System
- ⏳ Phase 5: AI Integration

## Support

For issues or questions, contact the development team.

