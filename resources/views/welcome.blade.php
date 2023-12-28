@extends('chat.app')
@section('content')

<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->
<link src="/frotend/chat/chat.css"></link>

<!DOCTYPE html>
<!-- resources/views/chat.blade.php -->

<style>
	.active{
		background:blue;
	}
	.loader {
  border: 5px solid #f3f3f3;
  border-radius: 50%;
  border-top: 5px solid #3498db;
  width: 30px;
  height: 30px;
  margin-left: 10%;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
  
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
<div style="display: flex;">
        <div style="width: 20%; padding-right: 20px;">
            <ul id="user-list">
              
            </ul>
        </div>
        <div style="width: 80%;">
        	<div style="border: 1px solid #ccc;">    
				<div id="chat-box" style="height: 300px; overflow-y: scroll; padding: 10px;">
					<p>Select a user to view messages.</p>
				</div>
				<div style="width= 100%">
				
					<div id="loader" class="loader" style="display: none;"></div>
				</div>
			</div>
			<form id="chat-form" style="margin-top: 10px;">
                @csrf
                <input type="text" name="message" id="message-input" placeholder="Type your message">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>

$(document).ready(function () {
	fetchUsers();
    Pusher.logToConsole = true;
    var pusher = new Pusher('2e1f3c481df17c813f1e', {
        cluster: 'mt1',
        forceTLS: true
    });

    // Subscribe to the Pusher channel
    var channel = pusher.subscribe('my-channel');

// Listen for the 'message-sent' event
channel.bind('message-sent', function (data) {
    // Handle the new message
    var message = data.message;
    var user = data.user;
    var isReceiver = data.to;

    // Check if the message is from the authenticated user
    var isSender = message.from == {{ auth()->id() }};

    // Display the message only if the authenticated user is the sender or receiver
    if (isReceiver) {
		fetchUsers();
	}
    if (isSender && isReceiver) {
		$('#loader').hide();
        fetchUsers();
        var senderName = isSender ? 'You' : user.name;

        // Update the UI, e.g., append the new message to the chat box
        var chatBox = $('#chat-box');
        var messageClass = isSender ? 'sender' : 'receiver';
        chatBox.append('<p class="' + messageClass + '"><strong>' + senderName + ':</strong> ' + message.message + '</p>');

        // Scroll to the bottom to show the latest message
        chatBox.scrollTop(chatBox[0].scrollHeight);

        // If the message is for the currently active user, also update their UI
        var activeUserId = $('#user-list .user-item.active').data('user-id');
        if (user.id == activeUserId) {
            var activeUserChatBox = $('#active-user-chat-box');
            activeUserChatBox.append('<p class="' + messageClass + '"><strong>' + senderName + ':</strong> ' + message.message + '</p>');
        }
    }
});


function fetchUsers() {
    $.ajax({
        method: 'GET',
        url: '/chat/users',
        success: function (data) {
            console.log(data);
            var users = data.users;

            if (users && users.length > 0) {
                displayUsers(users);
            } else {
                console.warn('No users found.');
            }
        },
        error: function (error) {
            console.error('Error fetching users:', error);
        }
    });
}
// Handle user item click
$('#user-list').on('click', '.user-item', function () {
    $('#user-list .user-item').removeClass('active');
    $(this).addClass('active');

    // Fetch and display messages for the selected user
    var userId = $(this).data('user-id');
    selectedUserId = userId; // Update the selectedUserId

    fetchMessages(userId);
});
function displayUsers(users) {
    var userList = $('#user-list');
    userList.empty(); // Clear existing users

    users.forEach(function (user) {
        var listItem = $('<li></li>')
            .attr('data-user-id', user.id)
            .addClass('user-item')
            .text(user.name);

        userList.append(listItem);
    });

    // After updating the user list, trigger a click on the first user to fetch and display messages
	if (selectedUserId) {
    // Find the user item with the selected user's ID and trigger the click event
    $('#user-list .user-item[data-user-id="' + selectedUserId + '"]').trigger('click');
}
}




    // Fetch and display messages for a specific user
    function fetchMessages(userId) {
        $.ajax({
            method: 'GET',
            url: '/chat/messages/' + userId,
            success: function (data) {
                displayMessages(data.messages,data.user);
            },
            error: function (error) {
                console.error('Error fetching messages:', error);
            }
        });
    }

    // Display messages in the chat box
    function displayMessages(messages,user) {
        var chatBox = $('#chat-box');
        if (messages.length > 0) {
            messages.forEach(function (message) {
				var senderName = message.from == {{ auth()->id() }} ? 'You' : user.name;
				var readStatus = message.is_read ? 'Read' : 'Unread';
            var messageContent = '<p class="' + (message.from === {{ auth()->id() }} ? 'sender' : 'receiver') + '"><strong>' + senderName + ':</strong> ' + message.message + '</p>';
            chatBox.append(messageContent);

            });

            // Scroll to the bottom to show the latest message
            chatBox.scrollTop(chatBox[0].scrollHeight);
        } else {
            chatBox.append('<p>No new messages available.</p>');
        }
    }

    // Handle the chat form submission
    $('#chat-form').submit(function (e) {
        e.preventDefault();
		$('#loader').show();
        var content = $('#message-input').val();
        var userId = $('#user-list .user-item.active').data('user-id');

        if (userId) {

			
            $.ajax({
                method: 'POST',
                url: '/chat/store',
                data: {
                    content: content,
                    user_id: userId,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
				
                success: function (data) {
                    $('#message-input').val('');
                },
                error: function (error) {
                    console.error('Error sending message:', error);
                }
            });
        } else {
            alert('Please select a user.');
        }
    });
});

</script>
@endsection