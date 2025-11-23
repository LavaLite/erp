<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    /**
     * Accept organization invitation.
     */
    public function accept(Request $request, string $token)
    {
        $invitation = OrganizationInvitation::where('token', $token)->firstOrFail();

        // Check if invitation is valid
        if (!$invitation->isPending()) {
            return response()->json([
                'message' => $invitation->isExpired() 
                    ? __('This invitation has expired') 
                    : __('This invitation is no longer valid'),
            ], 422);
        }

        // Check if user exists
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            // User needs to register first
            return response()->json([
                'message' => __('Please register an account first'),
                'requires_registration' => true,
                'email' => $invitation->email,
                'organization' => $invitation->organization->name,
            ], 200);
        }

        // Check if already a member
        if ($user->belongsToOrganization($invitation->organization)) {
            $invitation->markAsAccepted();
            return response()->json([
                'message' => __('You are already a member of this organization'),
            ]);
        }

        // Add user to organization
        $user->joinOrganization($invitation->organization);

        // Assign role
        $role = \App\Models\Role::where('slug', $invitation->role)
            ->where(function ($query) use ($invitation) {
                $query->where('organization_id', $invitation->organization_id)
                      ->orWhereNull('organization_id');
            })
            ->first();

        if ($role) {
            $user->assignRole($role, $invitation->organization);
        }

        // Mark invitation as accepted
        $invitation->markAsAccepted();

        return response()->json([
            'message' => __('Invitation accepted successfully! You are now a member of :organization', [
                'organization' => $invitation->organization->name
            ]),
            'organization' => $invitation->organization,
        ]);
    }

    /**
     * Reject organization invitation.
     */
    public function reject(string $token)
    {
        $invitation = OrganizationInvitation::where('token', $token)->firstOrFail();

        // Check if invitation is valid
        if (!$invitation->isPending()) {
            return response()->json([
                'message' => __('This invitation is no longer valid'),
            ], 422);
        }

        // Mark invitation as rejected
        $invitation->markAsRejected();

        return response()->json([
            'message' => __('Invitation declined'),
        ]);
    }

    /**
     * Show invitation landing page.
     */
    public function showLandingPage(string $token)
    {
        $invitation = OrganizationInvitation::with(['organization', 'inviter'])
            ->where('token', $token)
            ->firstOrFail();

        // Check if invitation is expired
        if ($invitation->isExpired()) {
            return view('actions.confirm', [
                'title' => 'Invitation Expired',
                'subtitle' => 'This invitation is no longer valid',
                'icon' => '⏰',
                'message' => 'This invitation has expired and cannot be accepted.',
                'infoBox' => [
                    'title' => 'Invitation Details',
                    'items' => [
                        ['label' => 'Organization', 'value' => $invitation->organization->name],
                        ['label' => 'Invited by', 'value' => $invitation->inviter->name],
                        ['label' => 'Status', 'value' => ucfirst($invitation->status)],
                    ],
                ],
                'actions' => [],
            ]);
        }

        // Check if already accepted/rejected
        if ($invitation->status !== 'pending') {
            return view('actions.confirm', [
                'title' => 'Invitation ' . ucfirst($invitation->status),
                'subtitle' => 'This invitation has already been processed',
                'icon' => $invitation->status === 'accepted' ? '✅' : '❌',
                'message' => "This invitation has already been {$invitation->status}.",
                'infoBox' => [
                    'title' => 'Invitation Details',
                    'items' => [
                        ['label' => 'Organization', 'value' => $invitation->organization->name],
                        ['label' => 'Status', 'value' => ucfirst($invitation->status)],
                    ],
                ],
                'actions' => [],
            ]);
        }

        // Show confirmation page
        return view('actions.confirm', [
            'title' => 'Organization Invitation',
            'subtitle' => 'You\'ve been invited to join an organization',
            'icon' => '🎉',
            'message' => "<strong>{$invitation->inviter->name}</strong> has invited you to join <strong>{$invitation->organization->name}</strong> as a <strong>{$invitation->role}</strong>.",
            'infoBox' => [
                'title' => 'Invitation Details',
                'items' => [
                    ['label' => 'Organization', 'value' => $invitation->organization->name],
                    ['label' => 'Role', 'value' => ucfirst($invitation->role)],
                    ['label' => 'Invited by', 'value' => $invitation->inviter->name],
                    ['label' => 'Email', 'value' => $invitation->email],
                    ['label' => 'Expires', 'value' => $invitation->expires_at->format('F j, Y g:i A')],
                ],
            ],
            'warning' => 'By accepting this invitation, you will become a member of ' . $invitation->organization->name . ' and gain access to their resources.',
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
            'redirectUrl' => config('app.frontend_url', '/'),
        ]);
    }

    /**
     * Get invitation details.
     */
    public function show(string $token)
    {
        $invitation = OrganizationInvitation::with(['organization', 'inviter'])
            ->where('token', $token)
            ->firstOrFail();

        return response()->json([
            'invitation' => [
                'email' => $invitation->email,
                'role' => $invitation->role,
                'status' => $invitation->status,
                'expires_at' => $invitation->expires_at,
                'is_expired' => $invitation->isExpired(),
                'is_pending' => $invitation->isPending(),
                'organization' => [
                    'id' => $invitation->organization->id,
                    'name' => $invitation->organization->name,
                ],
                'inviter' => [
                    'name' => $invitation->inviter->name,
                    'email' => $invitation->inviter->email,
                ],
            ],
        ]);
    }

    /**
     * List pending invitations for authenticated user.
     */
    public function myInvitations(Request $request)
    {
        $user = $request->user();

        $invitations = OrganizationInvitation::with(['organization', 'inviter'])
            ->where('email', $user->email)
            ->where('status', 'pending')
            ->whereDate('expires_at', '>', now())
            ->get()
            ->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'token' => $invitation->token,
                    'role' => $invitation->role,
                    'expires_at' => $invitation->expires_at,
                    'organization' => [
                        'id' => $invitation->organization->id,
                        'name' => $invitation->organization->name,
                    ],
                    'inviter' => [
                        'name' => $invitation->inviter->name,
                    ],
                ];
            });

        return response()->json([
            'invitations' => $invitations,
            'total' => $invitations->count(),
        ]);
    }
}
