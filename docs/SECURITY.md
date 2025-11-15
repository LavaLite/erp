# Security Features - Comprehensive Guide

> **Complete security implementation guide for the Multi-Organization Authorization Microservice**

## Table of Contents

1. [Email Verification](#email-verification)
2. [Password Reset](#password-reset)
3. [Two-Factor Authentication (2FA)](#two-factor-authentication-2fa)
4. [Rate Limiting](#rate-limiting)
5. [JWT Security](#jwt-security)
6. [Best Practices](#best-practices)
7. [Security Checklist](#security-checklist)

---

## Email Verification

### Overview

All new user registrations trigger automatic email verification. Users receive a secure verification link valid for 24 hours.

### Features

- ✅ **Secure Token Generation**: Cryptographically random tokens (256-bit)
- ✅ **Hashed Storage**: Tokens stored as SHA-256 hashes
- ✅ **Time-Limited**: 24-hour validity period
- ✅ **Rate Limited**: Maximum 3 resend attempts per minute
- ✅ **Single Use**: Tokens invalidated after successful verification

### API Endpoints

#### 1. Send Verification Email (Authenticated)

```http
POST /api/email/send-verification
Authorization: Bearer {jwt_token}
```

**Success Response (200):**
```json
{
  "message": "Verification email sent successfully."
}
```

**Rate Limit:** 3 requests per minute per user

---

#### 2. Verify Email

```http
POST /api/email/verify
Content-Type: application/json

{
  "email": "user@example.com",
  "token": "64-character-hex-token"
}
```

**Success Response (200):**
```json
{
  "message": "Email verified successfully."
}
```

**Error Responses:**

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Invalid or expired verification token | Token is invalid, expired, or already used |
| 404 | User not found | Email address not registered |
| 422 | Validation error | Missing or invalid request data |

**Rate Limit:** 10 requests per minute per IP

---

#### 3. Resend Verification Email

```http
POST /api/email/resend
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Success Response (200):**
```json
{
  "message": "Verification email resent successfully."
}
```

**Error Responses:**

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Email already verified | User has already verified their email |
| 404 | User not found | Email address not registered |
| 429 | Too many attempts | Rate limit exceeded |

**Rate Limit:** 3 requests per minute per email

---

### Email Template

Verification emails include:

- ✉️ **Clickable Link**: One-click verification
- ⏰ **Expiration Notice**: Clear 24-hour deadline
- 🔒 **Security Notice**: Warning about unsolicited emails
- 🎨 **Branding**: Company logo and colors

**Example Email:**

```
Subject: Verify Your Email Address

Hi John,

Welcome to Lavalite! Please verify your email address by clicking the link below:

[Verify Email Address]

This link will expire in 24 hours.

If you didn't create this account, please ignore this email.

Thanks,
The Lavalite Team
```

### Implementation Details

**Token Generation:**
```php
public function generateEmailVerificationToken(): string
{
    $token = Str::random(64);
    $this->email_verification_token = hash('sha256', $token);
    $this->email_verification_token_expires_at = now()->addHours(24);
    $this->save();
    
    return $token; // Return unhashed token for email
}
```

**Verification:**
```php
public function verifyEmail(string $token): bool
{
    if ($this->email_verification_token !== hash('sha256', $token)) {
        return false;
    }
    
    if ($this->email_verification_token_expires_at < now()) {
        return false;
    }
    
    $this->email_verified_at = now();
    $this->email_verification_token = null;
    $this->email_verification_token_expires_at = null;
    $this->save();
    
    return true;
}
```

---

## Password Reset

### Overview

Secure password reset flow using Laravel's built-in Password facade with time-limited tokens and email-only delivery.

### Features

- ✅ **Secure Tokens**: Cryptographically random, hashed tokens
- ✅ **Time-Limited**: 1-hour validity period
- ✅ **Single Use**: Tokens automatically invalidated after use
- ✅ **Email Delivery**: Reset links sent only to registered email
- ✅ **No User Enumeration**: Consistent responses for security
- ✅ **Rate Limited**: Protection against abuse

### API Endpoints

#### 1. Request Password Reset Link

```http
POST /api/password/forgot
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Success Response (200):**
```json
{
  "message": "If your email is registered, you will receive a password reset link."
}
```

**Security Note:** Always returns 200 to prevent email enumeration attacks.

**Rate Limit:** 3 requests per minute per IP

---

#### 2. Reset Password

```http
POST /api/password/reset
Content-Type: application/json

{
  "email": "user@example.com",
  "token": "reset-token-from-email",
  "password": "NewSecurePassword123!",
  "password_confirmation": "NewSecurePassword123!"
}
```

**Success Response (200):**
```json
{
  "message": "Password reset successfully."
}
```

**Error Responses:**

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Invalid or expired password reset token | Token is invalid or has expired (> 1 hour) |
| 422 | Password validation failed | Password doesn't meet requirements |
| 422 | Passwords do not match | password and password_confirmation differ |

**Password Requirements:**
- Minimum 8 characters
- Must contain: uppercase, lowercase, number, special character (recommended)
- Cannot be common password (optional enhancement)

**Rate Limit:** 5 requests per minute per IP

---

### Email Template

Password reset emails include:

- 🔗 **Secure Link**: Time-limited reset link
- ⏰ **Expiration**: Clear 1-hour deadline
- ⚠️ **Security Warning**: Instructions if not requested
- 📧 **Contact Support**: Help desk information

**Example Email:**

```
Subject: Password Reset Request

Hi John,

We received a request to reset your password. Click the link below to reset it:

[Reset Password]

This link will expire in 1 hour.

If you didn't request this, please ignore this email or contact support.

Thanks,
The Lavalite Team
```

### Security Flow

```
┌─────────────┐
│ User clicks │
│ "Forgot"    │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│ Email validated │
│ (no disclosure) │
└──────┬──────────┘
       │
       ▼
┌──────────────────┐
│ Token generated  │
│ & emailed        │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ User clicks link │
│ (1 hour window)  │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ New password set │
│ Token invalidated│
└──────────────────┘
```

---

## Two-Factor Authentication (2FA)

### Overview

TOTP-based (Time-based One-Time Password) two-factor authentication compatible with Google Authenticator, Authy, Microsoft Authenticator, and any RFC 6238 compliant app.

### Features

- ✅ **TOTP Standard**: RFC 6238 compliant
- ✅ **QR Code Setup**: Easy scanning with authenticator apps
- ✅ **Recovery Codes**: 8 single-use backup codes
- ✅ **Encrypted Storage**: 2FA secrets encrypted at rest
- ✅ **Optional Enforcement**: Can be made mandatory per organization
- ✅ **Account Lockout**: Protection against brute force

### API Endpoints

#### 1. Enable Two-Factor Authentication

```http
POST /api/2fa/enable
Authorization: Bearer {jwt_token}
```

**Success Response (200):**
```json
{
  "secret": "JBSWY3DPEHPK3PXP",
  "qr_code_svg": "<svg xmlns='http://www.w3.org/2000/svg'>...</svg>",
  "recovery_codes": [
    "a1b2-c3d4-e5f6",
    "g7h8-i9j0-k1l2",
    "m3n4-o5p6-q7r8",
    "s9t0-u1v2-w3x4",
    "y5z6-a7b8-c9d0",
    "e1f2-g3h4-i5j6",
    "k7l8-m9n0-o1p2",
    "q3r4-s5t6-u7v8"
  ],
  "message": "Two-factor authentication enabled. Save your recovery codes in a safe place."
}
```

**Error Responses:**

| Status | Error | Description |
|--------|-------|-------------|
| 400 | 2FA already enabled | User already has 2FA active |
| 401 | Unauthorized | Invalid or expired JWT token |

**⚠️ Critical:** Users must save recovery codes immediately. They won't be shown again!

---

#### 2. Confirm Two-Factor Setup

```http
POST /api/2fa/confirm
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
  "code": "123456"
}
```

**Success Response (200):**
```json
{
  "message": "Two-factor authentication confirmed successfully."
}
```

**Error Responses:**

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Invalid code | TOTP code is incorrect |
| 400 | 2FA not pending | User hasn't called /enable first |
| 401 | Unauthorized | Invalid or expired JWT token |

---

#### 3. Disable Two-Factor Authentication

```http
POST /api/2fa/disable
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
  "password": "currentPassword123"
}
```

**Success Response (200):**
```json
{
  "message": "Two-factor authentication disabled successfully."
}
```

**Error Responses:**

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Invalid password | Password verification failed |
| 400 | 2FA not enabled | User doesn't have 2FA active |
| 401 | Unauthorized | Invalid or expired JWT token |

**🔒 Security:** Requires password re-authentication to prevent unauthorized disabling.

---

#### 4. Verify 2FA Code (During Login)

```http
POST /api/2fa/verify
Content-Type: application/json

{
  "email": "user@example.com",
  "code": "123456"
}
```

**Alternative with Recovery Code:**
```json
{
  "email": "user@example.com",
  "code": "a1b2-c3d4-e5f6"
}
```

**Success Response (200):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Error Responses:**

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Invalid 2FA code | Code is incorrect or expired |
| 401 | Invalid credentials | Email not found or no 2FA pending |
| 429 | Too many attempts | Rate limit exceeded (5 per minute) |

**Rate Limit:** 5 requests per minute per email

---

#### 5. Regenerate Recovery Codes

```http
POST /api/2fa/recovery-codes
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
  "password": "currentPassword123"
}
```

**Success Response (200):**
```json
{
  "recovery_codes": [
    "new1-code-here",
    "new2-code-here",
    "new3-code-here",
    "new4-code-here",
    "new5-code-here",
    "new6-code-here",
    "new7-code-here",
    "new8-code-here"
  ],
  "message": "Recovery codes regenerated successfully. Old codes are now invalid."
}
```

**Error Responses:**

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Invalid password | Password verification failed |
| 400 | 2FA not enabled | User doesn't have 2FA active |
| 401 | Unauthorized | Invalid or expired JWT token |

**⚠️ Warning:** 
- Old recovery codes are immediately invalidated
- Save new codes before closing
- Requires password confirmation

---

### Complete Login Flow with 2FA

```
┌──────────────┐
│ 1. POST      │
│ /api/login   │
│ (email+pass) │
└──────┬───────┘
       │
       ▼
  ┌─────────┐
  │ 2FA     │ No
  │ enabled?├────────────┐
  └────┬────┘            │
       │ Yes             │
       ▼                 ▼
┌──────────────┐   ┌───────────┐
│ Return:      │   │ Return:   │
│ requires_2fa │   │ JWT token │
│ = true       │   └───────────┘
└──────┬───────┘
       │
       ▼
┌──────────────┐
│ 3. POST      │
│ /api/2fa/    │
│ verify       │
│ (email+code) │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│ Return:      │
│ JWT token    │
└──────────────┘
```

### Setup Guide for End Users

**Step 1: Enable 2FA**
```bash
curl -X POST https://api.example.com/api/2fa/enable \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Step 2: Scan QR Code**
- Open authenticator app (Google Authenticator, Authy, etc.)
- Scan the QR code from the response
- Or manually enter the secret key

**Step 3: Save Recovery Codes**
- Download or print the 8 recovery codes
- Store in a secure location
- Use them if you lose access to your authenticator app

**Step 4: Confirm Setup**
```bash
curl -X POST https://api.example.com/api/2fa/confirm \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"code":"123456"}'
```

**Step 5: Login with 2FA**
```bash
# Regular login
curl -X POST https://api.example.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Response indicates 2FA required
# {"requires_2fa": true}

# Verify with 2FA code
curl -X POST https://api.example.com/api/2fa/verify \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","code":"123456"}'
```

### Recovery Code Usage

**When to Use:**
- Lost or broken phone
- Authenticator app uninstalled
- New device without backup

**How to Use:**
1. Start normal login process
2. When prompted for 2FA code, use recovery code instead
3. Each recovery code works only once
4. Generate new codes when you have <3 remaining

**Format:** `xxxx-xxxx-xxxx` (12 characters with dashes)

### Technical Implementation

**Libraries Used:**
- `pragmarx/google2fa` (v9.0.0) - TOTP generation & verification
- `bacon/bacon-qr-code` (v3.0.1) - QR code generation

**Encryption:**
```php
// 2FA secret encrypted in database
$user->two_factor_secret = encrypt($secret);

// Retrieve and decrypt
$secret = decrypt($user->two_factor_secret);
```

**TOTP Algorithm:**
- **Time Step:** 30 seconds
- **Window:** ±1 (accepts codes from 30s before/after)
- **Digits:** 6
- **Algorithm:** SHA1

---

## Rate Limiting

### Overview

Comprehensive rate limiting protects against brute force attacks, credential stuffing, and API abuse through a multi-tier strategy.

### Rate Limit Tiers

| Endpoint Type | Limit | Window | Identifier |
|---------------|-------|--------|------------|
| **Authentication** ||||
| Login | 5 attempts | 1 minute | IP + Email |
| Registration | 5 requests | 1 minute | IP |
| 2FA Verify | 5 attempts | 1 minute | Email |
| **Password Management** ||||
| Reset Request | 3 requests | 1 minute | IP |
| Reset Confirm | 5 attempts | 1 minute | IP |
| **Email Verification** ||||
| Send Verification | 3 requests | 1 minute | User ID |
| Verify Email | 10 requests | 1 minute | IP |
| Resend Verification | 3 requests | 1 minute | Email |
| **General API** ||||
| Authenticated | 60 requests | 1 minute | User ID |
| Public | 60 requests | 1 minute | IP |

### Custom Throttle Middleware

**File:** `app/Http/Middleware/ThrottleAuthAttempts.php`

**Features:**
- ✅ Combined IP + email throttling
- ✅ Automatic cooldown period
- ✅ Cleared attempts on successful login
- ✅ Custom error messages
- ✅ Distributed rate limiting (Redis support)

**Example Response (429):**
```json
{
  "error": "Too many login attempts. Please try again in 60 seconds.",
  "retry_after": 60
}
```

### Route Configuration

```php
// routes/api.php

// Custom auth throttle (5 per minute)
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle.auth');

// Standard throttle with inline config
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('throttle:3,1'); // 3 requests per 1 minute

// Named rate limiter
Route::middleware(['throttle:api'])->group(function () {
    // 60 requests per minute
    Route::get('/users', [UserController::class, 'index']);
});
```

### Configuration

**In `bootstrap/app.php`:**
```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->input('email').$request->ip());
});
```

### Bypass for Testing

**Option 1: Environment Variable**
```env
# .env.testing
THROTTLE_ENABLED=false
```

**Option 2: Service Account**
```php
// Exclude specific users
RateLimiter::for('api', function (Request $request) {
    if ($request->user()?->is_service_account) {
        return Limit::none();
    }
    return Limit::perMinute(60);
});
```

### Monitoring & Logging

```php
// Log throttled requests
Log::warning('Rate limit exceeded', [
    'ip' => $request->ip(),
    'email' => $request->input('email'),
    'endpoint' => $request->path(),
    'user_agent' => $request->userAgent()
]);

// Metrics tracking
Metrics::increment('rate_limit.exceeded', [
    'endpoint' => $request->path()
]);
```

### Headers

**Response Headers:**
```http
HTTP/1.1 200 OK
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699564800
```

**429 Response:**
```http
HTTP/1.1 429 Too Many Requests
Retry-After: 60
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1699564860

{
  "error": "Too many requests. Please try again in 60 seconds."
}
```

---

## JWT Security

### Overview

JWT (JSON Web Tokens) provide stateless authentication with embedded claims for user identity, roles, and permissions.

### Features

- ✅ **Stateless Authentication**: No server-side session storage
- ✅ **Refresh Tokens**: Long-lived tokens for obtaining new access tokens
- ✅ **Role/Permission Embedding**: Claims include user's roles and permissions
- ✅ **Organization Context**: Current organization embedded in token
- ✅ **Expiration**: Short-lived access tokens (1 hour default)
- ✅ **Algorithm**: HS256 (HMAC with SHA-256)

### Token Structure

```json
{
  "header": {
    "typ": "JWT",
    "alg": "HS256"
  },
  "payload": {
    "iss": "https://api.example.com",
    "sub": "1",
    "iat": 1699564800,
    "exp": 1699568400,
    "organization_id": "019a77ec-851a-7028-8f56-5f31232cdf72",
    "roles": ["admin", "editor"],
    "permissions": ["view-users", "edit-posts", "delete-comments"]
  },
  "signature": "..."
}
```

### Configuration

**In `.env`:**
```env
JWT_SECRET=base64:your-secret-key-here
JWT_TTL=60                    # Access token TTL in minutes (1 hour)
JWT_REFRESH_TTL=20160         # Refresh token TTL in minutes (14 days)
JWT_ALGO=HS256                # Signing algorithm
JWT_LEEWAY=0                  # Leeway for clock skew (seconds)
```

**Generate Secret:**
```bash
php artisan jwt:secret
```

### Token Lifecycle

```
┌─────────────┐
│ Login       │
│ /api/login  │
└──────┬──────┘
       │
       ▼
┌────────────────────┐
│ Access Token (1h)  │
│ Refresh Token (14d)│
└──────┬─────────────┘
       │
       ▼ (after 1 hour)
┌─────────────────┐
│ /api/refresh    │
│ (refresh token) │
└──────┬──────────┘
       │
       ▼
┌────────────────┐
│ New Access     │
│ Token (1h)     │
└──────┬─────────┘
       │
       ▼ (when done)
┌─────────────┐
│ /api/logout │
│ (blacklist) │
└─────────────┘
```

### Best Practices

1. **Short Access Token TTL**: Keep access tokens short-lived (1 hour)
2. **Secure Refresh Tokens**: Store refresh tokens securely (HttpOnly cookies recommended)
3. **Rotate Secrets**: Rotate JWT secrets periodically
4. **Blacklist on Logout**: Invalidate tokens on logout
5. **HTTPS Only**: Always use HTTPS in production
6. **Don't Store Sensitive Data**: Minimize PII in JWT payload

### Token Validation

**Automatic validation includes:**
- ✅ Signature verification
- ✅ Expiration check
- ✅ Not-before check
- ✅ Issuer verification
- ✅ Blacklist check (on logout)

---

## Best Practices

### General Security

1. **Always Use HTTPS**
   - Encrypt all traffic in production
   - Use SSL/TLS certificates from trusted CA
   - Enable HSTS (HTTP Strict Transport Security)

2. **Environment Variables**
   - Never commit `.env` to version control
   - Use different secrets for dev/staging/production
   - Rotate secrets regularly

3. **Database Security**
   - Use parameterized queries (Laravel does this by default)
   - Encrypt sensitive data at rest
   - Regular backups with encryption

4. **Input Validation**
   - Validate all user input
   - Use Laravel's validation rules
   - Sanitize output to prevent XSS

5. **Error Handling**
   - Don't expose stack traces in production
   - Log errors securely
   - Use generic error messages for users

### Authentication Best Practices

1. **Password Requirements**
   ```php
   'password' => ['required', 'string', 'min:8', 'confirmed', 
                  'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/']
   ```

2. **Account Lockout**
   - Implement progressive delays
   - Lock after 5 failed attempts
   - Require email verification to unlock

3. **Session Management**
   - Regenerate session ID on login
   - Timeout inactive sessions
   - Single sign-out across devices

4. **Multi-Factor Authentication**
   - Encourage (or require) 2FA for sensitive operations
   - Provide backup codes
   - Allow SMS fallback (optional)

### API Security

1. **Rate Limiting**
   - Apply to all endpoints
   - Use different limits for different tiers
   - Monitor and adjust based on usage

2. **CORS Configuration**
   ```php
   // config/cors.php
   'paths' => ['api/*'],
   'allowed_origins' => ['https://app.example.com'],
   'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
   'allowed_headers' => ['Content-Type', 'Authorization', 'X-Organization-ID'],
   ```

3. **Request Size Limits**
   ```php
   // Limit request body size
   'max_input_vars' => 1000,
   'post_max_size' => '20M',
   'upload_max_filesize' => '10M'
   ```

4. **API Versioning**
   - Version your API (v1, v2)
   - Maintain backward compatibility
   - Deprecate old versions gracefully

### Monitoring & Logging

1. **Log Security Events**
   ```php
   Log::warning('Failed login attempt', [
       'email' => $email,
       'ip' => $request->ip(),
       'user_agent' => $request->userAgent()
   ]);
   ```

2. **Monitor for Anomalies**
   - Unusual login patterns
   - Multiple failed 2FA attempts
   - API abuse patterns
   - Geolocation anomalies

3. **Audit Trail**
   - Log all authentication events
   - Track permission changes
   - Record admin actions
   - Store logs securely with retention policy

---

## Security Checklist

### Pre-Launch

- [ ] **SSL/TLS configured** and enforced
- [ ] **Environment variables** secured and rotated
- [ ] **Debug mode disabled** in production
- [ ] **Error reporting** configured (no stack traces to users)
- [ ] **Database credentials** secured
- [ ] **JWT secrets** generated and secured
- [ ] **CORS** properly configured
- [ ] **Rate limiting** enabled on all endpoints
- [ ] **Email verification** required for new accounts
- [ ] **2FA available** for users
- [ ] **Password requirements** enforced
- [ ] **Account lockout** implemented
- [ ] **Logging** configured and tested
- [ ] **Backup strategy** in place
- [ ] **Monitoring** alerts configured

### Regular Maintenance

- [ ] **Rotate JWT secrets** (quarterly)
- [ ] **Review logs** for suspicious activity (weekly)
- [ ] **Update dependencies** (monthly)
- [ ] **Security patches** applied promptly
- [ ] **Backup verification** (monthly)
- [ ] **Access audit** (quarterly)
- [ ] **Penetration testing** (annually)
- [ ] **Security training** for team (ongoing)

### Incident Response

- [ ] **Incident response plan** documented
- [ ] **Contact list** updated
- [ ] **Backup restoration** tested
- [ ] **Communication templates** prepared
- [ ] **Forensics tools** available
- [ ] **Legal requirements** understood

---

## Testing

### Unit Tests

Run security-related tests:
```bash
php artisan test --testsuite=Feature --filter=Security
```

### Integration Tests

Test complete flows:
```bash
# Email verification flow
php artisan test --filter=EmailVerificationTest

# Password reset flow
php artisan test --filter=PasswordResetTest

# 2FA flow
php artisan test --filter=TwoFactorAuthenticationTest
```

### Manual Security Testing

1. **Test Rate Limiting**
   ```bash
   for i in {1..10}; do 
     curl -X POST https://api.example.com/api/login \
       -d '{"email":"test@example.com","password":"wrong"}'
   done
   ```

2. **Test JWT Expiration**
   ```bash
   # Wait for token to expire (> 1 hour)
   curl -X GET https://api.example.com/api/user \
     -H "Authorization: Bearer EXPIRED_TOKEN"
   ```

3. **Test 2FA Lockout**
   ```bash
   # Try invalid 2FA codes multiple times
   for i in {1..10}; do
     curl -X POST https://api.example.com/api/2fa/verify \
       -d '{"email":"test@example.com","code":"000000"}'
   done
   ```

---

## Support & Troubleshooting

### Common Issues

**1. "Invalid JWT token"**
- Token may be expired (check expiration)
- Secret key may have changed
- Token format incorrect

**2. "Too many attempts"**
- Rate limit exceeded
- Wait for cooldown period
- Check rate limit configuration

**3. "Email not verified"**
- User hasn't clicked verification link
- Token may have expired (24 hours)
- Resend verification email

**4. "Invalid 2FA code"**
- Code may have expired (30-second window)
- Clock synchronization issue
- User hasn't confirmed 2FA setup

### Getting Help

- 📚 **Documentation**: https://docs.example.com
- 💬 **Community**: https://community.example.com
- 🐛 **Bug Reports**: https://github.com/example/issues
- 📧 **Email Support**: support@example.com

---

## License

MIT License - See LICENSE file for details.

---

**Last Updated:** November 13, 2025  
**Version:** 1.3.0
