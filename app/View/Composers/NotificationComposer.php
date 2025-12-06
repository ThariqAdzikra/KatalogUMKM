<?php

namespace App\View\Composers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $unreadNotifications = Notification::forUser(Auth::id())
                ->unread()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $unreadCount = Notification::forUser(Auth::id())
                ->unread()
                ->count();

            $view->with('unreadNotifications', $unreadNotifications);
            $view->with('unreadCount', $unreadCount);
        } else {
            $view->with('unreadNotifications', collect([]));
            $view->with('unreadCount', 0);
        }
    }
}
