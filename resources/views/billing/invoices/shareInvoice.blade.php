<?php
$CommonController= new App\Http\Controllers\CommonController();
?>
<form class="shareInvoiceForm" id="shareInvoiceForm" name="shareInvoiceForm" method="POST">
    <span id="response"></span>
    @csrf
    <input class="form-control field" value="{{$Invoices['id']}}" maxlength="250" name="invoice_id" type="hidden">
    <div class="col-md-12">
        <div class="share-via-portal-form">
            <div class="py-2">Select Contacts to share this invoice on the Client Portal</div>
            <div class="alert alert-info" role="alert">
                <span>Your client will get a notification email that they now
                    have an invoice available in their portal, with an option to login. If you wish to directly email
                    this invoice as a PDF from MyCase, click the "Email Invoice" option on the invoice details
                    view.
                </span>
                <span class="px-1">
                    <a href="#" target="_blank" rel="noopener noreferrer">What will my client see?</a>
                </span>
            </div>
            <table class="table table-bordered pt-2">
                <thead class="thead-light">
                    <tr>
                        <th>Share</th>
                        <th>Contact Name</th>
                        <th>Last Login</th>
                        <th>Shared</th>
                        <th>Viewed</th>
                    </tr>
                </thead>
                <tbody>

                    <?php 
                    foreach($getAllClientForSharing as $k=>$v){?>
                    @if ($v->user_level=="4")
                        <tr class="invoice-sharing-row">
                            <td colspan="5"><strong>{{ucfirst($v->unm)}} (Company)</strong></td>
                        </tr>
                        @if (count($v->company_contacts))
                            @forelse ($v->company_contacts as $ckey => $citem)
                                @php
                                    $invShareDetail = $getAllClientForSharing->where('id', $citem->cid)->first();
                                @endphp
                                <tr class="invoice-sharing-row">
                                    <th scope="row" class="text-center">                                        
                                        <input type="checkbox" name="invoice_shared[{{$citem->cid}}]" value="{{$citem->cid}}" id="portalAccess_{{$citem->cid}}" class="invoiceSharingBox invoice-sharing-box"
                                            uid="{{$citem->cid}}" em="{{$v->email}}" pe="{{$v->client_portal_enable}}" onclick="checkPortalAccess({{$citem->cid}})" @if($v->shared=="yes") checked="checked" @endif>
                                    </th>
                                    <td class="client-name">{{ $citem->fullname }} (Client)</td>
                                    <td class="last-login-date">
                                        @if($v->email=="")
                                            Disabled
                                        @else
                                            @if($invShareDetail->last_login)
                                                {{ date('F jS Y, h:i:s A',strtotime(convertUTCToUserTime($invShareDetail->last_login, Auth::User()->user_timezone))) }}
                                            @else
                                                Never
                                            @endif
                                        @endif
                                    </td>
                                    <td class="shared-on-date">
                                        {{ ($invShareDetail->sharedDate) ? date('F jS Y, h:i a',strtotime(convertUTCToUserTime($invShareDetail->sharedDate,Auth::User()->user_timezone))) : "Not Shared" }}
                                    </td>
                                    <td class="viewed-on-date">
                                        {{ ($invShareDetail->isViewd=="yes") ? "Yes" : "Never" }}
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        @else
                            <tr class="invoice-sharing-row">
                                <td colspan="5" class="text-note"><i>No contacts from this company are linked to this case</i></td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="5"></td>
                        </tr>
                    @else
                    @if($v->is_company_contact == "no")
                    <tr class="invoice-sharing-row" id="ClientRow_{{$v->user_id}}">

                        <td scope="row" class="text-center">
                            <?php if($v->shared=="yes"){
                              ?>
                            <input type="checkbox" name="invoice_shared[{{$v->user_id}}]" value="{{$v->user_id}}"
                                id="portalAccess_{{$v->user_id}}" class="invoiceSharingBox invoice-sharing-box"
                                uid="{{$v->user_id}}" em="{{$v->email}}" pe="{{$v->client_portal_enable}}"
                                onclick="checkPortalAccess({{$v->user_id}})" checked="checked">

                            <?php
                            }else{
                                ?>
                            <input type="checkbox" name="invoice_shared[{{$v->user_id}}]" value="{{$v->user_id}}"
                                id="portalAccess_{{$v->user_id}}" class="invoiceSharingBox invoice-sharing-box"
                                uid="{{$v->user_id}}" em="{{$v->email}}" pe="{{$v->client_portal_enable}}"
                                onclick="checkPortalAccess({{$v->user_id}})">
                            <?php
                            }
                            ?>


                        </td>
                        <td class="client-name"> {{ucfirst($v->unm)}}
                            <?php 
                            if($v->user_level=="2"){
                                echo "(Client)";
                            }else{
                                echo "(Company)";
                            }?></td>
                        <td class="last-login-date">
                            <?php if($v->email==""){
                                echo "Disabled";
                            }else{
                                
                                if($v->last_login!=NULL){
                                    $loginDate=$CommonController->convertUTCToUserTime($v->last_login,Auth::User()->user_timezone);
                                    echo date('F jS Y, h:i:s A',strtotime($loginDate));
                                }else{
                                    echo "Never";
                                }
                            }
                        ?></td>
                        <td class="shared-on-date">
                            <?php if($v->sharedDate!=NULL){
                                $sharedDate=$CommonController->convertUTCToUserTime($v->sharedDate,Auth::User()->user_timezone);
                                echo date('F jS Y, h:i a',strtotime($sharedDate));
                            }else{
                                echo "Not Shared";
                            }
                            ?>
                        </td>
                        <td class="viewed-on-date">
                            <?php if($v->isViewd=="yes"){
                            echo "Yes";
                        }else{
                            echo "Never";
                        }
                        ?></td>
                    </tr>
                    @endif
                    @endif
                    <?php } ?>
                </tbody>
            </table>
            {{-- <table class="table table-bordered pt-2">
                <thead class="thead-light">
                    <tr>
                        <th>Share</th>
                        <th>Contact Name</th>
                        <th>Last Login</th>
                        <th>Shared</th>
                        <th>Viewed</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="invoice-sharing-row">
                        <td colspan="5"><strong>Roth and Irwin Trading (Company)</strong></td>
                    </tr>
                    <tr class="invoice-sharing-row">
                        <th scope="row" class="text-center">
                            <input type="checkbox" id="sharing-26709890" class="select-client" value="26709890">
                        </th>
                        <td class="client-name">Lee Quae excepteur omnis Vinson (Client)</td>
                        <td class="last-login-date">Disabled</td>
                        <td class="shared-on-date">Not Shared</td>
                        <td class="viewed-on-date">Never</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                    </tr>
                    <tr class="invoice-sharing-row">
                        <th scope="row" class="text-center">
                            <input type="checkbox" id="sharing-26530433" class="select-client" value="26530433">
                        </th>
                        <td class="client-name">[SAMPLE] John Doe (Client)</td>
                        <td class="last-login-date">Never</td>
                        <td class="shared-on-date">Not Shared</td>
                        <td class="viewed-on-date">Never</td>
                    </tr>
                </tbody>
            </table> --}}

        </div>
        </span>
        <div class="modal-footer">
            <div class="col-md-2 form-group">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                </div>
            </div>
            <a href="#">
                <button class="btn btn-secondary  btn-rounded mr-1 " type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary  btn-rounded submit " id="submitButton" value="savenote"
                type="submit">Save</button>
        </div>
    </div>
</form>


<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $("#shareInvoiceForm").validate({
            rules: {
                activity_title: {
                    required: true
                }
            },
            messages: {
                activity_title: {
                    required: "Name can't be blank",
                }
            }
        });
    });
    $('#grantAccessModal').on('hidden.bs.modal', function () {
        $(this).modal('hide')
    });
    $('.showError').html('');
    afterLoader();
    $('#shareInvoiceForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#shareInvoiceForm').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#shareInvoiceForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/saveShareInvoice", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&update=yes';
            },
            success: function (res) {
                if (res.errors != '') {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $('#shareInvoiceForm').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
                    window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });

    function checkPortalAccess(id) {
        var em = pa = "";
        em = $("#portalAccess_" + id).attr("em");
        pa = $("#portalAccess_" + id).attr("pe");

        if ($("#portalAccess_" + id).prop('checked') == true && (em == "" || pa == "0")) {
            $("#portalAccess_" + id).prop('checked', false);
            $('.showError').html('');
            beforeLoader();
            $("#preloader").show();
            $('#grantAccessModal').modal("show");
            $("#grantAccessModalArea").html('');
            $("#grantAccessModalArea").html('Loading...');
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/view/checkAccessFromViewInvoice",
                data: {
                    "id": id
                },
                success: function (res) {

                    if (typeof (res.errors) != "undefined" && res.errors !== null) {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        $("#preloader").hide();
                        $("#grantAccessModalArea").html('');
                        $('#grantAccessModal').animate({
                            scrollTop: 0
                        }, 'slow');

                        return false;
                    } else {
                        if (res == "true") {
                            $('#grantAccessModal').modal("hide");
                            $("#portalAccess_" + id).prop('checked', true);
                            $("#preloader").hide();
                            afterLoader()
                            return true;
                        } else {
                            afterLoader()
                            $("#grantAccessModalArea").html(res);
                            $("#preloader").hide();
                            return true;
                        }
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#grantAccessModal').animate({
                        scrollTop: 0
                    }, 'slow');

                    afterLoader();
                }
            })
        }
    }

    function reloadRow(id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/view/reloadRowForViewInvoice",
            data: {
                "id": id
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    return false;
                } else {
                    afterLoader()
                    $("#ClientRow_" + id).html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }

</script>
