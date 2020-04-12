<?php
spl_autoload_register(function(){
    $dir = __DIR__;
    file_put_contents(__DIR__.'/spl_autoload_register.txt', print_r($dir, true)."\n", FILE_APPEND);
});
echo \Bx\Mail\MailOption::MODULE_ID;