<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_slug' => $user->role?->slug,
                ] : null,
            ],
            'unreadNotifications' => $user
                ? Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->latest('created_at')
                    ->limit(10)
                    ->get(['id', 'type', 'message', 'related_work_order_id', 'created_at'])
                : [],
        ];
    }
}
