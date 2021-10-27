<h2 class="mx-2 mb-0 text-nowrap hiddenLable">        {{ ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name) }} (Client)    </h2>
<div class="table-responsive">
    <div class="d-flex align-items-center justify-content-end mb-2 d-print-none">
        <a data-toggle="modal" data-target="#addCaseLinkWithOption" data-placement="bottom" href="javascript:;"> 
            <button class="btn btn-primary btn-rounded m-1 px-5" type="button" onclick="">Add Case Link</button>
        </a> 
    </div>
    <table class="display table table-striped table-bordered" id="LinkedCaseList" style="width:100%">
        <thead>
            <tr>
                <th width="1%"></th>
                <th width="70%">Name</th>
                <th width="10%">Role</th>
                <th width="10%">Status</th>
                <th width="10%"></th>
            </tr>
        </thead>
    </table>
</div>
