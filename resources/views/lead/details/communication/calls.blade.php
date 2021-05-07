<?php
$CommonController= new App\Http\Controllers\CommonController();

$callfor=$status=$type='';

if(isset($_GET['callfor'])){
     $callfor= $_GET['callfor'];
}
if(isset($_GET['status'])){
     $status= $_GET['status'];
}
if(isset($_GET['type'])){
     $type= $_GET['type'];
}

?>
<div class="row ">
    <div class="col">
        <div class="float-right">
            <a data-toggle="modal" data-target="#addCaseNote" data-placement="bottom" href="javascript:;">
                <button class="btn btn-outline-secondary  btn-rounded m-1 px-3" type="button">Tell us what you
                    think</button>
            </a>
            <a data-toggle="modal" data-target="#addCall" data-placement="bottom" href="javascript:;">
                <button class="btn btn-primary btn-rounded m-1 px-3" type="button" onclick="addCall();">Add
                    Call</button>
            </a>
        </div>
    </div>
</div>
<form class="filterBy" id="filterBy" name="filterBy" method="GET">
    <div class="row m-1">

        <div class="col-md-3 form-group mb-3">
            <label for="picker1">Call For</label>
            <select id="callfor" name="callfor" class="form-control custom-select col select2">
                <option></option>
                <?php foreach($getAllFirmUser as $key=>$val){?>
                <option <?php if($val->id==$callfor){ echo "selected=selected"; } ?> value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
                </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-2 form-group mb-3">
            <label for="picker1">Status</label>
            <select id="status" name="status" class="form-control custom-select col select2">
                <option></option>
                <option <?php if($status=="yes"){ echo "selected=selected"; } ?> value="yes">Resolved</option>
                <option <?php if($status=="no"){ echo "selected=selected"; } ?> value="no">Unresolved</option>
            </select>
        </div>
        <div class="col-md-2 form-group mb-3">
            <label for="picker1">Type</label>
            <select id="type" name="type" class="form-control custom-select col select2">
                <option></option>
                <option <?php if($type=="0"){ echo "selected=selected"; } ?> value="0">Incoming</option>
                <option <?php if($type=="1"){ echo "selected=selected"; } ?> value="1">Outgoing</option>
            </select>
        </div>

        <div class="col-md-3 form-group mb-3 mt-3">
            <button class="btn btn-info btn-rounded m-1" type="submit">Apply Filters</button>
            <button type="button" class="test-clear-filters text-black-50 btn btn-link">
                <a href="{{URL::to('leads/'.$user_id.'/communications/calls')}}">Clear Filters</a>
            </button>
        </div>
        <div class="col-md-2 form-group mb-3 mt-4">
            <small class="text-muted mx-1">Text Size</small>
            <button type="button" arial-label="Decrease text size" data-testid="dec-text-size"
                class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease "><i class="fas fa-minus fa-xs"></i>
            </button>
            <button type="button" arial-label="Increase text size" data-testid="inc-text-size"
                class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase"><i class="fas fa-plus fa-xs"></i>
            </button>
        </div>
    </div>

</form>
<?php
if($totalCalls<=0){
?>
<div class="call-log-content w-100 text-center p-5 h-100">
    <i class="fas fa-phone-alt my-4 fa-5x"></i>
    <h1 class="font-weight-bold">Never miss a phone message </h1>
    <p>Say goodbye to message pads and spreadsheets. Increase efficiency and improve firm-wide communication by
        recording the details of all phone communication right in {{config('app.name')}}.</p>
    <button type="button" data-testid="empty-state-add-call" class="btn btn-primary btn-rounded m-1 px-3">Add
        Call</button>
    <button type="button" class="btn btn-outline-secondary  btn-rounded m-1 px-3">Learn More</button>
</div>

<?php
}else{?>
<div class="table-responsive">
    <table class="display table table-striped table-bordered" id="callList" style="width:100%">
        <thead>
            <tr>
                <th width="10%">DATE/TIME</th>
                <th width="10%">CALLER</th>
                <th width="10%">CALL FOR</th>
                <th width="10%">TYPE</th>
                <th width="40%">MESSAGE</th>
                <th width="10%"></th>
                <th width="10%"></th>
            </tr>
        </thead>

    </table>
</div>
<?php }
?>
