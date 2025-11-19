# Social Login Configuration

This application supports flexible social login with multiple OAuth providers.

## Features

- ✅ **JSON-based storage**: All social account IDs stored in a single `social_accounts` JSON field
- ✅ **Config-driven providers**: Enable/disable providers via config file
- ✅ **Auto email verification**: Social login emails are automatically verified
- ✅ **Account linking**: Link multiple social accounts to one email
- ✅ **Easy to extend**: Add new providers by editing config

## Architecture

### Database Structure
```php
// users table
'social_accounts' => [
    'google' => '1234567890',
    'microsoft' => 'abcdef-12345',
    // ... more providers
]
```

### Configuration File
Social login providers are configured in `config/social.php`:

```php
'providers' => [
    'google' => [
        'enabled' => env('GOOGLE_LOGIN_ENABLED', true),
        'label' => 'Google',
        'icon' => 'google',
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],
]
```

## Enabled Providers

- **Google OAuth 2.0** ✅ Built-in
- **Microsoft OAuth 2.0** ✅ Configured (via SocialiteProviders)

## Supported Providers

Laravel Socialite supports many OAuth providers. Below are popular options you can add:

### Built-in Providers (No extra package needed)
- **Google** - Already configured
- **Facebook**
- **GitHub**
- **GitLab**
- **Bitbucket**

### Configured Providers (Already installed)
- **Microsoft** - Already configured via SocialiteProviders

### Additional Providers (via SocialiteProviders)
Install via: `composer require socialiteproviders/[provider]`

**Popular Social Networks:**
- Twitter/X - `socialiteproviders/twitter`
- LinkedIn - `socialiteproviders/linkedin`
- Instagram - `socialiteproviders/instagram`
- Discord - `socialiteproviders/discord`
- Slack - `socialiteproviders/slack`
- Reddit - `socialiteproviders/reddit`
- Snapchat - `socialiteproviders/snapchat`
- TikTok - `socialiteproviders/tiktok`
- Twitch - `socialiteproviders/twitch`

**Developer Platforms:**
- Apple - `socialiteproviders/apple`
- Atlassian - `socialiteproviders/atlassian`
- Azure - `socialiteproviders/azure`
- DigitalOcean - `socialiteproviders/digitalocean`
- Dropbox - `socialiteproviders/dropbox`
- Stripe - `socialiteproviders/stripe`

**Enterprise/Business:**
- Okta - `socialiteproviders/okta`
- Salesforce - `socialiteproviders/salesforce`
- Zoom - `socialiteproviders/zoom`
- Microsoft Teams - `socialiteproviders/microsoft-graph`
- Auth0 - `socialiteproviders/auth0`

**Other Popular Services:**
- Spotify - `socialiteproviders/spotify`
- Steam - `socialiteproviders/steam`
- Shopify - `socialiteproviders/shopify`
- WordPress - `socialiteproviders/wordpress`
- Yahoo - `socialiteproviders/yahoo`

**Complete list:** https://socialiteproviders.com/

## Setup Instructions

### 1. Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Create a new project or select existing
3. Enable Google+ API
4. Create OAuth 2.0 Client ID credentials
5. Add authorized redirect URI: `http://localhost:8000/auth/google/callback` (or your production URL)
6. Copy Client ID and Client Secret to `.env`:
   ```env
   GOOGLE_CLIENT_ID=your_client_id_here
   GOOGLE_CLIENT_SECRET=your_client_secret_here
   ```

### 2. Microsoft OAuth Setup

1. Go to [Azure Portal - App Registrations](https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade)
2. Click "New registration"
3. Enter application name
4. Set redirect URI: `http://localhost:8000/auth/microsoft/callback`
5. After creation, go to "Certificates & secrets"
6. Create a new client secret
7. Copy Application (client) ID and secret to `.env`:
   ```env
   MICROSOFT_CLIENT_ID=your_application_id_here
   MICROSOFT_CLIENT_SECRET=your_client_secret_here
   ```

### 3. GitHub OAuth Setup

1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Click "New OAuth App"
3. Fill in:
   - Application name: Your App Name
   - Homepage URL: `http://localhost:8000`
   - Authorization callback URL: `http://localhost:8000/auth/github/callback`
4. Copy Client ID and generate a Client Secret
5. Add to `.env`:
   ```env
   GITHUB_LOGIN_ENABLED=true
   GITHUB_CLIENT_ID=your_github_client_id
   GITHUB_CLIENT_SECRET=your_github_client_secret
   ```

### 4. Facebook OAuth Setup

1. Go to [Facebook Developers](https://developers.facebook.com/apps/)
2. Create a new app → Select "Consumer" type
3. Go to Settings → Basic
4. Copy App ID and App Secret
5. Add Facebook Login product
6. Under Facebook Login → Settings, add redirect URI: `http://localhost:8000/auth/facebook/callback`
7. Add to `.env`:
   ```env
   FACEBOOK_LOGIN_ENABLED=true
   FACEBOOK_CLIENT_ID=your_facebook_app_id
   FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
   ```

### 5. LinkedIn OAuth Setup

1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/apps)
2. Create a new app
3. Go to "Auth" tab
4. Add redirect URL: `http://localhost:8000/auth/linkedin-openid/callback`
5. Request access to "Sign In with LinkedIn using OpenID Connect"
6. Copy Client ID and Client Secret
7. Add to `.env`:
   ```env
   LINKEDIN_LOGIN_ENABLED=true
   LINKEDIN_CLIENT_ID=your_linkedin_client_id
   LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret
   ```

### 6. Twitter/X OAuth Setup

1. Go to [Twitter Developer Portal](https://developer.twitter.com/en/portal/dashboard)
2. Create a new project and app
3. Go to app settings → User authentication settings
4. Enable OAuth 2.0
5. Add callback URL: `http://localhost:8000/auth/twitter/callback`
6. Copy Client ID and Client Secret
7. Add to `.env`:
   ```env
   TWITTER_LOGIN_ENABLED=true
   TWITTER_CLIENT_ID=your_twitter_client_id
   TWITTER_CLIENT_SECRET=your_twitter_client_secret
   ```

### 7. Apple Sign In Setup

1. Go to [Apple Developer Portal](https://developer.apple.com/account/)
2. Create an App ID with "Sign In with Apple" capability
3. Create a Service ID
4. Configure "Sign In with Apple" for the Service ID
5. Add return URL: `http://localhost:8000/auth/apple/callback`
6. Create a private key for Sign In with Apple
7. More details: https://socialiteproviders.com/Apple/

### 8. Discord OAuth Setup

1. Go to [Discord Developer Portal](https://discord.com/developers/applications)
2. Create a new application
3. Go to OAuth2 settings
4. Add redirect: `http://localhost:8000/auth/discord/callback`
5. Copy Client ID and Client Secret
6. Install provider: `composer require socialiteproviders/discord`

### 9. GitLab OAuth Setup

1. Go to your GitLab instance → User Settings → Applications
2. For GitLab.com: https://gitlab.com/-/profile/applications
3. Add redirect URI: `http://localhost:8000/auth/gitlab/callback`
4. Select scopes: `read_user`, `email`
5. Copy Application ID and Secret

### 10. Slack OAuth Setup

1. Go to [Slack API Apps](https://api.slack.com/apps)
2. Create a new app
3. Go to OAuth & Permissions
4. Add redirect URL: `http://localhost:8000/auth/slack/callback`
5. Add OAuth scopes: `identity.basic`, `identity.email`
6. Install provider: `composer require socialiteproviders/slack`

## Provider Setup Guides

**Official Documentation:**
- Laravel Socialite: https://laravel.com/docs/11.x/socialite
- SocialiteProviders: https://socialiteproviders.com/
- Google OAuth: https://console.cloud.google.com/
- Microsoft Azure: https://portal.azure.com/
- GitHub Apps: https://github.com/settings/developers
- Facebook Apps: https://developers.facebook.com/
- LinkedIn Apps: https://www.linkedin.com/developers/
- Twitter/X Apps: https://developer.twitter.com/
- Apple Developer: https://developer.apple.com/

**Quick Reference - Callback URLs:**
```
Google:     http://localhost:8000/auth/google/callback
Microsoft:  http://localhost:8000/auth/microsoft/callback
GitHub:     http://localhost:8000/auth/github/callback
Facebook:   http://localhost:8000/auth/facebook/callback
LinkedIn:   http://localhost:8000/auth/linkedin-openid/callback
Twitter:    http://localhost:8000/auth/twitter/callback
Apple:      http://localhost:8000/auth/apple/callback
Discord:    http://localhost:8000/auth/discord/callback
GitLab:     http://localhost:8000/auth/gitlab/callback
Slack:      http://localhost:8000/auth/slack/callback
```

## Environment Variables

```env
# Enable/disable providers
GOOGLE_LOGIN_ENABLED=true
MICROSOFT_LOGIN_ENABLED=true

# Google credentials
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback

# Microsoft credentials
MICROSOFT_CLIENT_ID=
MICROSOFT_CLIENT_SECRET=
MICROSOFT_REDIRECT_URL=http://localhost:8000/auth/microsoft/callback

# Options
SOCIAL_LOGIN_AUTO_VERIFY_EMAIL=true
SOCIAL_LOGIN_ALLOW_LINKING=true
```

## Adding New Providers

### Example: Adding GitHub

1. **GitHub already works with Laravel Socialite** (no extra package needed):
   ```bash
   # No installation needed - GitHub is built-in
   ```

2. **Update `config/social.php`**:
   ```php
   'github' => [
       'enabled' => env('GITHUB_LOGIN_ENABLED', false),
       'label' => 'GitHub',
       'icon' => 'github',
       'client_id' => env('GITHUB_CLIENT_ID'),
       'client_secret' => env('GITHUB_CLIENT_SECRET'),
       'redirect' => env('GITHUB_REDIRECT_URL', env('APP_URL') . '/auth/github/callback'),
   ],
   ```

3. **Add to `.env`**:
   ```env
   GITHUB_LOGIN_ENABLED=true
   GITHUB_CLIENT_ID=your_github_client_id
   GITHUB_CLIENT_SECRET=your_github_client_secret
   ```

4. **Add to `config/services.php`**:
   ```php
   'github' => config('social-login.providers.github'),
   ```

5. **The login button will automatically appear!** The Blade template reads from config.

6. **Add SVG icon** in `resources/views/auth/login.blade.php` (already has GitHub template).

### Example: Adding Facebook

1. **Facebook is built-in to Laravel Socialite**:
   ```bash
   # No installation needed
   ```

2. **Update `config/social.php`**:
   ```php
   'facebook' => [
       'enabled' => env('FACEBOOK_LOGIN_ENABLED', false),
       'label' => 'Facebook',
       'icon' => 'facebook',
       'client_id' => env('FACEBOOK_CLIENT_ID'),
       'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
       'redirect' => env('FACEBOOK_REDIRECT_URL', env('APP_URL') . '/auth/facebook/callback'),
   ],
   ```

3. **Add to `.env`**:
   ```env
   FACEBOOK_LOGIN_ENABLED=true
   FACEBOOK_CLIENT_ID=your_facebook_app_id
   FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
   ```

4. **Add to `config/services.php`**:
   ```php
   'facebook' => config('social-login.providers.facebook'),
   ```

5. **Add SVG icon** in `resources/views/auth/login.blade.php`:
   ```blade
   @elseif($provider === 'facebook')
       <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
           <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
       </svg>
   @endif
   ```

### Example: Adding Twitter/X (Requires SocialiteProviders)

1. **Install the Twitter provider**:
   ```bash
   composer require socialiteproviders/twitter
   ```

2. **Add service provider** to `config/app.php`:
   ```php
   'providers' => [
       // ... other providers
       \SocialiteProviders\Manager\ServiceProvider::class,
   ],
   ```

3. **Add listener** to `app/Providers/EventServiceProvider.php`:
   ```php
   protected $listen = [
       \SocialiteProviders\Manager\SocialiteWasCalled::class => [
           'SocialiteProviders\\Twitter\\TwitterExtendSocialite@handle',
       ],
   ];
   ```

4. **Update `config/social.php`**:
   ```php
   'twitter' => [
       'enabled' => env('TWITTER_LOGIN_ENABLED', false),
       'label' => 'X (Twitter)',
       'icon' => 'twitter',
       'client_id' => env('TWITTER_CLIENT_ID'),
       'client_secret' => env('TWITTER_CLIENT_SECRET'),
       'redirect' => env('TWITTER_REDIRECT_URL', env('APP_URL') . '/auth/twitter/callback'),
   ],
   ```

5. **Add to `.env`**:
   ```env
   TWITTER_LOGIN_ENABLED=true
   TWITTER_CLIENT_ID=your_twitter_api_key
   TWITTER_CLIENT_SECRET=your_twitter_api_secret
   ```

6. **Add to `config/services.php`**:
   ```php
   'twitter' => config('social-login.providers.twitter'),
   ```

### Example: Adding LinkedIn (Requires SocialiteProviders)

1. **Install the LinkedIn provider**:
   ```bash
   composer require socialiteproviders/linkedin-openid
   ```

2. **Follow same steps as Twitter** for service provider and listener

3. **Update `config/social.php`**:
   ```php
   'linkedin-openid' => [
       'enabled' => env('LINKEDIN_LOGIN_ENABLED', false),
       'label' => 'LinkedIn',
       'icon' => 'linkedin',
       'client_id' => env('LINKEDIN_CLIENT_ID'),
       'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
       'redirect' => env('LINKEDIN_REDIRECT_URL', env('APP_URL') . '/auth/linkedin-openid/callback'),
   ],
   ```

### Example: Adding Apple Sign In (Requires SocialiteProviders)

1. **Install the Apple provider**:
   ```bash
   composer require socialiteproviders/apple
   ```

2. **Configuration requires additional steps** - see: https://socialiteproviders.com/Apple/

3. **Update `config/social.php`**:
   ```php
   'apple' => [
       'enabled' => env('APPLE_LOGIN_ENABLED', false),
       'label' => 'Apple',
       'icon' => 'apple',
       'client_id' => env('APPLE_CLIENT_ID'),
       'client_secret' => env('APPLE_CLIENT_SECRET'),
       'redirect' => env('APPLE_REDIRECT_URL', env('APP_URL') . '/auth/apple/callback'),
   ],
   ```

## Provider Setup Guides

## User Model Methods

```php
// Check if user has linked social account
$user->hasSocialAccount('google'); // true/false

// Get social account ID
$user->getSocialAccountId('google'); // '1234567890' or null

// Link a social account
$user->linkSocialAccount('github', 'github_user_id_123');

// Unlink a social account
$user->unlinkSocialAccount('google');
```

## Routes

```php
GET  /auth/{provider}/redirect  - Redirect to OAuth provider
GET  /auth/{provider}/callback  - Handle OAuth callback
```

## Security Notes

- All social login emails are auto-verified (configurable)
- Users can link multiple providers to one email (configurable)
- OAuth credentials are environment-specific
- Social accounts are validated against enabled providers

## Troubleshooting

**Login button not showing?**
- Check `PROVIDER_LOGIN_ENABLED=true` in `.env`
- Verify config cache: `php artisan config:clear`

**Callback error?**
- Verify redirect URI matches exactly in OAuth provider settings
- Check client ID and secret are correct
- Ensure provider is enabled in config

**Account linking not working?**
- Set `SOCIAL_LOGIN_ALLOW_LINKING=true` in `.env`
- Clear config cache
