<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/28/17
 * Time: 8:33 AM
 */

namespace Oratorysignout;


class CommonLibrary
{

    /**
     * Returns the no. of business days between two dates and it skips the ignored
     * @param $startDate
     * @param $endDate
     * @param array $ignored
     * @return float|int
     */
    public static function getWorkingDays($startDate, $endDate, $ignored = [])
    {
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);
        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;
        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);
        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);
        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        } else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)
            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;
                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            } else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }
        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0) {
            $workingDays += $no_remaining_days;
        }
        //We subtract the ignored
        foreach ($ignored as $ignore) {
            $time_stamp = strtotime($ignore);
            //If the ignore doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N", $time_stamp) != 6 && date("N", $time_stamp) != 7)
                $workingDays--;
        }

        return $workingDays;
    }

    /**
     * @param null|string|int $date
     * @return bool
     */
    public static function isWeekend($date = null)
    {
        if (is_null($date))
            $date = date('Ymd');

        return date('w', strtotime($date)) % 6 == 0;
    }

}