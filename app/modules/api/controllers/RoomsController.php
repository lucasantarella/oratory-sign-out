<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/28/17
 * Time: 12:57 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use \Oratorysignout\Models\Rooms as Rooms;
use \Oratorysignout\Models\Students as Students;
use \Oratorysignout\Models\StudentsSchedules as StudentsSchedules;
use Phalcon\Filter;

class RoomsController extends ControllerBase
{

	public function roomAction($name = '')
	{
		if (strlen($name) == 0)
			return $this->sendNotFound();

		$room = Rooms::findFirst($name);
		if ($room === false)
			return $this->sendNotFound();
		else
			return $this->sendResponse($room);
	}

	public function currentStudentsAction($name = '')
	{
		if (strlen($name) == 0)
			return $this->sendNotFound();

		$room = Rooms::findFirst($name);
		if ($room === false)
			return $this->sendNotFound();

		$info = SchedulesController::getDateTimeInfo($this->request->getQuery('date', Filter::FILTER_ABSINT, (int)date('YmdHis')));

		if ($info === false || $info['period'] === false)
			return $this->sendNotFound();

		// Get users in room, minus those that are signed out
		$builder = $this->modelsManager->createBuilder()
			->from(["StudentsSchedules" => "\\Oratorysignout\\Models\\StudentsSchedules", "Students" => "\\Oratorysignout\\Models\\Students"])
			->columns("Students.*")
			->innerJoin("Students", "Students.id = StudentsSchedules.student_id")
			->where("StudentsSchedules.quarter = :quarter:")
			->andWhere("StudentsSchedules.cycle_day = :cycleDay:")
			->andWhere("StudentsSchedules.schedule_id = :schedule_id:")
			->andWhere("StudentsSchedules.period = :period:")
			->andWhere("StudentsSchedules.room = :room:");

		$query = $this->modelsManager->createQuery($builder->getPhql())->execute([
			'quarter' => $info['quarter'],
			'cycleDay' => $info['cycleDay'],
			'schedule_id' => $info['schedule']->id,
			'period' => $info['period'],
			'room' => $room->name,
		]);

		if (count($query) == 0 || $query === false)
			return $this->sendBadRequest();

		var_dump($query);
		return $this->sendResponse($query);
	}

}