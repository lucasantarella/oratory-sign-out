<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 9/18/17
 * Time: 5:19 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Students;
use Phalcon\Filter;
use Phalcon\Paginator\Adapter\QueryBuilder AS PaginatorQueryBuilder;

class StudentsController extends ControllerBase
{

	public function studentsAction()
	{
		$builder = $this->modelsManager->createBuilder()
			->from('Oratorysignout\\Models\\Students');

		$paginator = new PaginatorQueryBuilder(
			[
				"builder" => $builder,
				"limit"   => $this->request->getQuery("limit", Filter::FILTER_INT_CAST, 20),
				"page"    => $this->request->getQuery("page", Filter::FILTER_INT_CAST, 1),
			]
		);

		return $this->sendResponse($paginator->getPaginate()->items);
	}

	public function studentAction($id)
	{
		$student = Students::findFirst($id);
		if ($student !== false)
			return $this->sendResponse($student);
		else
			return $this->sendNotFound();
	}

}