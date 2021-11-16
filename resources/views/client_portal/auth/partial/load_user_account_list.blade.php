@forelse ($firms as $item)
    @if($item->user_level == '2' && $item->client_portal_enable == '1')
    <form class="launchpad-select-user-form" action="{{ route('login/sessions/selectuser') }}" method="POST">
        @csrf
        <button type="button" class="btn btn-outline-secondary my-2 btn-block text-break selected-primary launchpad">
            <div class="d-flex align-items-center">
                <div class=""> {{ $item->firm_name }}
                    @if($item->is_primary_account == 'yes')
                    <div class="badge badge-pill badge-primary primary-badge ">Primary</div>
                    @endif
                </div>
                <div class="ml-auto">
                    <div class="launchpad-user-type"> Client / Contact <i class="fa fa-lg fa-arrow-circle-right ml-2" aria-hidden="true"></i> </div>
                </div>
            </div>
        </button>
        <input type="hidden" name="client_id" value="{{ encodeDecodeId(@$item->user_id, 'encode') }}">
    </form>
    @elseif($item->user_level == '3')
    <form class="launchpad-select-user-form" action="{{ route('login/sessions/selectuser') }}" method="POST">
        @csrf
        <button type="button" class="btn btn-outline-secondary my-2 btn-block text-break selected-primary launchpad">
            <div class="d-flex align-items-center">
                <div class=""> {{ $item->firm_name }}
                    @if($item->is_primary_account == 'yes')
                    <div class="badge badge-pill badge-primary primary-badge ">Primary</div>
                    @endif
                </div>
                <div class="ml-auto">
                    <div class="launchpad-user-type"> Attorney <i class="fa fa-lg fa-arrow-circle-right ml-2" aria-hidden="true"></i> </div>
                </div>
            </div>
        </button>
        <input type="hidden" name="client_id" value="{{ encodeDecodeId(@$item->user_id, 'encode') }}">
    </form>
    @else
    @endif
@empty
@endforelse