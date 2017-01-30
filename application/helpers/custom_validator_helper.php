<?php

function valid_datetime($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function valid_date($date)
{
    return valid_datetime($date, 'Y-m-d');
}
