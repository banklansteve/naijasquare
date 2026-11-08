<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = $user->appNotifications()
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn (AppNotification $notification): array => $this->payload($notification));

        $user->appNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        $href = $notification->data['href'] ?? null;

        if (is_string($href) && str_starts_with($href, '/')) {
            return redirect($href);
        }

        return redirect()->route('notifications.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AppNotification $notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $this->title($notification->type, $data),
            'body' => $this->body($notification->type, $data),
            'href' => is_string($data['href'] ?? null) ? $data['href'] : null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'unread' => $notification->read_at === null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function title(string $type, array $data): string
    {
        return match ($type) {
            'related_need' => 'A Need you might help with',
            'community_admin_invite' => 'Admin invitation',
            default => 'Notification',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function body(string $type, array $data): string
    {
        return match ($type) {
            'related_need' => sprintf(
                '%s posted “%s” in %s.',
                (string) ($data['author_name'] ?? 'Someone'),
                (string) ($data['need_title'] ?? 'a Need'),
                (string) ($data['community_name'] ?? 'a community'),
            ),
            'community_admin_invite' => sprintf(
                '%s invited you to help admin %s.',
                (string) ($data['inviter_name'] ?? 'Someone'),
                (string) ($data['community_name'] ?? 'a community'),
            ),
            default => 'Open to view details.',
        };
    }
}
