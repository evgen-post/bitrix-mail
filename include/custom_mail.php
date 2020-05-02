<?php
if (!function_exists('custom_mail')) {
    function custom_mail($to, $subject, $message, $additional_headers, $additional_parameters, $context)
    {
        return \Bx\Mail\CustomMailAdapter::getInstance()->send($to, $subject, $message, $additional_headers,
            $additional_parameters, $context);
    }
}
