<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/28/17
 * Time: 12:57 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Rooms;

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

}