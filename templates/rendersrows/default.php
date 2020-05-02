<?php
use Bx\Mail\Options\HtmlOptions;

/**
 * @var array $tab
 * @var array $row
 * @var HtmlOptions $this
 */
if (empty($row['code'])) {
    return null;
}
?>
<tr>
    <td>
        <?=$row['label']?>
    </td>
    <td>
        <?$this->getRenderField($row, $tab)?>
    </td>
</tr>