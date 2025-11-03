# Session-Based Authentication with Cookie Persistence

## Overview
The application now uses Laravel's built-in session-based authentication with secure HTTP-only cookies. Sessions persist across browser tabs and remain active until logout or expiration.

## How It Works

### 1. **Login Process**
- User submits credentials via login form
- Backend validates credentials using `Auth::attempt()`
- If valid, Laravel:
  - Creates a session in the database (using `sessions` table)
  - Sets a session cookie (`laravel_session`) in the browser
  - Cookie is HTTP-only (prevents JavaScript access for security)
  - Cookie uses `SameSite=Lax` (allows cross-tab persistence)
  - Cookie path is `/` (available across entire domain)

### 2. **Remember Me Feature**
- When "Remember Me" is checked:
  - Laravel sets an additional `remember_me` cookie
  - This cookie has a longer expiration (default: 2 weeks)
  - User stays logged in even after closing browser (if remember me is checked)
- Without "Remember Me":
  - Session expires after 120 minutes of inactivity (configurable)
  - Or when browser is closed (if `expire_on_close` is enabled)

### 3. **Session Persistence Across Tabs**
- The session cookie is automatically sent with every request
- Opening a new tab/window on the same domain will:
  - Automatically include the session cookie
  - Allow access to protected routes
  - Keep user logged in across all tabs

### 4. **Authentication Verification**
- **Server-side**: `auth` middleware protects all admin routes
- **Client-side**: JavaScript checks `/auth/check` endpoint on page load
- Both work together to ensure security

## Configuration

### Session Settings (config/session.php)
```php
'driver' => 'database',           // Stores sessions in database
'lifetime' => 120,                 // 120 minutes
'expire_on_close' => false,        // Session persists after browser close (if remember me)
'http_only' => true,               // Cookie not accessible via JavaScript (secure)
'same_site' => 'lax',             // Allows cross-tab sharing
'path' => '/',                     // Available across entire domain
```

### Cookie Security
- **HttpOnly**: `true` - Prevents XSS attacks
- **SameSite**: `lax` - Prevents CSRF while allowing normal navigation
- **Secure**: Environment-based - Set to `true` in production (HTTPS)

## Endpoints

### `/login` (GET)
- Shows login form
- If already authenticated, redirects to dashboard

### `/login` (POST) 
- Validates credentials
- Creates session and sets cookie
- Redirects to dashboard on success

### `/auth/check` (GET)
- Returns JSON with authentication status
- Used by JavaScript to verify session is active
- Accessible without authentication

### `/logout` (POST)
- Destroys session
- Clears session cookie
- Redirects to login page

## Features Implemented

✅ **Session Cookies**: Automatically set and managed by Laravel  
✅ **Remember Me**: Extends session lifetime when checked  
✅ **Cross-Tab Persistence**: Sessions work across all browser tabs  
✅ **Automatic Verification**: JavaScript checks auth status on page load  
✅ **Auto-Redirect**: Logged-in users redirected away from login page  
✅ **Secure Cookies**: HTTP-only, SameSite protection enabled  

## Testing

### Test Session Persistence:
1. Log in at `/login`
2. Open a new tab and navigate to `/dashboard`
3. You should automatically be logged in (no need to login again)
4. Session cookie is sent automatically with the request

### Test Remember Me:
1. Log in with "Remember Me" checked
2. Close browser completely
3. Reopen browser and navigate to `/dashboard`
4. You should still be logged in (session persisted)

### Test Logout:
1. Click logout
2. Try accessing `/dashboard`
3. Should redirect to `/login`

## Troubleshooting

### Session not persisting across tabs?
- Check browser settings - ensure cookies are enabled
- Check domain/path settings in `config/session.php`
- Verify `SESSION_DRIVER` is set to `database` in `.env`
- Run `php artisan migrate` to ensure `sessions` table exists

### Remember me not working?
- Check that `remember` checkbox value is being sent
- Verify Laravel's remember token is stored in `users` table
- Check browser is not blocking cookies

### Session expires too quickly?
- Adjust `SESSION_LIFETIME` in `.env` (in minutes)
- Check `expire_on_close` setting

## Database Requirements

Ensure the `sessions` table exists:
```bash
php artisan migrate
```

The sessions table is created automatically by Laravel migrations.

