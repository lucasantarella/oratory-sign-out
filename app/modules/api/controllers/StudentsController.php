<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 9/18/17
 * Time: 5:19 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Schedules;
use Oratorysignout\Models\SchedulesPeriods;
use Oratorysignout\Models\Students;
use Oratorysignout\Models\StudentsSchedules;
use Phalcon\Filter;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class StudentsController extends ControllerBase
{

	public function studentsAction()
	{
		$builder = $this->modelsManager->createBuilder()
			->from('Oratorysignout\\Models\\Students');

		$paginator = new PaginatorQueryBuilder(
			[
				"builder" => $builder,
				"limit" => $this->request->getQuery("per_page", Filter::FILTER_INT_CAST, 20),
				"page" => $this->request->getQuery("page", Filter::FILTER_INT_CAST, 1),
			]
		);
		$paginate = $paginator->getPaginate();

		$this->response->setHeader('X-Paginate-Total-Pages', $paginate->total_pages);
		$this->response->setHeader('X-Paginate-Total-Items', $paginate->total_items);
		$this->response->setHeader('X-Paginate-Current-Page', $paginate->current);
		return $this->sendResponse($paginate->items);
	}

	public function studentAction($id)
	{
		$student = Students::findFirst($id);
		if ($student !== false)
			return $this->sendResponse($student);
		else
			return $this->sendNotFound();
	}

	public function studentScheduleAction($student_id, $date = null)
	{
		$student = Students::findFirst($student_id);
		if ($student === false)
			return $this->sendNotFound();

		if (is_null($date))
			$date = (int)date('Ymd');

		$quarter = Schedules::getQuarter(substr(strval($date), 4, 4));

		$schedule = Schedules::getSchedule($date);
		if ($schedule === false)
			return $this->sendBadRequest([
				'status' => 'Error',
				'status_details' => 'No schedule for the specified date.'
			]);

		$cycleDay = Schedules::getCycleDay($date);
		if ($cycleDay === false)
			return $this->sendBadRequest([
				'status' => 'Error',
				'status_details' => 'No schedule for the specified date.'
			]);

		/** @var SchedulesPeriods $periods */
		$periods = $schedule->getPeriods();

		$periodNums = [];
		foreach ($periods as $period) {
			$periodNums[] = (int)$period->period;
		}
		unset($period);

		$builder = $this->modelsManager->createBuilder()
			->from('Oratorysignout\\Models\\StudentsSchedules')
			->columns(['Oratorysignout\\Models\\StudentsSchedules.*', 'Oratorysignout\\Models\\SchedulesPeriods.*'])
			->inWhere('Oratorysignout\\Models\\StudentsSchedules.period', $periodNums)
			->andWhere('Oratorysignout\\Models\\StudentsSchedules.student_id = :student_id:')
			->andWhere('Oratorysignout\\Models\\StudentsSchedules.quarter = :quarter:')
			->andWhere('Oratorysignout\\Models\\StudentsSchedules.cycle_day = :cycle_day:')
			->innerJoin('Oratorysignout\\Models\\SchedulesPeriods', 'Oratorysignout\\Models\\SchedulesPeriods.period = Oratorysignout\\Models\\StudentsSchedules.period AND Oratorysignout\\Models\\SchedulesPeriods.schedule_id = ' . $schedule->id);

		/** @var StudentsSchedules $query */
		$query = $builder->getQuery()->execute([
			'student_id' => $student_id,
			'quarter' => $quarter,
			'cycle_day' => $cycleDay,
		]);

		$response = [];
		foreach ($query as $row) {
			/** @var SchedulesPeriods $period */
			$period = $row['oratorysignout\\Models\\SchedulesPeriods'];

			/** @var StudentsSchedules $studentSchedule */
			$studentSchedule = $row['oratorysignout\\Models\\StudentsSchedules'];

			$response[] = [
				'period' => (int)$period->period,
				'start_time' => $period->start_time,
				'end_time' => $period->end_time,
				'room' => $studentSchedule->room
			];
		}

		return $this->sendResponse($response);
	}

}