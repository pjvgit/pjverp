<h2 class="mx-2 mb-0 text-nowrap hiddenLable"> {{ ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name) }} (Company)    </h2>
<div class="table-responsive">
    <div class="d-flex align-items-center justify-content-end mb-2">
        <a data-toggle="modal" data-target="#addClientLinkWithOption" data-placement="bottom" href="javascript:;">
            <button class="btn btn-primary btn-rounded m-1 px-5" type="button" onclick="">Add Contact</button>
        </a>
    </div>
    <?php if($clientLinkCount<=0 && $clientArchiveLinkCount<=0){ ?>
    <div class="no_items text-right test-no-company-contacts">
        <img class="img-fluid" src="{{BASE_URL}}images/company_add_prompt.png">
    </div>
    <?php  }else{ ?>
    <?php if($clientArchiveLinkCount>0){ ?> <h4 class="pl-4">Active</h4> <?php } ?>
    <table class="display table table-striped table-bordered" id="linkedClient" style="width:100%">
        <thead>
            <tr>
                <th width="1%"></th>
                <th width="3%"></th>
                <th width="33%">Name</th>
                <th width="40%">Cases</th>
                <th width="10%">Last Login</th>
                <th width="10%"></th>
            </tr>
        </thead>
    </table>
    <?php } ?>
    <?php 
    if($clientArchiveLinkCount>0){ ?>
    <br>
    <h4 class="pl-4">Archived</h4>
    <table class="display table table-striped table-bordered" id="linkedArchiveClient" style="width:100%">
        <thead>
            <tr>
                <th width="1%"></th>
                <th width="3%"></th>
                <th width="33%">Name</th>
                <th width="40%">Cases</th>
                <th width="10%">Last Login</th>
                <th width="10%"></th>
            </tr>
        </thead>
    </table>
    <?php } ?>
</div>
