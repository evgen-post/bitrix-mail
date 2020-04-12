<?php
spl_autoload_register(function($class){
    $moduleDir = substr(__DIR__, strrpos(__DIR__, '/')+1);
    $moduleNs = join('\\', array_map('ucfirst', explode('.', $moduleDir))).'\\';
    if (stripos($class, $moduleNs)===0) {
        $classPath = str_replace('\\', '/', strtolower(substr($class, strlen($moduleNs))));
        $classFile = __DIR__.'/lib/'.$classPath.'.php';
        if (file_exists($classFile)) {
            require_once $classFile;
        }
    }
});
