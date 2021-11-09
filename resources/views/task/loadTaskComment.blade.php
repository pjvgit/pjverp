<div style="max-height: 50vh; overflow: auto;" class="d-flex flex-column flex-grow-1 p-2" id="comment-view-16568099">
<?php
$controllerLoad= new App\Http\Controllers\CommonController();

if(!$TaskCommentData->isEmpty()){
    foreach($TaskCommentData as $k=>$v){?>
   
        <div class="comment d-flex mt-3 {{ ($v->user_level == 3) ? 'flex-row-reverse' : 'flex-row' }}">
            <div class="align-self-end mx-2 mb-auto">
                <div class="MuiAvatar-root MuiAvatar-circle MuiAvatar-colorDefault">{{ucfirst(substr($v->first_name,0,1))}}{{ucfirst(substr($v->last_name,0,1))}}</div>
            </div>
            <div class="comment-details d-flex flex-column  {{ ($v->user_level == 3) ? 'align-items-end' : 'align-items-start' }}">
                <div class="comment-bubble p-2 rounded text-break bg-light-c text-dark" style="word-break: break-word;">
                   <?php echo $v->title;?>
                </div>
                <?php
                $OwnDate=$controllerLoad->convertUTCToUserTime($v->created_at,Auth::User()->user_timezone);
                ?>
                <small class="text-black-50">{{date('m/d/Y h:i a',strtotime($OwnDate))}}</small>
            </div>
        </div>
        
<?php }
}else{

?>
 <div class="alert alert-info fade show" role="alert">
    <div class="d-flex align-items-start">
        <div class="w-100">No comment available.</div>
    </div>                                 
</div>
 <?php

}
?>
    </div>


{{-- <div class="d-flex flex-column flex-grow-1 p-2" id="comment-view-22717915" style="max-height: 50vh; overflow: auto;">
    <div class="comment d-flex mt-3 flex-row-reverse">
        <div class="align-self-end mx-2 mb-auto">
            <div class="MuiAvatar-root MuiAvatar-circle MuiAvatar-colorDefault">LB</div>
        </div>
        <div class="comment-details d-flex flex-column  align-items-end">
            <div class="comment-bubble p-2 rounded text-break bg-light text-dark" style="word-break: break-word;">
                <p>comment from firm portal</p>
            </div><small class="text-black-50">  Today, 11:57 am</small></div>
    </div>
    <div class="comment d-flex mt-3 flex-row">
        <div class="align-self-end mx-2 mb-auto">
            <div class="MuiAvatar-root MuiAvatar-circle MuiAvatar-colorDefault">EY</div>
        </div>
        <div class="comment-details d-flex flex-column  align-items-start">
            <div class="comment-bubble p-2 rounded text-break bg-light text-dark" style="word-break: break-word;">client comment from client portal</div><small class="text-black-50">Emma Young (Client) -  Today, 12:45 pm</small></div>
    </div>
</div> --}}
