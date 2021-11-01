<header class="header">
	<div class="header__firm-details">
        <span class="header__firm-logo">
            <img src="{{ @firmDetail(auth()->user()->firm_name)->firm_logo_url }}" alt="Firm Logo">
        </span>
		<div class="header__title">
            <span class="header__firm-name">{{ auth()->user()->firmDetail->firm_name ?? "" }}</span>
        </div>
	</div>
	<div class="header__icon"><i class="material-icons" id="settings_icon">settings</i></div>
</header>


{{-- Navigation menu --}}

<div class="horizontal-bar-wrap">
    <div class="header-topnav">
        <div class="container-fluid main-navigation">
            <div class=" topnav rtl-ps-none" id="" data-perfect-scrollbar data-suppress-scroll-x="true">
                <ul class="menu float-left">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client/home') }}"><span class="nav-item__label">Home</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><span class="nav-item__label">Messages</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><span class="nav-item__label">Documents</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client/events') }}"><span class="nav-item__label">Events</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><span class="nav-item__label">Tasks</span></a>
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
                                <a href="{{ route('client/account') }}" class="dropdown-item" >Settings</a>
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
