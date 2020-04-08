<?php
function custom_mail($to, $subject, $message, $additional_headers, $additional_parameters, $context)
{
    return \Bx\Mail\CustomMailAdapter::getInstance()->send($to, $subject, $message, $additional_headers,
        $additional_parameters, $context);
}