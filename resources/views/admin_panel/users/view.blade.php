   <!-- ============ Search UI Start ============= -->
  <div class="search-ui">
    <div class="search-header">
        <img src="{{asset('assets/images/logo.png')}}" alt="" class="logo">
        <button class="search-close btn btn-icon bg-transparent float-right mt-2">
            <i class="i-Close-Window text-22 text-muted"></i>
        </button>
    </div>

    <input type="text" placeholder="Type here" class="search-input" id="search_input" autofocus>

    <div class="search-title">
        <span class="text-muted">Search results</span>
    </div>
    <div class="search-resultss">
    </div>
    <div class="search-results list-horizontal">
    </div>
</div>
<!-- ============ Search UI End ============= -->
@section('bottom-js')
<script>
    var currentRequest = null;
    
    $("#search_input").keyup(function(){
        $(".search-resultss").html('').html(loaderImage);
        if($(this).val().length > 0){
            currentRequest = $.ajax({
                url : '{{ route("admin/searchUsers") }}',
                headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data : {'st' : $(this).val()},
                type : "POST",
                beforeSend : function()    {           
                    if(currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                success: function (res) {
                    
                    var result = JSON.parse(res);
                    console.log(result);
                    var htmlResult = '';
                    $.each(result, function(index){
                        htmlResult +='<div class="list-item col-md-12 p-0">'+
                            '<div class="card o-hidden flex-row mb-4 d-flex">'+
                                '<div class="flex-grow-1 pl-2 d-flex">'+
                                    '<div class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row">'+
                                        '<a href="" class="w-40 w-sm-100">'+
                                            '<div class="item-title">'+result[index].full_name+'</div>'+
                                        '</a>'+
                                        '<p class="m-0 text-muted text-small w-15 w-sm-100">'+result[index].email+'</p>'+
                                        '<p class="m-0 text-muted text-small w-15 w-sm-100 d-lg-block item-badges">'+
                                            '<a href="#"><span class="badge badge-primary">View</span> </a>'+
                                        '</p>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>';
                    });
                    $(".search-resultss").html('').html(htmlResult);
                }
            });
        }
    });
</script>
@endsection