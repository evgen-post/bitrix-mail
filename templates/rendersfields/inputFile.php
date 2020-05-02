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
<?= \CFile::InputFile(
    $row['name'],
    1,
    0,
    false,
    0,
    'CSV'
);
?>