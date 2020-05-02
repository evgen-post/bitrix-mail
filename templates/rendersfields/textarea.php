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
<textarea <?=$row['attrs']?> style="<?=$row['style']?>" class="<?=$row['class']?>" name="<?=$row['name']?>"><?=htmlspecialchars($row['value'])?></textarea>