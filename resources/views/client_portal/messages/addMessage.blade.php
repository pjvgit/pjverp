<div class="col-md-12" bladefile="resources/views/client_portal/messages/addMessage.blade.php">
    <div class="showError"></div>
    <p>All fields marked with * are required.</p>
    <div>
        <form id="addMessage" name="addMessage" method="POST">
            @csrf
            <input type="hidden" name="message_id" value="{{ $Messages->id }}"/>
            @if($caseListCount > 0 && $caseListCount == 1)
            <div class="form-group mb-3 pt-3 pb-1">
                <div class="form-input__label">To*</div>
                <div>
                @foreach($userCaseStaffList as $k => $v)
                <input class="mr-2 sendTo" name="send_to[]"  type="checkbox" value="{{$v->id}}" <?php echo ($Messages->user_id == $v->id) ? 'checked' :''; ?> ><label> {{ ucfirst(substr($v->first_name,0,100).' '.substr($v->last_name,0,100)) }} ({{$v->user_title}})</label>
                <br/>
                @endforeach
                </div>
                <input type="hidden" name="case_id" value="{{ $caseList->clientCases[0]->id }}"/>
            </div>
            @else
            <div class="form-group row ">
                <label class="col-12  col-form-label">Case *</label>
                <div class="col-12 ">
                    <select class="form-control" name="case_id" id="case_id" required="">
                    <option value="">Please choose one case</option>    
                        @foreach($caseList->clientCases as $case)
                        <option value="{{$case->id}}" <?php echo ($Messages->case_id == $case->id) ? 'selected' : ''; ?>  >{{$case->case_title}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group mb-3 pt-3 pb-1 staffList">
            <?php
                $staffList = explode(',',$Messages->user_id);
            ?>
            @if($Messages->user_id != '')
                <div class="form-input__label">To*</div>
                <div class="listView">                    
                    @foreach($userCaseStaffList as $k => $v)
                    <input class="mr-2 sendTo" name="send_to[]"  type="checkbox" value="{{$v->id}}" <?php echo (in_array($v->id, $staffList)) ? 'checked' :''; ?> ><label> {{ ucfirst(substr($v->first_name,0,100).' '.substr($v->last_name,0,100)) }} ({{$v->user_title}})</label>
                    <br/>
                    @endforeach
                    </div>   
                
            @endif
                </div>
            @endif
            <div class="form-group row ">
                <label class="col-12  col-form-label">Subject*</label>
                <div class="col-12 ">
                <input id="message_subject" name="subject" required="" type="text" class="form-control" value="{{ $Messages->subject ?? ''}}">
                </div>
            </div>
            <div class="form-group row ">
                <label class="col-12  col-form-label">Message*</label>
                <div class="col-12">
                <textarea id="message_body" name="msg" class="form-control" required="" rows="2">{{ $Messages->message ?? ''}}</textarea>
                </div>
            </div>
            <div class="form-group row"><label class="col-12 col-form-label saved"></label></div>
            <div class="form-group row">
                <button type="submit" id="sendMessageButton" class="btn btn-primary ml-3">Send</button>
                <button class="btn btn-info ml-3" type="button" onclick="discardDraft({{ $Messages->id }});" >Discard Draft</button>
                <input type="hidden" name="action" id="action" value=""/>
            </div>
        </form>
    </div>
</div>
@section('page-js')
<script src="{{ asset('assets\client_portal\js\messages\messages.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@endsection
