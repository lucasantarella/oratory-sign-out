<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/28/17
 * Time: 12:57 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Rooms as Rooms;
use Oratorysignout\Models\Schedules;
use Phalcon\Filter;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class RoomsController extends ControllerBase
{

	public function roomsAction()
	{
		$builder = $this->modelsManager->createBuilder()
			->from('Oratorysignout\\Models\\Rooms');

		$paginator = new PaginatorQueryBuilder(
			[
				"builder" => $builder,
				"limit"   => $this->request->getQuery("limit", Filter::FILTER_INT_CAST, 20),
				"page"    => $this->request->getQuery("page", Filter::FILTER_INT_CAST, 1),
			]
		);

		return $this->sendResponse($paginator->getPaginate()->items);

	}

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

		$room = Rooms::findFirst("name = '{$name}'");
		if ($room === false)
			return $this->sendNotFound();

		$info = PaginatorQueryBuilder::getDateTimeInfo($this->request->getQuery('date', Filter::FILTER_ABSINT, (int)date('YmdHis')));

		if ($info === false || $info['period'] === false)
			return $this->sendBadRequest();

		// Get users in room, minus those that are signed out
		// Setup Phalcon Query
		$phql = "SELECT
                \\Oratorysignout\\Models\\Students.*
            FROM \\Oratorysignout\\Models\\StudentsSchedules
            INNER JOIN \\Oratorysignout\\Models\\Students
                ON \\Oratorysignout\\Models\\StudentsSchedules.student_id = \\Oratorysignout\\Models\\Students.id
            WHERE
                \\Oratorysignout\\Models\\StudentsSchedules.quarter = :quarter: AND
                \\Oratorysignout\\Models\\StudentsSchedules.cycle_day = :cycle_day: AND
                \\Oratorysignout\\Models\\StudentsSchedules.period = :period: AND
                \\Oratorysignout\\Models\\StudentsSchedules.room = :room:
            GROUP BY \\Oratorysignout\\Models\\Students.id
        ";

		$query = $this->modelsManager->executeQuery($phql, [
			'quarter' => $info['quarter'],
			'cycle_day' => $info['cycleDay'],
			'period' => $info['period']->period,
			'room' => $room->name,
		]);

		if (count($query) == 0 || $query === false)
			return $this->sendResponse([]);

		return $this->sendResponse($query);
	}

}