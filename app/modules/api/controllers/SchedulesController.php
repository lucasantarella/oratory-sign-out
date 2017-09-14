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

class SchedulesController extends ControllerBase
{

	public function scheduleAction($date = null)
	{
		if (is_null($date))
			$date = date('Ymd');

		return $this->sendResponse(array_merge(self::getSchedule($date)->jsonSerialize(), ['date' => (int)$date]));
	}

	public function schedulesAction($id = 0)
	{
		if ($id <= 0)
			return $this->sendNotFound();

		$schedule = Schedules::findFirst("id = {$id}");
		if ($schedule === false)
			return $this->sendNotFound();
		else
			return $this->sendResponse($schedule);
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

		$period = $schedule->getPeriods("period = {$num}");
		if (count($period) > 0)
			return $this->sendResponse($period[0]);
		else
			return $this->sendNotFound();
	}

	public function periodTodayAction($num = 0)
	{
		$schedule = self::getSchedule(date('Ymd'));
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
		$exception = SchedulesExceptions::findFirst("ignored = 0 AND date = {$date}");
		if ($exception !== false)
			$schedule = $exception->getSchedule();
		else
			$schedule = Schedules::getDefault();

		return $schedule;
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
		$period = SchedulesPeriods::findAtTime($hourMinute, $schedule);

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

		$ignored = SchedulesExceptions::find("ignored = 1 AND date >= " . $fromDate);
		$ignoredArray = [];
		foreach ($ignored as $date) {
			$ignoredArray[] = (int)$date->date;
		}

		return $ignoredArray;
	}

	/**
	 * @param $day
	 * @return int
	 */
	public static function getCycleDay($day)
	{
		$ignored = self::getNonCycleDays();
		$result = CommonLibrary::getWorkingDays(getenv('CYCLE_START_DATE'), $day, $ignored) % 8;
		return ($result === 0) ? 8 : $result;
	}

}