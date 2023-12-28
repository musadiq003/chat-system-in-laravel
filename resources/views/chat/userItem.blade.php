



@if(isset($user))
<li id="contact" data-name="favorite" class="">
    <a href="javascript:void(0);">
        <div class="d-flex align-items-center">
            <div class="chat-user-img online align-self-center me-2 ms-0">
                <div class="avatar-xs">
                    <span class="avatar-title rounded-circle bg-primary text-white">
                        <span class="username">{{ $user->name[0] }}</span>
                        <span class="user-status"></span>
                    </span>
                </div>
            </div>
            <div class="overflow-hidden me-2">
                <p class="text-truncate chat-username mb-0">{{ $user->name }}</p>
                <p class="text-truncate text-muted fs-13 mb-0">Wow that's great!</p>
            </div>
        </div>
    </a>
</li>
@endif