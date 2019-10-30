<?php

function isREGEXCorrectName($name)
{
    return preg_match('/^[a-zA-Z0-9]{3,20}$/', $name);
}

function isREGEXCorrectEmail($mail)
{
    return filter_var($mail, FILTER_VALIDATE_EMAIL);
}

?>