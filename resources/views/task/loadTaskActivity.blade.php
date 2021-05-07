<select id="activity" name="activity" class="form-control custom-select col">
    <option value=""></option>
    <?php foreach($TaskActivity as $k=>$v){ ?>
        <option value="{{$v->id}}">{{$v->title}}</option>
    <?php } ?>
 </select>