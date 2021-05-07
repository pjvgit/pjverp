<html>

<head>
    <meta http-equiv="Content-Type" content="charset=utf-8" />
    <meta charset="UTF-8">

    <?php
        $CommonController= new App\Http\Controllers\CommonController();
        $filledData=json_decode($alreadyFilldedData['form_value']); 
    ?>
    <style>
        .header,
        .footer {
            width: 100%;
            text-align: center;
            position: fixed;
        }

        .header {
            top: 0px;
            float: right;
            width: 100%;
        }

        .footer {
            bottom: 0px;
        }

        /* .upn {
            background-image:url("{{ asset('images/logo.png') }}");
            background-repeat: no-repeat;
            width: 700px;
            height: 342px;
            position: absolute;
        } */

    </style>

    <div class="main-content">
        {{-- <div class="upn">test</div> --}}
        <h4 class="heading mb-3 test-firm-name">{{$firmData['firm_name']}}</h3><br>
            <h3 class="font-weight-bold heading mb-3 test-form-name">{{$intakeForm['form_name']}}
        </h4>

        <?php 
        foreach($intakeFormFields as $k=>$v){
            if($v->form_field=="name"){?>
        <div><b> Name</b></div>
        <div> {{$filledData->first_name}} {{$filledData->middle_name}} {{$filledData->last_name}}</div>
        <?php }else if($v->form_field=="email"){?>
        <div><br></div>
        <div><b> Email</b></div>
        <div> {{$filledData->email}} </div>
        <?php }else if($v->form_field=="home_phone"){?>
        <div><br></div>
        <div><b> Home Phone</b></div>
        <div> {{$filledData->home_phone}} </div>
        <?php } else if($v->form_field=="cell_phone"){?>
        <div><br></div>
        <div><b> Cell Phone</b></div>
        <div> {{$filledData->cell_phone}} </div>
        <?php }else if($v->form_field=="work_phone"){?>
        <div><br></div>
        <div><b> Work Phone</b></div>
        <div> {{$filledData->work_phone}} </div>
        <?php }else if($v->form_field=="address"){
        ?>
        <div><br></div>
        <div><b> Address</b></div>
        <div> {{$filledData->address1}} {{$filledData->address2}} {{$filledData->city}} {{$filledData->state}}
            {{$filledData->postal}}
            <?php
    foreach($country as $c=>$v){
        if($v->id==$filledData->country){
            echo $v->name;
        }
    } 
    ?> </div>
        <?php }else if($v->form_field=="birthday"){
            ?>
        <div><br></div>
        <div><b> Birthday</b></div>
        <div> {{$filledData->birthday}} </div>
        <?php } else if($v->form_field=="driver_license"){
            ?>
        <div><br></div>
        <div><b> Driver license</b></div>
        <div> {{$filledData->driver_license_number}} {{$filledData->driver_license_state}}</div>
        <?php } } ?>
    </div>
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $text = sprintf(_("Page %d of %d"),  $PAGE_NUM, $PAGE_COUNT);
                // Uncomment the following line if you use a Laravel-based i18n
                //$text = __("Page :pageNum/:pageCount", ["pageNum" => $PAGE_NUM, "pageCount" => $PAGE_COUNT]);
                $font = null;
                $size = 9;
                $color = array(0,0,0);
                $word_space = 0.0;  //  default
                $char_space = 0.0;  //  default
                $angle = 0.0;   //  default
    
                // Compute text width to center correctly
                $textWidth = $fontMetrics->getTextWidth($text, $font, $size);
    
                $x = ($pdf->get_width() - $textWidth) / 2;
                $y = $pdf->get_height() - 35;
    
                $pdf->text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
            '); // End of page_script
        }
    </script>
    </body>

</html>
