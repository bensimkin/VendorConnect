# 🔐 API Security Documentation

## Overview

The VendorConnect API implements enterprise-grade security using Laravel Sanctum for token-based authentication with additional security enhancements.

## 🔑 Authentication System

### Token-Based Authentication (Laravel Sanctum)

- **Token Generation**: Each user receives a unique JWT-like token on successful login
- **Token Expiration**: Tokens expire after 7 days (configurable)
- **Token Refresh**: Users can refresh tokens without re-authentication
- **Token Revocation**: Tokens are revoked on logout or manual deletion

### Security Features

#### 1. Rate Limiting
- **Login Attempts**: Maximum 5 attempts per IP address
- **Lockout Period**: 15 minutes after 5 failed attempts
- **IP-based Tracking**: Prevents brute force attacks

#### 2. Token Security
- **Encryption**: Tokens are encrypted using Laravel's APP_KEY
- **Expiration**: Automatic token expiration (7 days default)
- **Single Use**: Each token can only be used once per request
- **Revocation**: Immediate token invalidation on logout

#### 3. User Validation
- **Account Status**: Only active users can authenticate
- **Email Verification**: Optional email verification system
- **Password Security**: Bcrypt hashing with salt

## 🛡️ Protected Endpoints

### Authentication Required
All sensitive endpoints require a valid Bearer token:

```http
Authorization: Bearer {token}
```

### Public Endpoints
- `POST /api/v1/auth/login` - User authentication
- `POST /api/v1/auth/forgot-password` - Password reset request
- `POST /api/v1/auth/reset-password` - Password reset
- `POST /api/v1/auth/verify-email/{id}/{hash}` - Email verification

### Protected Endpoints
All other endpoints require authentication:
- Dashboard data
- User management
- Task management
- Project management
- File uploads
- Profile management

## 🔧 Configuration

### Environment Variables

```env
# Token expiration (in minutes)
SANCTUM_TOKEN_EXPIRATION=10080  # 7 days

# Token prefix (optional)
SANCTUM_TOKEN_PREFIX=

# App encryption key (auto-generated)
APP_KEY=base64:...
```

### Sanctum Configuration

```php
// config/sanctum.php
'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 60 * 24 * 7),
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
```

## 📋 API Authentication Flow

### 1. Login Process
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "user@example.com",
            "photo": "path/to/photo.jpg",
            "status": 1
        },
        "permissions": ["create_tasks", "edit_tasks", ...],
        "token": "1|abc123...",
        "token_type": "Bearer",
        "expires_at": "2024-08-28T10:00:00.000000Z"
    }
}
```

### 2. Using Protected Endpoints
```http
GET /api/v1/dashboard
Authorization: Bearer 1|abc123...
```

### 3. Token Refresh
```http
POST /api/v1/auth/refresh
Authorization: Bearer 1|abc123...
```

### 4. Logout
```http
POST /api/v1/auth/logout
Authorization: Bearer 1|abc123...
```

## 🚨 Security Best Practices

### For Frontend Applications

1. **Token Storage**: Store tokens securely (HttpOnly cookies or secure storage)
2. **Token Refresh**: Implement automatic token refresh before expiration
3. **Error Handling**: Handle 401/403 responses gracefully
4. **HTTPS Only**: Always use HTTPS in production

### For API Consumers

1. **Token Security**: Never expose tokens in client-side code
2. **Request Headers**: Always include Authorization header
3. **Error Handling**: Implement proper error handling for auth failures
4. **Rate Limiting**: Respect API rate limits

## 🔍 Security Monitoring

### Logging
- All authentication attempts are logged
- Failed login attempts are tracked
- Token usage is monitored

### Alerts
- Multiple failed login attempts trigger alerts
- Unusual token usage patterns are flagged
- Suspicious IP addresses are monitored

## 🛠️ Troubleshooting

### Common Issues

1. **Token Expired**
   - Error: 401 Unauthorized
   - Solution: Refresh token or re-authenticate

2. **Rate Limited**
   - Error: 429 Too Many Requests
   - Solution: Wait 15 minutes or contact admin

3. **Invalid Token**
   - Error: 401 Unauthorized
   - Solution: Check token format and validity

4. **Account Inactive**
   - Error: 403 Forbidden
   - Solution: Contact administrator

## 📞 Support

For security-related issues or questions:
- Contact: admin@vendorconnect.com
- Emergency: +1-800-SECURITY
- Documentation: https://docs.vendorconnect.com/api/security
