<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/28/17
 * Time: 8:25 AM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Schedules;
use Oratorysignout\Models\SchedulesExceptions;
use Oratorysignout\Models\SchedulesPeriods;

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
		$date = date('YmdHis');

		$schedule = self::getSchedule($date);

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
	protected static function getSchedule($date)
	{
		$exception = SchedulesExceptions::findFirst("ignored = 0 AND date = {$date}");
		if ($exception !== false)
			$schedule = $exception->getSchedule();
		else
			$schedule = Schedules::getDefault();

		return $schedule;
	}

}