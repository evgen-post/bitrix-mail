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
<input <?=$row['attrs']?> style="<?=$row['style']?>" class="<?=$row['class']?>" type="password" name="<?=$row['name']?>" value="<?=$row['value']?>">