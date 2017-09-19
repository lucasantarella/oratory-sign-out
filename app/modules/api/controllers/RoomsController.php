<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/28/17
 * Time: 12:57 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Rooms;
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
				"limit" => $this->request->getQuery("per_page", Filter::FILTER_INT_CAST, 25),
				"page" => $this->request->getQuery("page", Filter::FILTER_INT_CAST, 1),
			]
		);
		$paginate = $paginator->getPaginate();

		$this->response->setHeader('X-Paginate-Total-Pages', $paginate->total_pages);
		$this->response->setHeader('X-Paginate-Total-Items', $paginate->total_items);
		$this->response->setHeader('X-Paginate-Current-Page', $paginate->current);
		return $this->sendResponse($paginate->items);
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

		$info = Schedules::getDateTimeInfo($this->request->getQuery('date', Filter::FILTER_ABSINT, (int)date('YmdHis')));

		if ($info === false || $info['period'] === false)
			return $this->sendBadRequest();

		// Get users in room, minus those that are signed out
		$builder = $this->modelsManager->createBuilder()
			->from('Oratorysignout\\Models\\StudentsSchedules')
			->columns(['Oratorysignout\\Models\\Students.*'])
			->where('Oratorysignout\\Models\\StudentsSchedules.room = :room:', ['room' => $room->name])
			->andWhere('Oratorysignout\\Models\\StudentsSchedules.period = :period:', ['period' => $info['period']->period])
			->andWhere('Oratorysignout\\Models\\StudentsSchedules.quarter = :quarter:', ['quarter' => $info['quarter']])
			->andWhere('Oratorysignout\\Models\\StudentsSchedules.cycle_day = :cycle_day:', ['cycle_day' => $info['cycleDay']])
			->innerJoin('Oratorysignout\\Models\\Students', 'Oratorysignout\\Models\\Students.id = Oratorysignout\\Models\\StudentsSchedules.student_id')
			->groupBy('Oratorysignout\\Models\\Students.id');

		$paginator = new PaginatorQueryBuilder(
			[
				"builder" => $builder,
				"limit" => $this->request->getQuery("per_page", Filter::FILTER_INT_CAST, 25),
				"page" => $this->request->getQuery("page", Filter::FILTER_INT_CAST, 1),
			]
		);
		$paginate = $paginator->getPaginate();

		$this->response->setHeader('X-Paginate-Total-Pages', $paginate->total_pages);
		$this->response->setHeader('X-Paginate-Total-Items', $paginate->total_items);
		$this->response->setHeader('X-Paginate-Current-Page', $paginate->current);
		return $this->sendResponse($paginate->items);
	}

}