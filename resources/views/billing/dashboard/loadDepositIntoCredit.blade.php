<?php $CommonController= new App\Http\Controllers\CommonController(); ?>
<form class="depositForm" id="depositForm" name="depositForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <div class="col-sm-12">
                <label for="inputEmail3" class="col-form-label">Select Contact</label>
                <select class="form-control contact select2" id="NonTrustContact" name="contact">
                    <option></option>
                    {{-- <?php  if(!$CaseMasterClient->isEmpty()){?>
                    <optgroup label="Client">
                        <?php foreach($CaseMasterClient as $k=>$v){?>
                            <option value="{{$v->id}}">{{$v->contact_name}} 
                            (Client)
                             </option>
                        <?php } ?>
                    </optgroup>    
                    <?php } ?>  --}}
                    <optgroup label="Client">
                        @forelse (firmClientList() as $key => $item)
                            <option value="{{$item->id}}">{{$item->name}} 
                                ({{ getUserTypeText()[$item->user_level] }})
                            </option>
                        @empty
                        @endforelse
                    </optgroup>
                    {{-- <?php  if(!$CaseMasterCompany->isEmpty()){?>
                    <optgroup label="Company">
                        <?php foreach($CaseMasterCompany as $k=>$v){?>
                            <option value="{{$v->id}}">{{$v->contact_name}} 
                            (Company)
                             </option>
                        <?php } ?>
                    </optgroup>
                    <?php } ?> --}}
                    <optgroup label="Comapny">
                        @forelse (firmCompanyList() as $key => $item)
                            <option value="{{$item->id}}">{{$item->name}} 
                                ({{ getUserTypeText()[$item->user_level] }})
                            </option>
                        @empty
                        @endforelse
                    </optgroup>
                </select>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        localStorage.setItem("selectedNonTrustUser", null);
        afterLoader();
        $("#NonTrustContact").select2({
            allowClear: true,
            placeholder: "Search for an existing contact or company",
            theme: "classic",
            dropdownParent: $("#loadDepositIntoCreditPopup"),
        });
        $('#NonTrustContact').on('select2:select', function (e) {
            var data = e.params.data;
            localStorage.setItem("selectedNonTrustUser", data.id);
            $("#loadDepositIntoCreditPopup").modal("hide");
            depositIntoNonTrustAccount(localStorage.getItem("selectedNonTrustUser"));
            $("#depositIntoNonTrustAccount").modal("show");
        });
    });
</script>
