<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;

class TeamInvitationController extends Controller
{
    /**
     * Show team invitation landing page.
     */
    public function showLandingPage(string $token)
    {
        $invitation = TeamInvitation::with(['team.organization', 'inviter'])
            ->where('token', $token)
            ->firstOrFail();

        // Check if invitation is expired
        if ($invitation->isExpired()) {
            return view('actions.confirm', [
                'title' => 'Invitation Expired',
                'subtitle' => 'This invitation is no longer valid',
                'icon' => '⏰',
                'message' => 'This team invitation has expired and cannot be accepted.',
                'infoBox' => [
                    'title' => 'Invitation Details',
                    'items' => [
                        ['label' => 'Team', 'value' => $invitation->team->name],
                        ['label' => 'Organization', 'value' => $invitation->team->organization->name],
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
                'message' => "This team invitation has already been {$invitation->status}.",
                'infoBox' => [
                    'title' => 'Invitation Details',
                    'items' => [
                        ['label' => 'Team', 'value' => $invitation->team->name],
                        ['label' => 'Status', 'value' => ucfirst($invitation->status)],
                    ],
                ],
                'actions' => [],
            ]);
        }

        // Show confirmation page
        return view('actions.confirm', [
            'title' => 'Team Invitation',
            'subtitle' => 'You\'ve been invited to join a team',
            'icon' => '👥',
            'message' => "<strong>{$invitation->inviter->name}</strong> has invited you to join the <strong>{$invitation->team->name}</strong> team in <strong>{$invitation->team->organization->name}</strong> as a <strong>{$invitation->role}</strong>.",
            'infoBox' => [
                'title' => 'Invitation Details',
                'items' => [
                    ['label' => 'Organization', 'value' => $invitation->team->organization->name],
                    ['label' => 'Team', 'value' => $invitation->team->name],
                    ['label' => 'Role', 'value' => ucfirst($invitation->role)],
                    ['label' => 'Invited by', 'value' => $invitation->inviter->name],
                    ['label' => 'Email', 'value' => $invitation->email],
                    ['label' => 'Expires', 'value' => $invitation->expires_at->format('F j, Y g:i A')],
                ],
            ],
            'warning' => 'By accepting this invitation, you will become a member of the ' . $invitation->team->name . ' team.',
            'actions' => [
                [
                    'label' => 'Accept Invitation',
                    'url' => url('/api/team-invitations/' . $token . '/accept'),
                    'method' => 'POST',
                    'type' => 'primary',
                    'icon' => '✓',
                ],
                [
                    'label' => 'Decline',
                    'url' => url('/api/team-invitations/' . $token . '/reject'),
                    'method' => 'POST',
                    'type' => 'danger',
                    'icon' => '✕',
                ],
            ],
            'redirectUrl' => config('app.frontend_url', '/'),
        ]);
    }

    /**
     * Accept team invitation.
     */
    public function accept(Request $request, string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

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
                'team' => $invitation->team->name,
                'organization' => $invitation->team->organization->name,
            ], 200);
        }

        // Check if user belongs to organization
        if (!$user->belongsToOrganization($invitation->team->organization)) {
            return response()->json([
                'message' => __('You must be a member of :organization first', [
                    'organization' => $invitation->team->organization->name
                ]),
            ], 422);
        }

        // Check if already a team member
        if ($invitation->team->hasMember($user)) {
            $invitation->markAsAccepted();
            return response()->json([
                'message' => __('You are already a member of this team'),
            ]);
        }

        // Add user to team
        $invitation->team->addMember($user, $invitation->role, $invitation->inviter);

        // Mark invitation as accepted
        $invitation->markAsAccepted();

        return response()->json([
            'message' => __('Invitation accepted successfully! You are now a member of :team', [
                'team' => $invitation->team->name
            ]),
            'team' => $invitation->team,
        ]);
    }

    /**
     * Reject team invitation.
     */
    public function reject(string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

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
     * Get invitation details.
     */
    public function show(string $token)
    {
        $invitation = TeamInvitation::with(['team.organization', 'inviter'])
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
                'team' => [
                    'id' => $invitation->team->id,
                    'name' => $invitation->team->name,
                ],
                'organization' => [
                    'id' => $invitation->team->organization->id,
                    'name' => $invitation->team->organization->name,
                ],
                'inviter' => [
                    'name' => $invitation->inviter->name,
                    'email' => $invitation->inviter->email,
                ],
            ],
        ]);
    }

    /**
     * List pending team invitations for authenticated user.
     */
    public function myInvitations(Request $request)
    {
        $user = $request->user();

        $invitations = TeamInvitation::with(['team.organization', 'inviter'])
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
                    'team' => [
                        'id' => $invitation->team->id,
                        'name' => $invitation->team->name,
                    ],
                    'organization' => [
                        'id' => $invitation->team->organization->id,
                        'name' => $invitation->team->organization->name,
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
