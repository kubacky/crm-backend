<?php

function formatDate($date, $format = 'd-m-Y H:i') {
    $months = array(array('styczeń', 'stycznia'),
        array('luty', 'lutego'),
        array('marzec', 'marca'),
        array('kwiecień', 'kwietnia'),
        array('maj', 'maja'),
        array('czerwiec', 'czerwca'),
        array('lipiec', 'lipca'),
        array('sierpień', 'sierpnia'),
        array('wrzesień', 'września'),
        array('październik', 'października'),
        array('listopad', 'listopada'),
        array('grudzień', 'grudnia'));
    $days = array('poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek', 'sobota', 'niedziela');

    $date = strtotime($date);

    $day = date('N', $date);
    $month = date('m', $date) - 1;

    $format = str_split($format);
    $return = '';

    foreach ($format as $char) {
        switch ($char) {
            case 'Dy':
                $return .= ucfirst($days[$day]);
                break;
            case 'dy':
                $return .= $days[$day];
                break;
            case 'M':
                $return .= ucfirst($months[$month][1]);
                break;
            case 'n':
                $return .= $months[$month][1];
                break;
            case 'F':
                $return .= ucfirst($months[$month][0]);
                break;
            case 'f':
                $return .= $months[$month][0];
                break;
            case ' ':
                $return .= ' ';
                break;
            case '-':
                $return .= '-';
                break;
            case ':':
                $return .= ':';
                break;
            default:
                $return .= date($char, $date);
        }
    }
    return $return;
}

function setShippingTime($type, $value) {
    if ($type == 0) {
        $shipping_time = $value . ' godz.';
    } else {
        if ($value == 1) {
            $shipping_time = $value . ' dzień';
        } else {
            $shipping_time = $value . ' dni';
        }
    }
    return $shipping_time;
}

function getMonthDays($month, $year = null) {
    if ($year == null) {
        $year = date('Y');
    }
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

function getPaymentDate($days, $date) {
    return date('Y-m-d', strtotime($date . ' + ' . $days . ' days'));
}

function countNextDays($period) {
  return ($period == 12) ? '+ 365 days' : '+ 730 days';
}

function isCurrentYear($date) {
  $current = date('Y');
  $checked = date('Y', strtotime($date));

  return $current === $checked;
}
