<?php

/**
 * Class EvkComposerInstall
 */
class EvkComposerInstall
{
    protected $moduleClassName;
    protected $moduleDirPath;
    protected $moduleDirName;
    protected $moduleNamespace;

    public function __construct()
    {
        $this->init();
        $this->install();
    }

    /**
     *
     */
    protected function init()
    {
        $this->moduleDirPath = __DIR__;
        $this->moduleDirName = substr($this->moduleDirPath, strrpos($this->moduleDirPath, '/')+1);
        $this->moduleClassName = str_replace('.', '_', $this->moduleDirName);
        $this->moduleNamespace = join('\\', array_map('ucfirst', explode('.', $this->moduleDirName)));
    }
    /**
     *
     */
    public function install()
    {

        file_put_contents(__DIR__.'/EvkComposerInstall.txt', print_r([
            $this,
        ], true), FILE_APPEND);

    }
}
new EvkComposerInstall();