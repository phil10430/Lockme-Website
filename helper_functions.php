<?php
const REQUEST_TYPE_DEFAULT = "0";
const REQUEST_UPDATE_HISTORY = "1";
const REQUEST_CLEAR_WISHED_ACTION = "2";
const REQUEST_TYPE_LOGIN = "3";
const REQUEST_TYPE_LOGOUT = "4";

const MSG_OPEN = "O";
const MSG_CLOSE = "C";
const MSG_SEPARATOR = "/";
const CON_STATUS_CONNECTED = 1;
const PLACEHOLDER = "*";

const PASSWORD_REGEX = "/^[a-zA-Z0-9]+$/";
const USERNAME_REGEX = "/^[a-zA-Z0-9]+$/";


function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateDate($date)
{
    $format = 'd/m/Y H:i'; // Eg : 21/07/2022 14:40
    $dateTime = DateTime::createFromFormat($format, $date);

    if ($dateTime instanceof DateTime && $dateTime->format('d/m/Y H:i') == $date) {
        return true;
    }

    return false;
}

function isValidPassword($pw)
{
   // only letters and numbers allowed
   return preg_match(PASSWORD_REGEX, $pw);
}


function isValidUsername($name)
{
   // only letters and numbers allowed
    if ((strlen($name) < 20) && preg_match(USERNAME_REGEX, $name))
    {
        return true;
    } 
   return false;
}

function isValidEmail($email){
    $clean_email = "";
    $clean_email = filter_var($email,FILTER_SANITIZE_EMAIL);
    if (filter_var($clean_email,FILTER_VALIDATE_EMAIL)){
        return true;
    } else {
        return false;
    }
}

?>