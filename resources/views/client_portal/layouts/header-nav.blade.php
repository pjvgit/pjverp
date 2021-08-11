<div class="main-header">
    <div class="logo">
        <a href="{{BASE_URL}}dashboard">
        <img src="{{asset('assets/images/logo.png')}}" alt="">
        </a>
    </div>

    <div class="text-center">
        {{ auth()->user()->firmDetail->firm_name ?? "" }}
    </div>

</div>
<div id="logoutModel" class="modal fade text-danger" role="dialog">
    <div class="modal-dialog ">
        <!-- Modal content-->
        <form id="logout-form" name="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title ">Logout Confirmation?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body bg-font-purpul">

                    <p>Do you really want to logout from the system?</p>
                </div>
                <div class="modal-footer">
                    <center>
                        <a href="#">
                            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">No</button>
                        </a>
                        <button type="submit" name="sss" class="btn btn-success ">Yes, Sure</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- header top menu end -->

{{-- Navigation menu --}}

<div class="horizontal-bar-wrap">
    <div class="header-topnav">
        <div class="container-fluid main-navigation">
            <div class=" topnav rtl-ps-none" id="" data-perfect-scrollbar data-suppress-scroll-x="true">
                <ul class="menu float-left">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><span class="nav-item__label">Home</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/"><span class="nav-item__label">Messages</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/"><span class="nav-item__label">Documents</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/"><span class="nav-item__label">Events</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/"><span class="nav-item__label">Tasks</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client/bills') }}"><span class="nav-item__label">Billing</span></a>
                    </li>
                </ul>
                <div class="header-part-right">
                    <!-- User avatar dropdown -->
                    <div class="dropdown">
                        <div class="user col align-self-end">
                            <label id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Account</label>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                <a href="#" class="dropdown-item" href="{{ route('load_profile') }}" >Settings</a>
                                <a href="{{ route('terms/client/portal') }}" class="dropdown-item">Terms</a>
                                <a href="{{ route('privacy') }}" class="dropdown-item">Privacy Policy</a>
                                <a data-toggle="modal" data-target="#logoutModel" data-placement="bottom" href="javascript:;" class="dropdown-item">Sign out </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
