<?php


namespace App\Core\Modules;


use DateTime;

class DatetimeUtility
{
    public static function getMySQLDateTime($datetime): bool|string
    {

        $dateTime = DateTime::createFromFormat('d-m-Y H:i', $datetime);

        if ($dateTime === false) {
            return false;
        }

        $now = new DateTime();

        if ($dateTime < $now) {
            return false;
        }

        return $dateTime->format('Y-m-d H:i:s');
    }

    public static function addIntervalToNow($daysInterval, $hour): bool|string
    {
        $time = explode(":", $hour);
        if (count($time) < 2) {
            return false;
        }

        $datetime = new DateTime();

        $datetime->modify('+' . $daysInterval . ' days');

        $datetime->setTime($time[0], $time[1], 0);

        $now = new DateTime();

        if ($datetime < $now) {
            return false;
        }

        return $datetime->format('Y-m-d H:i');
    }
}