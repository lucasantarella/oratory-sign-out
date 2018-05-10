<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/28/17
 * Time: 12:57 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\LogsStudents;
use Oratorysignout\Models\Rooms;
use Oratorysignout\Models\Schedules;
use Oratorysignout\Models\SchedulesPeriods;
use Oratorysignout\Models\Students;
use Phalcon\Filter;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class RoomsController extends ControllerBase
{

    public function roomsAction()
    {
        $builder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\Rooms');

        return $this->sendResponse($builder->getQuery()->execute());
    }

    public function roomAction($name = '')
    {
        if (strlen($name) == 0)
            return $this->sendNotFound();

        $room = Rooms::findFirst("name = '{$name}'");
        if ($room === false)
            return $this->sendNotFound();
        else
            return $this->sendResponse($room);
    }

    public function presentStudentsAction($name = '')
    {
        if (strlen($name) == 0)
            return $this->sendNotFound();

        $room = Rooms::findFirst("name = '{$name}'");
        if ($room === false)
            return $this->sendNotFound();

        $date = $this->request->getQuery('date', Filter::FILTER_ABSINT, (int)date('YmdHis'));

        $info = Schedules::getDateTimeInfo($date);

        if ($info === false || $info['period'] === false)
            return $this->sendResponse([]);

        /** @var SchedulesPeriods $period */
        $period = $info['period'];

        $periodStartTime = (int)(substr($date, 0, 8) . $period->start_time . '00');
        $periodEndTime = (int)(substr($date, 0, 8) . $period->end_time . '00');

        // Get users in room, minus those that are signed out
        $scheduledStudentsBuilder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\Rooms')
            ->columns(['Oratorysignout\\Models\\Rooms.*', 'Oratorysignout\\Models\\Students.*', 'Oratorysignout\\Models\\LogsStudents.*'])
            ->where('Oratorysignout\\Models\\Rooms.name = :room:', ['room' => $room->name])
            ->innerJoin('Oratorysignout\\Models\\StudentsSchedules', 'Oratorysignout\\Models\\Rooms.name = Oratorysignout\\Models\\StudentsSchedules.room AND Oratorysignout\\Models\\StudentsSchedules.period = ' . $period->period . ' AND Oratorysignout\\Models\\StudentsSchedules.quarter = ' . $info['quarter'] . ' AND Oratorysignout\\Models\\StudentsSchedules.cycle_day = ' . $info['cycleDay'])
            ->innerJoin('Oratorysignout\\Models\\Students', 'Oratorysignout\\Models\\Students.id = Oratorysignout\\Models\\StudentsSchedules.student_id')
            ->leftJoin('Oratorysignout\\Models\\LogsStudents', '(Oratorysignout\\Models\\LogsStudents.timestamp BETWEEN ' . $periodStartTime . ' AND ' . $periodEndTime . ') AND Oratorysignout\\Models\\Students.id = Oratorysignout\\Models\\LogsStudents.student_id AND Oratorysignout\\Models\\LogsStudents.latest = 1')
            ->groupBy(['Oratorysignout\\Models\\Students.id']);

        // Get users signed into a room
        $signedInStudentsBuilder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\Rooms')
            ->columns(['Oratorysignout\\Models\\Rooms.*', 'Oratorysignout\\Models\\Students.*', 'Oratorysignout\\Models\\LogsStudents.*'])
            ->where('Oratorysignout\\Models\\Rooms.name = :room:', ['room' => $room->name])
            ->leftJoin('Oratorysignout\\Models\\LogsStudents', '(Oratorysignout\\Models\\LogsStudents.timestamp BETWEEN ' . $periodStartTime . ' AND ' . $periodEndTime . ')  AND Oratorysignout\\Models\\LogsStudents.room_to = "' . $room->name . '" AND Oratorysignout\\Models\\LogsStudents.latest = 1')
            ->innerJoin('Oratorysignout\\Models\\Students', 'Oratorysignout\\Models\\Students.id = Oratorysignout\\Models\\LogsStudents.student_id')
            ->groupBy(['Oratorysignout\\Models\\Students.id']);

        /** @var array $response */
        $response = [];

        // Iterate over students who are scheduled and determine if they are signed out or not
        foreach ($scheduledStudentsBuilder->getQuery()->execute() as $row) {
            /** @var Students $student */
            $student = $row['oratorysignout\\Models\\Students'];

            /** @var LogsStudents $log */
            $log = $row['oratorysignout\\Models\\LogsStudents'];

            if (isset($response[$student->id]) && isset($response[$student->id]['signout_id']) && $response[$student->id]['signout_id'] < $log->id) {
                $response[$student->id]['status'] = 'signedout';
                $response[$student->id]['signout_id'] = $log->id;
                $response[$student->id]['signedout_room'] = $log->room_to;
            } else
                $response[$student->id] = array_merge($student->jsonSerialize(), [
                    'status' => ($log->id === 0) ? 'scheduled' : (($log->confirmed) ? 'signedout_confirmed' : 'signedout_unconfirmed'),
                    'signout_id' => $log->id,
                    'signedout_room' => $log->room_to
                ]);
        }

        // Iterate over students who are signed in and see if they are confirmed or not
        foreach ($signedInStudentsBuilder->getQuery()->execute() as $row) {
            /** @var Students $student */
            $student = $row['oratorysignout\\Models\\Students'];

            /** @var LogsStudents $log */
            $log = $row['oratorysignout\\Models\\LogsStudents'];

            if ($log->id > 0 && isset($response[$student->id]['signout_id']) && $response[$student->id]['signout_id'] < $log->id) {
                $response[$student->id]['status'] = ($log->confirmed) ? 'signedin_confirmed' : 'signedin_unconfirmed';
                $response[$student->id]['signout_id'] = $log->id;
            } else
                $response[$student->id] = array_merge($student->jsonSerialize(), [
                    'status' => ($log->confirmed) ? 'signedin_confirmed' : 'signedin_unconfirmed',
                    'signout_id' => (int)$log->id
                ]);
        }

        return $this->sendResponse(array_values($response));
    }

}