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

		$cycleDay = Schedules::getCycleDay($date);
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
				'schedule' => Schedules::getSchedule($date)
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

		$schedule = Schedules::getSchedule($date);
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

		$schedule = Schedules::getSchedule($date);
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
		$schedule = Schedules::getSchedule(date('Ymd'));
		if ($schedule === false)
			return $this->sendNotFound();

		$period = $schedule->getPeriods("period = {$num}");
		if (count($period) > 0)
			return $this->sendResponse($period[0]);
		else
			return $this->sendNotFound();
	}


}