<div class="breadcrumb">
    <ul class="text-nowrap">  
        <li><a class="{{ Route::currentRouteName()=='contacts/client' ? 'myactive' : '' }}"
                href="{{route('contacts/client')}}?target=active"> Clients </a></li>
        <li><a class="{{ Route::currentRouteName()=='contacts/company' ? 'myactive' : '' }}"
                href="{{route('contacts/company')}}?target=active"> Companies </a></li>
        <li><a class="{{ Route::currentRouteName()=='contacts/contact_groups' ? 'myactive' : '' }}"
                href="{{route('contacts/contact_groups')}}?target=active"> Contact Groups </a></li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<style>
    .myactive {
        font-weight: bold;
    }
</style>
