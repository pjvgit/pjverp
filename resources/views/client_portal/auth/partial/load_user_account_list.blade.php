@forelse ($firms as $item)
    <form class="launchpad-select-user-form" action="{{ route('login/sessions/selectuser') }}" method="post">
        @csrf
        <button id="launchpad-83a0987a-1d5c-4bc3-a549-5de8416cc223" class="btn btn-outline-secondary my-2 btn-block text-break selected-primary launchpad">
            <div class="d-flex align-items-center">
                <div class=""> {{ $item->firm_name }}
                    @if(count($item->user) && $item->user[0]->is_primary_account == 'yes')
                    <div class="badge badge-pill badge-primary primary-badge ">Primary</div>
                    @endif
                </div>
                <div class="ml-auto">
                    <div class="launchpad-user-type"> Client / Contact <i class="fa fa-lg fa-arrow-circle-right ml-2" aria-hidden="true"></i> </div>
                </div>
            </div>
        </button>
        <input type="hidden" name="client_id" value="{{ encodeDecodeId(@$item->user[0]->id, 'encode') }}">
    </form>
@empty
@endforelse