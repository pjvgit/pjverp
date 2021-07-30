<div class="breadcrumb">
    <ul class="text-nowrap">
        <li>
            <a href="{{route('leads/statuses')}}" 
            class="<?php if(in_array(Route::currentRouteName(),["leads/statuses","leads/active","leads/donthire","leads/converted","leads/onlineleads"])){ echo "myactive"; } ?> ">
                Manage Pipeline
            </a>
        </li>
        <li>
            <a href="" class="{{ Route::currentRouteName()=='' ? 'myactive' : '' }}">
                Dashboard
            </a>
        </li>
        <li>
            <a href="{{route('leads/tasks')}}" class="{{ Route::currentRouteName()=='leads/tasks' ? 'myactive' : '' }}">
                Tasks
            </a>
        </li>
        <?php if(!in_array(Route::currentRouteName(),["leads/statuses","leads/active","leads/donthire","leads/converted","leads/onlineleads","leads/tasks"])){  ?>
            <li>
            <a href="" class="myactive"> Lead Details </a>
        </li>
        <?php } ?>
        <li>
            <a href="{{route('leads/statuses')}}" class="{{ Route::currentRouteName()=='' ? 'myactive' : '' }}">
                Lead Insight
            </a>
        </li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<style>
    .myactive {
        font-weight: bold;
    }
</style>
