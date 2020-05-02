<?php
use Bx\Mail\CustomMailAdapter;

CustomMailAdapter::getInstance()->getRestrict()->onlyEmailTo();
CustomMailAdapter::getInstance()->createCustomMailFunction();
