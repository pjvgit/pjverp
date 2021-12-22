<div id="loadGrantAccessModal" class="modal fade modal-overlay" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Sharing with a client asdasd</h5>
                <button class="close dismissLoadGrantAccessModal" type="button" aria-label="Close">
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
@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        $('.dismissLoadGrantAccessModal').on('click', function() {
            $('#loadGrantAccessModal').modal('hide');
        });
    });
</script>
@endsection