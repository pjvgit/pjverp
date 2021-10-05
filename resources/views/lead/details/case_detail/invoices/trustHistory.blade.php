
<table class="display table table-striped table-bordered" id="billingTabTrustHistory" style="width:100%" data-url="{{ route('contacts/clients/loadTrustHistory') }}" data-client-id="{{ $user_id }}">
    <thead>
        <tr>
            <th class="" style="cursor: initial;">Date</th>
            <th class="" style="cursor: initial;">Related To</th>
            <th class="" style="cursor: initial;">Details</th>
            <th class="" style="cursor: initial;">Payment Method</th>
            <th class="" style="cursor: initial;">Allocated To</th>
            <th class="" style="cursor: initial;">Amount</th>
            <th class="" style="cursor: initial;">Balance</th>
            <th class="text-right d-print-none" style="cursor: initial;">Action</th>
        </tr>
    </thead>
</table>
@section('page-js-inner')
<script src="{{ asset('assets\js\custom\client\viewclient.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@endsection