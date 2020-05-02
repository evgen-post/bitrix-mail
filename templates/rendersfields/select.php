<?php
use Bx\Mail\Options\HtmlOptions;

/**
 * @var array $row
 * @var string $div
 * @var HtmlOptions $this
 */
if (empty($row['code'])) {
    return null;
}
?>
<select <?=$row['attrs']?> style="<?=$row['style']?>" class="<?=$row['class']?>" name="<?=$row['name']?>">
    <?php foreach ($row['values'] as $keyValue => $value):?>
        <option <?=($row['value']===$keyValue) ? ' selected="selected"' : ''?> value="<?=$keyValue?>"><?=$value?></option>
    <?php endforeach;?>
</select>
