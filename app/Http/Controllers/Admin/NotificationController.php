<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request){
        if ($request->ajax()) {
            $notificaiton = Notification::where('user_to_notify', $request->user()->id)->orderBy('id', 'DESC')->get();
            return response()->json(['success' => true, 'data' => $notificaiton]);
        }
        $notifications = Notification::where('user_to_notify', $request->user()->id)
            ->orderByDesc('id')->paginate(25);
        $unreadCount = Notification::where('user_to_notify', $request->user()->id)->where('read', 0)->count();
        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }
}
