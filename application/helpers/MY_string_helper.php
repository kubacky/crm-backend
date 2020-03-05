<?php

function getPlainText($subject, $replacement = '_')
{

    $return = stripHtml($subject);
    $return = repPlChars(mb_strtolower($return, 'UTF-8'));
    $return = urlencode(repSymbol($return, $replacement));
    $return = preg_replace('/[_][\-][' . $replacement . '][\.][\+]+/', '_', $return);
    return trim($return, $replacement);
}

function stripHtml($subject)
{
    $regex_html = '/(?:<|&lt;)\/?([a-zA-Z]+) *[^<\/]*?(?:>|&gt;)/';

    $subject = str_replace('&nbsp;', '', $subject);
    $subject = strip_tags($subject);
    return preg_replace($regex_html, '', $subject);
}

function repPlChars($subject)
{

    $pl = array('ą', 'Ą', 'Ć', 'ć', 'Ę', 'ę', 'Ł', 'ł', 'Ń', 'ń', 'Ó', 'ó', 'Ś', 'ś', 'Ź', 'ź', 'Ż', 'ż');
    $in = array('a', 'A', 'C', 'c', 'E', 'e', 'L', 'l', 'N', 'n', 'O', 'o', 'S', 's', 'Z', 'z', 'Z', 'z');
    return str_replace($pl, $in, $subject);
}

function formatNip($nip)
{
    $parts = str_split($nip);
    $prefix = '';
    $number = '';
    for ($i = 0; $i < sizeof($parts); $i++) {
        if (is_numeric($parts[$i])) {
            $number .= $parts[$i];
        } else {
            $prefix .= $parts[$i];
        }
    }
    $number = substr_replace($number, '-', 3, 0);
    $number = substr_replace($number, '-', 6, 0);
    $number = substr_replace($number, '-', 9, 0);
    return (strlen($prefix) == 0) ? $number : $prefix . ' ' . $number;
}

function boolToInt($value, $unsigned = false)
{
    if ($unsigned == false) {
        if ($value == 'true') {
            return 1;
        } else {
            return 0;
        }
    } else {
        if ($value == 'true') {
            return 1;
        } else {
            return 2;
        }
    }
}

function intToBool($value)
{
    if ($value == 1) {
        return true;
    } else {
        return false;
    }
}

function repSymbol($subject, $replacement = '_')
{
    $regex_symbol = '/[^A-Za-z0-9]/';

    $return = preg_replace($regex_symbol, $replacement, $subject);
    return preg_replace('/[\_]+/', $replacement, $return);
}

function pre($array)
{

    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

function formatPostcode($postcode)
{
    $prefix = substr($postcode, 0, 2);
    $suffix = substr($postcode, 2, 3);

    return $prefix . '-' . $suffix;
}

function get_user_ip()
{
    $ip = getenv('HTTP_CLIENT_IP') ?:
    getenv('HTTP_X_FORWARDED_FOR') ?:
    getenv('HTTP_X_FORWARDED') ?:
    getenv('HTTP_FORWARDED_FOR') ?:
    getenv('HTTP_FORWARDED') ?:
    getenv('REMOTE_ADDR');

    return substr($ip, 0, 15);
}
