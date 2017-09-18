<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/28/17
 * Time: 8:25 AM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use DateTime;
use DateTimeZone;
use Oratorysignout\CommonLibrary;
use Oratorysignout\Models\Schedules;
use Oratorysignout\Models\SchedulesExceptions;
use Oratorysignout\Models\SchedulesPeriods;
use Oratorysignout\Models\SchedulesQuarters;
use Phalcon\Filter;

class SchedulesController extends ControllerBase
{

	public function scheduleAction($date = null)
	{
		if (is_null($date))
			$date = $this->request->getQuery('date', Filter::FILTER_INT_CAST, date('Ymd'));

		$cycleDay = self::getCycleDay($date);
		if ($cycleDay === false)
			return $this->sendResponse([
				'date' => (int)$date,
				'cycle_day' => false,
				'schedule' => null
			]);
		else
			return $this->sendResponse([
				'date' => (int)$date,
				'cycle_day' => $cycleDay,
				'schedule' => self::getSchedule($date)
			]);
	}

	public function schedulesAction($id = null)
	{
		if (!is_null($id)) {
			$schedule = Schedules::findFirst($id);
			if ($schedule === false)
				return $this->sendNotFound();
			else return $this->sendResponse($schedule);
		} else
			return $this->sendResponse(Schedules::find());
	}

	public function periodsAction($schedule_id = 0)
	{
		if ($schedule_id <= 0)
			return $this->sendNotFound();

		$schedule = Schedules::findFirst("id = {$schedule_id}");
		if ($schedule === false)
			return $this->sendNotFound();

		return $this->sendResponse($schedule->getPeriods());
	}

	public function periodsByDayAction($date = null)
	{
		if (is_null($date))
			$date = date('Ymd');

		$schedule = self::getSchedule($date);
		if ($schedule === false)
			return $this->sendNotFound();

		return $this->sendResponse($schedule->getPeriods());
	}

	public function periodAction($schedule_id = 0, $num = 0)
	{
		if ($schedule_id <= 0 || ($num <= 0 || $num > 7))
			return $this->sendNotFound();

		$schedule = Schedules::findFirst("id = {$schedule_id}");
		if ($schedule === false)
			return $this->sendNotFound();

		$period = $schedule->getPeriods("period = {$num}");
		if (count($period) > 0)
			return $this->sendResponse($period[0]);
		else
			return $this->sendNotFound();
	}

	public function periodByDayAction($date = null, $num = 0)
	{
		if ($num <= 0 || $num > 7)
			return $this->sendNotFound();

		$schedule = self::getSchedule($date);
		if ($schedule === false)
			return $this->sendNotFound();

		$period = $schedule->getPeriods("period = {$num}");
		if (count($period) > 0)
			return $this->sendResponse($period[0]);
		else
			return $this->sendNotFound();
	}

	public function periodTodayAction($num = 0)
	{
		$schedule = self::getSchedule(date('Ymd'));
		if ($schedule === false)
			return $this->sendNotFound();

		$period = $schedule->getPeriods("period = {$num}");
		if (count($period) > 0)
			return $this->sendResponse($period[0]);
		else
			return $this->sendNotFound();
	}

	/**
	 * Returns the schedule for a specified date, if possible.
	 * @param int $date
	 * @return bool|Schedules
	 */
	public static function getSchedule($date)
	{
		// If the requested day is a weekend, then it does not belong to the cycle
		if (CommonLibrary::isWeekend($date))
			return false;

		$exception = SchedulesExceptions::findFirst("(ignored_from_cycle = 1 OR ignore_day = 1) AND (date = {$date})");
		if ($exception !== false) {
			if ($exception->ignore_day) // If the exception is to ignore the day (i.e. no school) then return false
				return false;
			if (!is_null($exception->schedule_id)) // If the exception is a specific schedule type, return that
				return $exception->schedule_id;

			return Schedules::getDefault();
		} else
			return Schedules::getDefault();
	}

	/**
	 * @param $monthDay
	 * @return bool|int
	 */
	public static function getQuarter($monthDay)
	{
		$quarter = SchedulesQuarters::findFirst("start_date <= {$monthDay} AND end_date >= {$monthDay}");
		if ($quarter !== false)
			return (int)$quarter->quarter_num;
		else return false;
	}

	/**
	 * @param string $datetime YmdHis formatted timestamp.
	 * @return array|bool
	 */
	public static function getDateTimeInfo($datetime)
	{
		$date = DateTime::createFromFormat('YmdHis', $datetime, new DateTimeZone('America/New_York'));
		if ($date === false)
			return false;

		$yearMonthDay = $date->format('Ymd');
		$monthDay = $date->format('md');
		$hourMinute = $date->format('Hi');
		$quarter = self::getQuarter($monthDay);
		$cycleDay = self::getCycleDay($yearMonthDay);
		$schedule = self::getSchedule($yearMonthDay);
		$period = ($cycleDay !== false && $schedule !== false) ? SchedulesPeriods::findAtTime($hourMinute, $schedule) : false;

		return [
			'quarter' => $quarter,
			'cycleDay' => $cycleDay,
			'schedule' => $schedule,
			'period' => $period,
		];
	}

	/**
	 * @param string $fromDate
	 * @return array
	 */
	public static function getNonCycleDays($fromDate = null)
	{
		if (is_null($fromDate))
			$fromDate = getenv('CYCLE_START_DATE');

		// Find days that have overridden cycle days or are ignored from the calendar
		$ignored = SchedulesExceptions::find("(ignored_from_cycle = 1 OR ignore_day = 1) AND (date >= " . $fromDate . ")");
		$ignoredArray = [];
		foreach ($ignored as $date) {
			$ignoredArray[] = (int)$date->date;
		}

		return $ignoredArray;
	}

	/**
	 * @param $date
	 * @return int|false
	 */
	public static function getCycleDay($date)
	{
		// If the requested day is a weekend, then it does not belong to the cycle
		if (CommonLibrary::isWeekend($date))
			return false;

		$exception = SchedulesExceptions::findFirst($date);
		if ($exception !== false) {
			if ($exception->ignore_day) // If the exception is to ignore the day (i.e. no school) then return false
				return false;
			if (!is_null($exception->cycle_day_override)) // If the exception is a specific cycle day, return that
				return $exception->cycle_day_override;
		}

		$ignored = self::getNonCycleDays();
		$result = CommonLibrary::getWorkingDays(getenv('CYCLE_START_DATE'), $date, $ignored) % 8;
		return ($result <= 0) ? 8 : $result;
	}

}