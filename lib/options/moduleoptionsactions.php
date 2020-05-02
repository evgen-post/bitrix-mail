<?php
namespace Bx\Mail\Options;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bx\Mail\MailOption;

/**
 * Class ModuleOptionsActions
 * @package Bx\Mail\Options
 */
class ModuleOptionsActions
{
    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions(): ModuleOptions
    {
        return $this->moduleOptions;
    }

    /**
     * @param ModuleOptions $moduleOptions
     * @return ModuleOptionsActions
     */
    public function setModuleOptions(ModuleOptions $moduleOptions): ModuleOptionsActions
    {
        $this->moduleOptions = $moduleOptions;
        return $this;
    }
    public function runActions()
    {

    }
}