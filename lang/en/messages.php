<?php

return [
    // Authentication
    'auth' => [
        'login_success' => 'Login successful',
        'logout_success' => 'Logged out successfully',
        'register_success' => 'Registration successful. Please check your email to verify your account before logging in.',
        'invalid_credentials' => 'The provided credentials are incorrect.',
        'unauthorized' => 'Unauthorized',
        'token_expired' => 'Token has expired',
        'token_invalid' => 'Invalid token',
        'email_not_verified' => 'Email verification required.',
        'email_verification_message' => 'Please verify your email address before logging in. Check your inbox for the verification link.',
        'organization_required' => 'Organization is required',
        'organization_not_found' => 'Organization not found',
        'switched_organization' => 'Successfully switched to :name',
        'social_login_error' => 'Unable to login with :provider. Please try again.',
        'social_login_success' => 'Successfully logged in with :provider!',
        'no_org_access' => 'You do not have access to this organization.',
        'no_org_context' => 'No organization context set',
        'org_context_required' => 'Organization context required (X-Organization-ID header)',
    ],

    // Email Verification
    'verification' => [
        'sent' => 'Verification email sent successfully.',
        'resent' => 'Verification email resent successfully.',
        'already_verified' => 'Email already verified.',
        'verified' => 'Email verified successfully.',
        'invalid_token' => 'Invalid or expired verification token.',
        'expired' => 'The verification link is invalid or has expired. Please request a new verification email.',
        'user_not_found' => 'User not found.',
        'rate_limit' => 'Please wait :seconds seconds before requesting another verification email.',
        'invalid_link' => 'Invalid verification link. Missing token or email parameter.',
        'failed_title' => 'Verification Failed',
        'already_verified_title' => 'Already Verified',
        'verified_title' => 'Email Verified',
        'already_verified_message' => 'Your email address has already been verified.',
        'verified_message' => 'Your email address has been verified successfully! You can now log in to your account.',
    ],

    // Password Reset
    'password' => [
        'reset_sent' => 'If your email is registered, you will receive a password reset link.',
        'reset_success' => 'Password reset successfully.',
        'invalid_token' => 'Invalid or expired password reset token.',
        'same_password' => 'New password must be different from current password',
        'current_incorrect' => 'Current password is incorrect',
    ],

    // Two-Factor Authentication
    '2fa' => [
        'already_enabled' => 'Two-factor authentication is already enabled.',
        'enabled' => 'Two-factor authentication enabled successfully.',
        'disabled' => 'Two-factor authentication disabled successfully.',
        'confirmed' => '2FA setup confirmed',
        'invalid_code' => 'Invalid authentication code.',
        'recovery_codes_generated' => 'Recovery codes regenerated successfully.',
        'required' => 'Two-factor authentication code required.',
        'scan_qr' => 'Scan the QR code with your authenticator app and confirm with a valid code.',
        'not_enabled' => 'Two-factor authentication is not enabled.',
        'not_enabled_user' => 'Two-factor authentication is not enabled for this user.',
        'recovery_accepted' => 'Recovery code accepted.',
        'invalid_recovery' => 'Invalid recovery code.',
        'code_verified' => 'Authentication code verified.',
        'enable_first' => 'Please call /2fa/enable first to generate a secret.',
    ],

    // User Management
    'user' => [
        'profile_updated' => 'Profile updated successfully',
        'avatar_uploaded' => 'Avatar uploaded successfully',
        'avatar_deleted' => 'Avatar deleted successfully',
        'password_changed' => 'Password changed successfully',
        'preferences_updated' => 'Preferences updated successfully',
        'not_found' => 'User not found',
        'not_member' => 'User is not a member of this organization',
    ],

    // Organizations
    'organization' => [
        'created' => 'Organization created successfully',
        'updated' => 'Organization updated successfully',
        'deleted' => 'Organization deleted successfully',
        'not_found' => 'Organization not found',
        'access_denied' => 'Access denied to this organization',
        'user_added' => 'User added to organization successfully',
        'user_removed' => 'User removed from organization successfully',
        'user_limit_reached' => 'User limit reached for this organization',
        'suspended' => 'Organization suspended successfully',
        'activated' => 'Organization activated successfully',
        'cancelled' => 'Organization subscription cancelled successfully',
        'limits_updated' => 'Organization limits updated successfully',
        'invalid_id' => 'The selected organization id is invalid.',
        'invalid_uuid' => 'The organization id must be a valid UUID or "global".',
    ],

    // Teams
    'team' => [
        'created' => 'Team created successfully',
        'updated' => 'Team updated successfully',
        'deleted' => 'Team deleted successfully',
        'not_found' => 'Team not found',
        'member_added' => 'Member added successfully',
        'member_updated' => 'Member role updated successfully',
        'member_removed' => 'Member removed successfully',
        'access_denied' => 'You do not have access to this team',
        'leader_required' => 'Only team leaders can perform this action',
        'modules_assigned' => 'Modules assigned successfully',
        'slug_exists' => 'A team with this slug already exists in your organization',
        'has_subteams' => 'Cannot delete team with sub-teams. Delete or reassign sub-teams first.',
        'already_member' => 'User is already a member of this team',
        'not_member' => 'User is not a member of this team',
        'last_leader' => 'Cannot remove the last team leader',
        'demote_last_leader' => 'Cannot demote the last team leader',
        'only_admin_delete' => 'Only organization admins can delete teams',
        'only_admin_assign' => 'Only organization admins can assign modules',
    ],

    // Roles & Permissions
    'role' => [
        'created' => 'Role created successfully',
        'updated' => 'Role updated successfully',
        'deleted' => 'Role deleted successfully',
        'not_found' => 'Role not found',
        'not_found_in_org' => 'Role not found in this organization',
        'assigned' => 'Role assigned successfully',
        'removed' => 'Role removed successfully',
        'permission_denied' => 'Permission denied',
        'slug_exists' => 'A role with this slug already exists for this tenant',
        'global_only' => 'Only global admins can create or update global roles',
        'admin_only' => 'Only organization admins can create roles in their organization',
        'superadmin_only' => 'Only super admins can assign the superadmin role',
        'global_assign_only' => 'Only global admins can assign global roles',
        'not_belong' => 'Role does not belong to this tenant',
        'permission_assigned' => 'Permission assigned to role successfully',
        'permission_removed' => 'Permission removed from role successfully',
        'modules_assigned' => 'Modules assigned to role successfully',
        'module_added' => "Module ':module' added to role ':role'",
        'module_removed' => "Module ':module' removed from role ':role'",
        'manage_modules_denied' => 'Unauthorized. Only owners and admins can manage role modules.',
        'view_modules_denied' => 'Unauthorized. Only owners and admins can view role modules.',
    ],

    'permission' => [
        'created' => 'Permission created successfully',
        'deleted' => 'Permission deleted successfully',
        'not_found' => 'Permission not found',
        'slug_exists' => 'A permission with this slug already exists for this tenant',
        'global_only' => 'Only global admins can create or update global permissions',
        'admin_only' => 'Only organization admins can create permissions in their organization',
        'assigned' => 'Permission assigned to user successfully',
        'removed' => 'Permission removed from user successfully',
    ],

    // Modules
    'module' => [
        'enabled' => 'Module enabled for organization successfully',
        'enabled_plural' => 'Modules enabled for organization successfully',
        'disabled' => 'Module disabled for organization successfully',
        'updated' => 'Module settings updated',
        'deleted' => 'Module deleted successfully',
        'not_found' => 'Module not found',
        'access_denied' => 'You do not have access to this module',
        'expired' => 'Module access has expired',
        'not_enabled' => 'Module is not enabled for this organization',
        'some_not_enabled' => 'Some modules are not enabled for this organization',
    ],

    // Billing
    'billing' => [
        'subscription_updated' => 'Subscription updated successfully',
        'limit_updated' => 'User limit updated successfully',
        'organization_suspended' => 'Organization suspended',
        'organization_reactivated' => 'Organization reactivated',
        'subscription_id_required' => 'Subscription ID is required for activation',
    ],

    // Validation
    'validation' => [
        'required' => 'The :attribute field is required',
        'email' => 'Please provide a valid email address',
        'min' => 'The :attribute must be at least :min characters',
        'max' => 'The :attribute must not exceed :max characters',
        'unique' => 'This :attribute is already taken',
        'confirmed' => 'The :attribute confirmation does not match',
    ],

    // Errors
    'error' => [
        'server_error' => 'Internal server error',
        'not_found' => 'Resource not found',
        'validation_failed' => 'Validation failed',
        'rate_limit_exceeded' => 'Too many requests. Please try again later',
    ],

    // Success
    'success' => [
        'operation_completed' => 'Operation completed successfully',
    ],
];
