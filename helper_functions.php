<?php

const MSG_OPEN = "O";
const MSG_CLOSE = "C";
const MSG_SEPARATOR = "/"; 
const PLACEHOLDER = "*";

const PASSWORD_REGEX = "/^[a-zA-Z0-9]+$/";
const username_REGEX = "/^[a-zA-Z0-9]+$/";


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


function isValidusername($name)
{
   // only letters and numbers allowed
    if (preg_match(username_REGEX, $name))
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