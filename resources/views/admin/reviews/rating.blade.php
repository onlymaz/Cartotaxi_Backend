<div class="starrating">
    <?php $i=(int)$row->rating?>
    <?php for($j=5;$j>0;$j--){?>
    <input type="radio" style="pointer-events: none" id="star_<?php echo $row->id."_".$j;?>" name="rating_{{$row->id}}" value="<?php echo $j;?>" class="<?php if ($i>=$j) echo 'checked' ;?> "/>
    <label  style="pointer-events: none;" disabled for="star_<?php echo $row->id."_".$j;?>" title="<?php echo $j;?> star" class="<?php if ($i>=$j) echo 'checked' ;?>" ></label>
        <?php /*$i = $i-1;*/?>
    <?php }?>
</div>
