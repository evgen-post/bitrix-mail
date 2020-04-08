<?php

use Bitrix\Main\Localization\Loc;

/**
 * Class bx_mail
 */
class bx_mail extends CModule
{
    public $MODULE_NAME;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public $MODULE_GROUP_RIGHTS = "Y";

    /**
     * bx_mail constructor.
     */
    public function __construct()
    {
        $this->MODULE_ID = str_replace('_','.', __CLASS__);
        include(__DIR__ . "/version.php");
        /**
         * @global array $arModuleVersion
         */
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = GetMessage("BX_MAIL_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("BX_MAIL_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = GetMessage("BX_MAIL_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("BX_MAIL_PARTNER_URI");
    }

    /**
     *
     */
    function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
    }

    /**
     *
     */
    function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);
    }

    /**
     * @return array
     */
    function GetModuleRightList()
    {
        $arr = [
            "reference_id" => ["D", "W"],
            "reference" => [
                "[D] " . Loc::getMessage("BX_MAIL_RIGHT_D"),
                "[W] " . Loc::getMessage("BX_MAIL_RIGHT_W"),
            ],
        ];
        return $arr;
    }
}
