<?php
use Bitrix\Main\Config\Option;
use Bx\Mail\MailOption;

$arFields = [
    'SMTP' => [
        [
            'label' => 'Активность отправки SMTP',
            'code' => 'SMTP_ACTIVE',
            'default' => 'N',
            'type' => 'checkbox',
        ],
        [
            'label' => 'Хост',
            'code' => 'SMTP_HOST',
            'default' => MailOption::DEFAULT_HOST,
        ],
        [
            'label' => 'Порт',
            'code' => 'SMTP_PORT',
            'default' => MailOption::DEFAULT_PORT,
        ],
        [
            'label' => 'Логин',
            'code' => 'SMTP_USERNAME',
        ],
        [
            'label' => 'Пароль',
            'code' => 'SMTP_PASSWORD',
        ],
        [
            'label' => 'Отправитель по умолчанию',
            'code' => 'SMTP_SENDER',
        ],
        [
            'label' => 'Ограничить список получателей',
            'code' => 'MAIL_SENDERS_STRICT',
            'default' => 'N',
            'type' => 'checkbox',
        ],
        [
            'label' => 'Разрешённые получатели',
            'code' => 'MAIL_SENDERS',
        ]
    ],
];

function PzSmtploadOptions(&$arFields)
{
    foreach ($arFields as $key => &$fields) {
        foreach ($fields as &$field) {
            if (!isset($field['default'])) {
                $field['default'] = '';
            }
            if ($field['code'] === 'MAIL_SENDERS') {
                $field['value'] = join("\n", array_map('trim', preg_split("([\n,;]+)ui", Option::get(MailOption::MODULE_ID, $field['code'], $field['default']))));
            } else {
                $field['value'] = Option::get(MailOption::MODULE_ID, $field['code'], $field['default']);
            }
        }
    }
}

PzSmtploadOptions($arFields);

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["Update"])>0 && ($USER->CanDoOperation('edit_other_settings') && $USER->CanDoOperation('edit_groups')) && check_bitrix_sessid()) {
    if (!empty($_POST['SMTP'])) {
        MailOption::saveOptions($_POST['SMTP']);
    }
    if($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == "") {
        LocalRedirect($_REQUEST["back_url_settings"]);
    } else {
        LocalRedirect("/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=".urlencode($mid)."&tabControl_active_tab=".urlencode($_REQUEST["tabControl_active_tab"])."&back_url_settings=".urlencode($_REQUEST["back_url_settings"]));
    }
}

PzSmtploadOptions($arFields);

$aTabs = array(
    array("DIV" => "edit1", "TAB" => "Настройки SMTP", "ICON" => "main_settings", "TITLE" => "Настройки SMTP"),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

?>
<form name="main_options" method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&amp;lang=<?echo LANG?>">
    <?=bitrix_sessid_post()?>
    <?php
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><b>Настройки SMTP</b></td>
    </tr>
    <?php foreach ($arFields['SMTP'] as $field):?>
        <tr>
            <td><?=$field['label']?></td>
            <td>
                <?php if($field['type'] === 'checkbox'):?>
                    <input type="hidden" name="SMTP[<?=$field['code']?>]" value="N">
                    <input class="input-text" type="checkbox" name="SMTP[<?=$field['code']?>]" value="Y" <?=(($field['value'] === 'Y') ? ' checked="checked"' : '')?>>
                <?php elseif($field['code'] === 'SMTP_PASSWORD'):?>
                    <input class="input-text" type="password" name="SMTP[<?=$field['code']?>]" value="<?=$field['value']?>">
                <?php elseif($field['code'] === 'MAIL_SENDERS'):?>
                    <textarea rows="20" style="width: 100%" class="input-text" name="SMTP[<?=$field['code']?>]"><?=htmlspecialchars($field['value'])?></textarea>
                <?php else:?>
                    <input class="input-text" type="text" name="SMTP[<?=$field['code']?>]" value="<?=$field['value']?>">
                <?php endif;?>
            </td>
        </tr>
    <?php endforeach;?>
    <?$tabControl->Buttons();?>
    <script type="text/javascript">
        function RestoreDefaults()
        {
            if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANGUAGE_ID?>&mid=<?echo urlencode($mid)?>&<?echo bitrix_sessid_get()?>";
        }

        BX.ready(function(){
            var f = document.forms['main_options'];
            if(f.use_time_zones)
                f.default_time_zone.disabled = f.auto_time_zone.disabled = !f.use_time_zones.checked;
        });

    </script>
    <input <?if (!$USER->CanDoOperation('edit_other_settings')) echo "disabled" ?> type="submit" name="Apply" value="Сохранить" title="<?echo GetMessage("MAIN_OPT_APPLY_TITLE")?>"<?if($_REQUEST["back_url_settings"] == ""):?>  class="adm-btn-save"<?endif?>>
    <?if($_REQUEST["back_url_settings"] <> ""):?>
        <input type="button" name="" value="<?echo GetMessage("MAIN_OPT_CANCEL")?>" title="<?echo GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::JSEscape($_REQUEST["back_url_settings"]))?>'">
    <?endif?>
    <input type="hidden" name="Update" value="Y">
    <input type="hidden" name="back_url_settings" value="<?echo htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
    <?$tabControl->End();?>
</form>
