<div id="loadGrantAccessModal" class="modal fade modal-overlay" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Sharing with a client asdasd</h5>
                <button class="close dismissLoadGrantAccessModal"  type="button" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="grantCase">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- If open section, event list not working --}}
@section('modal-js')
<script type="text/javascript">
    
    $(document).on('click', '.dismissLoadGrantAccessModal', function() {
        localStorage.setItem('loadGrantAccessModal', "hide");
        $('#loadGrantAccessModal').modal('hide');
    });
    
</script>
@endsection