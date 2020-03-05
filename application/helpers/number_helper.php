<?php

require_once(APPPATH . 'third_party' . DIRECTORY_SEPARATOR . 'Kwota.php');

function say($value) {
    $say = str_replace(array(' ', ','), array('', '.'), $value);
    return Kwota::getInstance()->slownie($say);
}

function formatAccountNumber($accountNumber) {
    $newNumber = '';
    $parts = array(0 => 2, 2 => 4, 6 => 4, 10 => 4, 14 => 4, 18 => 4, 22 => 4);
    foreach ($parts as $key => $val) {
        $newNumber .= substr($accountNumber, $key, $val) . ' ';
    }
    return trim($newNumber);
}
