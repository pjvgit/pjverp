<div class="breadcrumb">
    <ul class="text-nowrap">  
        <li><a class="<?php if(Route::currentRouteName()=='court_cases'  && !isset($_GET['i'])){ echo 'myactive'; } ?>"
                href="{{route('court_cases')}}">My Open Cases</a></li>
        <li><a class="<?php if(Route::currentRouteName()=='court_cases'  &&  isset($_GET['i'])){ echo 'myactive'; } ?>"
                href="{{route('court_cases')}}?i=c">My Close Cases</a></li>
        <!-- <li><a class="{{ Route::currentRouteName()=='apexAreaCharts' ? 'myactive' : '' }}"
                href="{{route('apexAreaCharts')}}"> Firm Open Cases </a></li>
        <li><a class="{{ Route::currentRouteName()=='apexBarCharts' ? 'myactive' : '' }}"
                href="{{route('apexBarCharts')}}"> Firm Close Cases </a></li> -->
        <li><a class="{{ Route::currentRouteName()=='practice_areas' ? 'myactive' : '' }}"
                href="{{route('practice_areas')}}"> Practice Areas </a></li>
        <?php if(!in_array(Route::currentRouteName(),["court_cases","apexColumnCharts","practice_areas","apexBarCharts","apexAreaCharts"])){  ?>
        <li>
            <a href="" class="myactive"> Case Details </a>
        </li>
        <?php } ?>
        <li><a class=" <?php if(in_array(Route::currentRouteName(),["apexColumnCharts","apexBarCharts","apexAreaCharts"])){ echo'myactive'; } ?>"
                href="{{route('apexColumnCharts')}}"> Case Insights </a></li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<style>
    .myactive {
        font-weight: bold;
    }
</style>
