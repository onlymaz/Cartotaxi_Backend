<?php
use Carbon\Carbon;


function utc_time($date,$timezone='UTC')
{
    $date_now = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $date),$timezone);
    $date_now->setTimezone('UTC');
    return $date_now;
}

function getOptionsTimes($time){
	$range=range(strtotime(date($time)),strtotime("23:59"),15*60);
    return $range;
}   

if (!function_exists('get_table_name')) {
    function get_table_name($model) {
        return (new $model)->getTable();
    }
}

