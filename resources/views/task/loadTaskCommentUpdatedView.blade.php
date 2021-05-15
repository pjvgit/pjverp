<div style="max-height: 50vh; overflow: auto;" class="d-flex flex-column flex-grow-1 p-2" id="comment-view-16568099">
    <?php
    $controllerLoad= new App\Http\Controllers\CommonController();
    if(!$TaskCommentData->isEmpty()){
        foreach($TaskCommentData as $k=>$v){?>
            <div id="comment_35000411" class="comment_row" style="">
                <div class="comment_padding">
                    <table style="width: 100%;">
                        <tbody>
                        <tr>
                            <td style="width: 76px; vertical-align: top;">
                                <div style="background-color: white;border: 0px solid #d6d6d6;border-radius: 0px;width: 56px;height: 56px;padding: 2px;display: inline-block;overflow: hidden;">
                                @if(file_exists(public_path().'/images/users/'.Auth::user()->profile_image) && Auth::user()->profile_image!='' && Auth::User()->is_published=="yes")
                                    <img class="border border-dark" src="{{URL::asset('/public/images/users/')}}/{{Auth::user()->profile_image}}"
                                        id="userDropdown" alt="" data-toggle="dropdown" aria-haspopup="true" width="52" height="52"
                                        aria-expanded="false">
                                       
                                    @else

                                    <img class="border border-dark" style="max-width: 150px;" width="52" height="52"
                                        src="{{asset('assets/images/faces/default_face.svg')}}">
                                    @endif
                                    
                     </div>
                            </td>
                            <td>
                                <?php
                                    $OwnDate=$controllerLoad->convertUTCToUserTime($v->created_at,Auth::User()->user_timezone);
                                ?>
                                <div style="font-weight: bold;">
                                    <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->uid)}}">{{ucfirst($v->first_name)}} {{ucfirst($v->last_name)}} ({{$controllerLoad->getUserLevelText($v->user_type)}})</a>
                                    -
                                    <small class="text-black-50">{{date('m/d/Y h:i a',strtotime($OwnDate))}}</small>
                                </div>
                                <div class="comment-text-formatted wrap-long-words">
                                    <div><?php echo $v->title;?></div>
                                </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
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

<style>
.calico_lightbox div.comment_row, .calico div.comment_row {
    background-color: transparent;
    border: none;
    border-bottom: 1px dotted #d6d6d6;
}
</style>