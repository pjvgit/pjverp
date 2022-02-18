@if(count($messages) > 0)
<ul class="list">
    @foreach ($messages as $key => $item)
    <?php
    $clientList = [];    
    $userlist = explode(',', $item->user_id);
    if(count($userlist) > 0) {
        foreach ($userlist as $key => $value) {
            $userInfo =  \App\User::where('id',$value)->select('first_name','last_name','user_level')->first();
            array_push($clientList, $userInfo['first_name'].' '.$userInfo['last_name']);
        }
    }
    ?>
    <li class="list-row <?php echo ($item->is_read) ? 'is-unread' : ''; ?>">
        @if ($item->is_draft == 1)
        <a href="javascript::void(0);" onclick="openDraftMessage({{$item->id}})">
        @else
        <a href="{{ route('client/messages/info',['id' => $item->id ]) }}">
        @endif
            <span class="author-avatar">{{$clientList[0][0]}}</span>
            <div class="list-row__body list-row__body--nowrap">
                <div class="d-flex justify-content-between">
                    <span class="list-row__header">{{$item->subject}}  </span>
                    <span class="list-row__header-detail mt-1">{{$item->client_last_post}}</span>
                </div>
                <span class="list-row__header-detail">{{ implode(', ',$clientList) }}</span>
                <br>
                <span class="list-row__preview">{{$item->message}}</span>
            </div>
        </a>
    </li>
    @endforeach
</ul>
@else
<ul class="list">
    <li style="list-style: none;"><div class="text-center p-4"><i>No Messages</i></div></li>
</ul>
@endif