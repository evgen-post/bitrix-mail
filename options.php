<?php
use Bitrix\Main\Loader;
use Bx\Mail\Options\ModuleOptions;

Loader::includeModule('bx.mail');
$moduleOptions = new ModuleOptions();
$moduleOptions->runActions();
$moduleOptions->showForm();