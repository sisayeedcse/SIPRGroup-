<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_view_notifications_inbox(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->notify(new InAppNotification('Welcome', 'Inbox message'));

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Inbox message');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->notify(new InAppNotification('Reminder', 'Read this'));
        $notification = $user->notifications()->first();

        $this->actingAs($user)
            ->put(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->notify(new InAppNotification('One', 'First'));
        $user->notify(new InAppNotification('Two', 'Second'));

        $this->actingAs($user)
            ->put(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_user_cannot_mark_others_notification(): void
    {
        $owner = User::factory()->create(['status' => 'active']);
        $other = User::factory()->create(['status' => 'active']);

        $owner->notify(new InAppNotification('Owner', 'Private'));
        $notification = $owner->notifications()->first();

        $this->actingAs($other)
            ->put(route('notifications.read', $notification->id))
            ->assertNotFound();
    }
}
