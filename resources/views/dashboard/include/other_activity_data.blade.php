<tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                    $imageLink=[];
                    $imageLink["complete"]="activity_intake_form_completed.png";
                    $image=$imageLink[$v->action];
                    ?>                   
                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                    <?php  if($v->activity_for == 1){  ?>
                        <a class="name" href="{{ route('contacts/clients/view', $v->user_id) }}">{{ $v->fullname}} (Client)</a>
                            {{$v->activity}}
                        <?php  if($v->case_unique_number!=NULL){  ?>
                            <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                        <?php }  ?>
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                        <?php  if($v->case_unique_number!=NULL){  ?>
                            <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                        <?php }  ?>
                    <?php }else{ ?>
                        <a class="name" href="{{ route('case_details/info', $v->user_id) }}">{{ $v->fullname}} (Lead)</a>
                            {{$v->activity}}
                        <a class="name" href="{{ route('case_details/info',$v->user_id) }}">Potential Case: {{ $v->fullname}}</a>
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                        <a class="name" href="{{ route('case_details/info',$v->user_id) }}">{{ $v->fullname}}</a>
                    <?php }  ?>
                </div>
            </td>
        </tr>