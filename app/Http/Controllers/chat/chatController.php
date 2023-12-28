<?php

namespace App\Http\Controllers\chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Message;
use App\Models\User;
use Pusher\Pusher;
use Illuminate\Support\Facades\DB;


class chatController extends Controller
{
    public function chat() {
        $users = User::all();
        return view('chat.chat', compact('users'));
    }
    
        public function userList($id) {
            $user = User::find($id);
            if (!$user) {
                // Handle the case where the user is not found
                abort(404);
            }
    
            // Pass the user data to the view
            return view('chat.userItem', ['user' => $user]);
        }


        public function getUsersWithHtml()
{
    $users = User::all();

    $userItemsHtml = view('chat.userItem', compact('users'))->render();

    return response()->json([
        'users' => $users,
        'userItemsHtml' => $userItemsHtml,
    ]);
}


    public function users()
{
    $loggedInUserId = auth()->id();

    $interactedUsers = Message::join('users', function ($join) {
        $join->on('messages.from', '=', 'users.id')
        ->orOn('messages.to', '=', 'users.id');
    })
    ->where(function ($q) {
        $q->where('messages.from', Auth::user()->id)
        ->orWhere('messages.to', Auth::user()->id);
    })
    ->where('users.id', '!=', Auth::user()->id)
    ->select('users.*', DB::raw('MAX(messages.created_at) as max_created_at'))
    ->groupBy('users.id')
    ->orderByDesc('max_created_at')
    ->get();
    $allUsers = User::where('id', '!=', Auth::user()->id)->get();

    // Merge the two collections
    $users = $interactedUsers->merge($allUsers);

    return response()->json(['users' => $users]);
}


    

    

    public function getUserMessages($user)
{
    $loggedInUser = auth()->user();

    // Fetch messages between the logged-in user and the selected user
    $loggedInUser = auth()->user();

    // Fetch messages between the logged-in user and the selected user
    $messages = Message::where(function ($query) use ($loggedInUser, $user) {
        $query->where('from', $loggedInUser->id)
              ->where('to', $user);
    })->orWhere(function ($query) use ($loggedInUser, $user) {
        $query->where('from', $user)
              ->where('to', $loggedInUser->id);
    })->orderBy('created_at', 'asc')->get();
    $messages->where('to', $loggedInUser)->where('is_read', 0)->each(function ($message) {
        $message->update(['is_read' => 1]);
    });

    $user = User::find($user);

    return response()->json(['messages' => $messages, 'user' => $user]);
}


    public function store(Request $request) {
        $user = User::where('id', Auth::user()->id)->first();
        $message  = Message::create([
                    'from' => $user->id,
                    'message' => $request->input('content'),
                    'to' => $request->input('user_id'),
                    
        ]);
        $to = $request->input('user_id');
        
        // Mark messages as read for the currently logged-in user
        
        // Trigger the 'NewComment' event on a unique channel for the blog post
        $channelName = 'my-channel';

        $options = [
            'cluster' => 'mt1',
            'useTLS' => true,
        ];

        $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                $options
        );
        $user = User::find($request->input('user_id'));
      
        $pusher->trigger($channelName, 'message-sent', ['to'=> $to, 'message' => $message, 'user' => $user]);

        return response()->json([ ]);

    }
}
