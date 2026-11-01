<?php

namespace App\Community;

use App\Mail\CommunityAdminInvite;
use App\Models\AppNotification;
use App\Models\Community;
use App\Models\CommunityAdminInvitation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreateCommunity
{
    public function __construct(private CommunityCovers $covers) {}

    /**
     * @param  array{
     *     name: string,
     *     description: string,
     *     location_type: string,
     *     location_label: ?string,
     *     city: ?string,
     *     tag_keys: list<string>,
     *     access_mode: string,
     *     who_can_post?: string,
     *     guidelines: ?string,
     *     require_guidelines_accept?: bool,
     *     admin_emails?: list<string>
     * }  $data
     */
    public function handle(User $creator, array $data, ?UploadedFile $cover = null): Community
    {
        return DB::transaction(function () use ($creator, $data, $cover): Community {
            $locationLabel = $data['location_label'] ?? null;
            $city = $data['city'] ?? null;

            if (($data['location_type'] ?? '') === 'city' && $city === null && $locationLabel !== null) {
                $city = $locationLabel;
            }

            if (($data['location_type'] ?? '') === 'uk_wide') {
                $locationLabel = $locationLabel ?: 'UK-wide';
                $city = $city ?: 'UK';
            }

            $community = Community::query()->create([
                'name' => $data['name'],
                'description' => $data['description'],
                'city' => $city ?: ($locationLabel ?: 'UK'),
                'listed' => true,
                'access_mode' => $data['access_mode'] ?? Community::ACCESS_REQUEST,
                'location_type' => $data['location_type'],
                'location_label' => $locationLabel,
                'tag_keys' => $data['tag_keys'] ?? [],
                'guidelines' => $data['guidelines'] ?? null,
                'require_guidelines_accept' => (bool) ($data['require_guidelines_accept'] ?? false),
                'who_can_post' => $data['who_can_post'] ?? 'members',
                'member_list_visibility' => 'members',
                'created_by' => $creator->id,
            ]);

            if ($cover !== null) {
                $community->update([
                    'cover_path' => $this->covers->store($cover, $community->id),
                ]);
            }

            $community->members()->attach($creator->id, [
                'role' => Community::ROLE_OWNER,
                'guidelines_accepted_at' => now(),
            ]);

            $emails = collect($data['admin_emails'] ?? [])
                ->map(fn (string $email): string => strtolower(trim($email)))
                ->filter()
                ->unique()
                ->reject(fn (string $email): bool => $email === strtolower($creator->email));

            foreach ($emails as $email) {
                $invitee = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

                $invitation = CommunityAdminInvitation::query()->create([
                    'community_id' => $community->id,
                    'inviter_id' => $creator->id,
                    'invitee_user_id' => $invitee?->id,
                    'email' => $email,
                    'status' => CommunityAdminInvitation::STATUS_PENDING,
                ]);

                Mail::to($email)->send(new CommunityAdminInvite($invitation));

                if ($invitee !== null) {
                    AppNotification::query()->create([
                        'user_id' => $invitee->id,
                        'type' => 'community_admin_invite',
                        'data' => [
                            'community_id' => $community->id,
                            'community_name' => $community->name,
                            'invitation_id' => $invitation->id,
                            'token' => $invitation->token,
                            'inviter_name' => $creator->name,
                        ],
                    ]);
                }
            }

            return $community->fresh();
        });
    }
}
