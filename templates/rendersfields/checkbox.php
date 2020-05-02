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
$defValue = $row['default'];
?>
<?php if (empty($row['values'])):
    if ($row['value'] === $defValue) {
        $row['attrs'] .= ' checked="checked"';
    }
    ?>
    <input <?=$row['attrs']?> style="<?=$row['style']?>" class="<?=$row['class']?>" type="checkbox" name="<?=$row['name']?>" value="<?=$defValue?>">
<?php else:?>
    <?php foreach ($row['values'] as $keyValue => $value):?>
        <?php if ($keyValue === 0):?>
            <input type="hidden" name="<?=$row['name']?>" value="<?=$value?>">
        <?php else:?>
            <?php if ($row['value'] === $value):
                $row['attrs'] .= ' checked="checked"';
                ?>
            <?php endif;?>
            <input <?=$row['attrs']?> style="<?=$row['style']?>" class="<?=$row['class']?>" type="checkbox" name="<?=$row['name']?>" value="<?=$value?>">
        <?php endif;?>
    <?php endforeach;?>
<?php endif;?>