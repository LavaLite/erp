# Reusable Action Confirmation Template

The `/resources/views/actions/confirm.blade.php` template is a flexible, reusable component for email-triggered actions that require user confirmation.

## Features

- 🎨 Beautiful, responsive design with gradient backgrounds
- 📱 Mobile-friendly layout
- ⚡ AJAX-powered actions (no page reload)
- 🔄 Loading states and success/error handling
- ♿ Accessible and semantic HTML
- 🎯 Highly customizable via simple data array

## Usage Examples

### 1. Organization Invitation (Current Implementation)

```php
return view('actions.confirm', [
    'title' => 'Organization Invitation',
    'subtitle' => 'You\'ve been invited to join an organization',
    'icon' => '🎉',
    'message' => "<strong>John Doe</strong> has invited you...",
    'infoBox' => [
        'title' => 'Invitation Details',
        'items' => [
            ['label' => 'Organization', 'value' => 'Acme Corp'],
            ['label' => 'Role', 'value' => 'Member'],
        ],
    ],
    'warning' => 'By accepting...',
    'actions' => [
        [
            'label' => 'Accept Invitation',
            'url' => url('/api/invitations/' . $token . '/accept'),
            'method' => 'POST',
            'type' => 'primary',
            'icon' => '✓',
        ],
        [
            'label' => 'Decline',
            'url' => url('/api/invitations/' . $token . '/reject'),
            'method' => 'POST',
            'type' => 'danger',
            'icon' => '✕',
        ],
    ],
    'redirectUrl' => config('app.frontend_url'),
]);
```

### 2. Password Reset Confirmation

```php
return view('actions.confirm', [
    'title' => 'Reset Your Password',
    'subtitle' => 'Confirm password reset request',
    'icon' => '🔒',
    'message' => 'You requested to reset your password for <strong>' . $email . '</strong>',
    'infoBox' => [
        'title' => 'Reset Details',
        'items' => [
            ['label' => 'Email', 'value' => $email],
            ['label' => 'Requested', 'value' => now()->diffForHumans()],
            ['label' => 'Expires', 'value' => $expiresAt],
        ],
    ],
    'warning' => 'If you didn\'t request this, please ignore this page or click Cancel.',
    'actions' => [
        [
            'label' => 'Reset Password',
            'url' => route('password.reset.confirm', ['token' => $token]),
            'method' => 'POST',
            'type' => 'primary',
            'icon' => '🔑',
        ],
    ],
    'cancelUrl' => route('login'),
]);
```

### 3. Email Verification

```php
return view('actions.confirm', [
    'title' => 'Verify Your Email',
    'subtitle' => 'Complete your account setup',
    'icon' => '📧',
    'message' => 'Please verify your email address to activate your account.',
    'infoBox' => [
        'title' => 'Account Details',
        'items' => [
            ['label' => 'Email', 'value' => $user->email],
            ['label' => 'Name', 'value' => $user->name],
        ],
    ],
    'actions' => [
        [
            'label' => 'Verify Email',
            'url' => route('verification.verify', ['token' => $token]),
            'method' => 'POST',
            'type' => 'primary',
            'icon' => '✓',
        ],
    ],
    'redirectUrl' => route('dashboard'),
]);
```

### 4. Delete Account Confirmation

```php
return view('actions.confirm', [
    'title' => 'Delete Account',
    'subtitle' => 'This action cannot be undone',
    'icon' => '⚠️',
    'message' => 'You are about to permanently delete your account.',
    'warning' => 'All your data will be permanently deleted. This action cannot be undone.',
    'actions' => [
        [
            'label' => 'Delete My Account',
            'url' => route('account.destroy'),
            'method' => 'DELETE',
            'type' => 'danger',
            'icon' => '🗑️',
        ],
    ],
    'cancelUrl' => route('settings'),
]);
```

### 5. Subscription Cancellation

```php
return view('actions.confirm', [
    'title' => 'Cancel Subscription',
    'subtitle' => 'We\'re sorry to see you go',
    'icon' => '💔',
    'message' => 'Your subscription will be cancelled at the end of the billing period.',
    'infoBox' => [
        'title' => 'Subscription Details',
        'items' => [
            ['label' => 'Plan', 'value' => 'Premium'],
            ['label' => 'Billing Cycle', 'value' => 'Monthly'],
            ['label' => 'Next Billing', 'value' => '2025-12-21'],
            ['label' => 'Access Until', 'value' => '2025-12-21'],
        ],
    ],
    'warning' => 'You will lose access to premium features after this date.',
    'actions' => [
        [
            'label' => 'Cancel Subscription',
            'url' => route('subscription.cancel'),
            'method' => 'POST',
            'type' => 'danger',
        ],
        [
            'label' => 'Keep My Subscription',
            'url' => route('subscription.index'),
            'method' => 'GET',
            'type' => 'primary',
        ],
    ],
]);
```

### 6. Team Member Removal

```php
return view('actions.confirm', [
    'title' => 'Remove Team Member',
    'subtitle' => 'Confirm member removal',
    'icon' => '👋',
    'message' => 'You are about to remove <strong>' . $member->name . '</strong> from the team.',
    'infoBox' => [
        'title' => 'Member Details',
        'items' => [
            ['label' => 'Name', 'value' => $member->name],
            ['label' => 'Email', 'value' => $member->email],
            ['label' => 'Role', 'value' => $member->role],
            ['label' => 'Joined', 'value' => $member->joined_at],
        ],
    ],
    'actions' => [
        [
            'label' => 'Remove Member',
            'url' => route('team.members.remove', ['id' => $member->id]),
            'method' => 'DELETE',
            'type' => 'danger',
        ],
    ],
    'cancelUrl' => route('team.members'),
]);
```

## Template Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `title` | string | Yes | Main heading text |
| `subtitle` | string | No | Subheading text |
| `icon` | string | No | Emoji or icon character |
| `message` | string | No | Main message (supports HTML) |
| `infoBox` | array | No | Structured information display |
| `infoBox.title` | string | No | Info box heading |
| `infoBox.items` | array | No | Array of label/value pairs |
| `warning` | string | No | Warning message in yellow box |
| `actions` | array | Yes | Array of action buttons |
| `actions[].label` | string | Yes | Button text |
| `actions[].url` | string | Yes | API endpoint URL |
| `actions[].method` | string | No | HTTP method (default: POST) |
| `actions[].type` | string | No | Button style: primary, secondary, danger |
| `actions[].icon` | string | No | Button icon/emoji |
| `actions[].disabled` | bool | No | Disable button |
| `cancelUrl` | string | No | URL for cancel/back button |
| `redirectUrl` | string | No | Auto-redirect after success |

## Styling

The template uses inline styles for email compatibility but is fully customizable. Key color scheme:

- Primary gradient: `#667eea` → `#764ba2`
- Success: `#10b981`
- Danger: `#ef4444`
- Gray scale: Tailwind CSS palette

## JavaScript Features

- AJAX form submission
- Loading spinner during requests
- Success/error state handling
- Auto-redirect on success (optional)
- Error message display
- Network error handling

## Best Practices

1. **Keep actions simple**: Limit to 2-3 buttons max
2. **Use clear labels**: Action buttons should clearly state what will happen
3. **Show context**: Always include relevant information in the info box
4. **Warn about consequences**: Use the warning box for irreversible actions
5. **Mobile first**: Test on mobile devices
6. **Accessibility**: Ensure color contrast and keyboard navigation work
