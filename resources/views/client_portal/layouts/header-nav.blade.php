<header class="header">
    <div class="header__firm-details">
        @php
            $firm = @firmDetail(auth()->user()->firm_name);
        @endphp
        <span class="header__firm-logo">
            <img alt="Firm Logo" src="{{ @$firm->firm_logo_url }}">
        </span>
        <div class="header__title">
            <span class="header__active-section">Home</span>
            <span class="header__firm-name">{{ @$firm->firm_name }}</span>
        </div>
    </div>
    <div class="header__icon">
        <i class="material-icons" id="settings_icon">settings</i>
    </div>
</header>
<div class="main-navigation-container">
    <nav class="main-navigation">
        <ul class="main-navigation__links main-navigation__links--shared-items">
            <li class="nav-item" data-app-section="Home">
                <a class="nav-link" href="{{ route('client/home') }}">
                    <i class="nav-item__icon">home</i>
                    <span class="nav-item__label">Home</span>
                </a>
            </li>
            <li class="nav-item" data-app-section="Messages">
                <a class="nav-link" href="{{ route('client/messages') }}">
                    <i class="nav-item__icon">email</i>
                    <span class="nav-item__label">Messages</span>
                </a>
            </li>
            <li class="nav-item" data-app-section="Documents">
                <a class="nav-link" href="#">
                    <i class="nav-item__icon">insert_drive_file</i>
                    <span class="nav-item__label">Documents</span>
                </a>
            </li>
            <li class="nav-item" data-app-section="Events">
                <a class="nav-link" href="{{ route('client/events') }}">
                    <i class="nav-item__icon">insert_invitation</i>
                    <span class="nav-item__label">Events</span>
                </a>
            </li>
            <li class="nav-item" data-app-section="Tasks">
                <a class="nav-link" href="{{ route('client/tasks') }}">
                    <i class="nav-item__icon">check_circle</i>
                    <span class="nav-item__label">Tasks</span>
                </a>
            </li>
            <li class="nav-item" data-app-section="Billing">
                <a class="nav-link" href="{{ route('client/bills') }}">
                    <i class="nav-item__icon">monetization_on</i>
                    <span class="nav-item__label">Billing</span>
                </a>
            </li>
        </ul>
        <div class="main-navigation__account">
            {{-- <div class="main-navigation__account-button nav-item">
                <a class="nav-link" href="#">My Account</a>
            </div>
            <ul class="main-navigation__links main-navigation__account-dropdown">
                <li class="nav-item" data-app-section="Settings">
                    <a class="nav-link" href="/account">
                        <span class="nav-item__label">Settings</span>
                    </a>
                </li>
                <li class="nav-item" data-app-section="Terms">
                    <a class="nav-link" href="https://www.mycase.com/terms/client_portal" rel="noopener" target="_blank">
                        <span class="nav-item__label">Terms</span>
                    </a>
                </li>
                <li class="nav-item" data-app-section="Privacy Policy">
                    <a class="nav-link" href="https://www.mycase.com/privacy" rel="noopener" target="_blank">
                        <span class="nav-item__label">Privacy Policy</span>
                    </a>
                </li>
                <li class="nav-item" data-app-section="Log Out">
                    <a class="nav-link" href="#">
                        <span class="nav-item__label">Log Out</span>
                    </a>
                </li>
            </ul> --}}

            <div class="dropdown">
                <div  class="user col align-self-end">
                    <label class="nav-link" id="header-dd" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Account</label>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="header-dd" id="header-dd">
                        <a href="{{ route('client/account') }}" class="dropdown-item" >Settings</a>
                        @if (auth()->user()->getUserFirms() > 1)
                        <a href="{{ route('login/sessions/launchpad', encodeDecodeId(auth()->id(), 'encode')) }}" class="dropdown-item" >Switch Account</a>
                        @endif
                        <a href="{{ route('terms/client/portal') }}" class="dropdown-item">Terms</a>
                        <a href="{{ route('privacy') }}" class="dropdown-item">Privacy Policy</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item" style="outline: none;">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>