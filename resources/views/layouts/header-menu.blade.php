    <div class="main-header">
        <div class="logo">
            <a href="{{BASE_URL}}dashboard">
            <img src="{{ @firmDetail(auth()->user()->firm_name)->firm_logo_url }}" alt="">
            </a>
        </div>
        <div class="d-flex align-items-center">
           
            <div class="search-bar">
                <input type="text" placeholder="Search" /><i class="search-icon text-muted i-Magnifi-Glass1"></i>
            </div>
        </div>

        <div style="margin: auto"></div>

        <div class="header-part-right">
            <!-- User avatar dropdown -->
            <div class="dropdown">
                <div class="user col align-self-end">
                  
                    <?php
                        echo substr(Auth::user()->first_name,0,15);
                        echo "&nbsp;";
                        echo substr(Auth::user()->last_name,0,15);
                    ?>
                    @if(file_exists( public_path().'/images/users/'.Auth::user()->profile_image ) && Auth::user()->profile_image!='' && Auth::User()->is_published=="yes")
                    <img src="{{URL::asset('/public/images/users/')}}/{{Auth::user()->profile_image}}" id="userDropdown" alt="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                    @else
                    <img src="{{asset('assets/images/faces/default_face.svg')}}" id="userDropdown" alt=""
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @endif
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <div class="dropdown-header">
                            <?php if(isset(Auth::user()->first_name)){?>
                            <i class="i-Lock-User mr-1"></i>
                            <?php
                                echo substr(Auth::user()->first_name,0,15);
                                echo "&nbsp;";
                                echo substr(Auth::user()->last_name,0,15);
                            }?>
                        </div>
                        <a class="dropdown-item" href="{{ route('account/dashboard') }}" >All Settings</a>
                        <a class="dropdown-item" href="{{ route('load_profile') }}" >My Profile & Contact Info</a>
                        @can('add_firm_user')
                        <a class="dropdown-item" >Add Firm User</a>    
                        @endcan
                        @can('edit_firm_user_permission')
                        <a class="dropdown-item" href="{{ route('contacts/attorneys') }}" >Firm User Permissions</a>
                        @endcan
                        @if (auth()->user()->getUserFirms() > 1)
                        <a class="dropdown-item" href="{{ route('login/sessions/launchpad', encodeDecodeId(auth()->id(), 'encode')) }}" >Switch Account</a>
                        @endif
                        <a data-toggle="modal" data-target="#logoutModel" data-placement="bottom" href="javascript:;"
                            class="dropdown-item">Sign out </a>

                    </div>
                </div>
            </div>
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
