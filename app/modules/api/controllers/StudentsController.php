<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 9/18/17
 * Time: 5:19 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\LogsStudents;
use Oratorysignout\Models\Rooms;
use Oratorysignout\Models\Schedules;
use Oratorysignout\Models\SchedulesPeriods;
use Oratorysignout\Models\Students;
use Oratorysignout\Models\StudentsSchedules;
use Phalcon\Filter;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use ZMQ;
use ZMQContext;

class StudentsController extends AuthRequiredControllerBase
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
            ->innerJoin('Oratorysignout\\Models\\SchedulesPeriods', 'Oratorysignout\\Models\\SchedulesPeriods.period = Oratorysignout\\Models\\StudentsSchedules.period AND Oratorysignout\\Models\\SchedulesPeriods.schedule_id = ' . $schedule->id)
            ->orderBy('Oratorysignout\\Models\\StudentsSchedules.period ASC');

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

    public function signOutAction($student_id, $name_from = '')
    {
        $filter = new Filter();

        $requestBody = $this->request->getJsonRawBody(true);

        if (strlen($name_from) == 0 && isset($requestBody['room_from']))
            $room_from = $filter->sanitize($requestBody['room_from'], Filter::FILTER_STRING);
        else
            return $this->sendNotFound();

        if (!isset($requestBody['room_to']))
            return $this->sendNotFound();
        else
            $room_to = $filter->sanitize($requestBody['room_to'], Filter::FILTER_STRING);

        $student = Students::findFirst($student_id);
        if ($student === false)
            return $this->sendNotFound();

        $room_from = Rooms::findFirst("name = '{$room_from}'");
        if ($room_from === false)
            return $this->sendNotFound();

        $room_to = Rooms::findFirst("name = '{$room_to}'");
        if ($room_to === false)
            return $this->sendNotFound();

        // Begin transaction
        $this->db->begin();

        $log = new LogsStudents();
        $log->student_id = $student_id;
        $log->timestamp = (isset($requestBody['timestamp']) ? $requestBody['timestamp'] : (int)date('YmdHis'));
        $log->room_from = $room_from->name;
        $log->room_to = $room_to->name;

        if (!$log->create()) {
            // Revert transaction
            $this->db->rollback();
            $errors = [];
            foreach ($log->getMessages() as $message) {
                $error = [];
                $error['message'] = $message->getMessage();
                $error['field'] = $message->getField();
                $error['type'] = $message->getType();
                array_push($errors, $error);
            }
            return $this->sendBadRequest([
                "errors" => $errors
            ]);
        }

        // Commit transaction
        $this->db->commit();

        // Send response
        return $this->sendResponse($log);
    }

    public function signMeOutAction()
    {
        $filter = new Filter();

        $requestBody = $this->request->getJsonRawBody(true);

        $student = Students::findFirst("email = '{$this->getUser()['email']}'");
        if ($student === false)
            return $this->sendNotFound();

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
        $periods = $schedule->getPeriods("start_time <= " . date('Hi') . " AND end_time > " . date('Hi'));
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
            ->innerJoin('Oratorysignout\\Models\\SchedulesPeriods', 'Oratorysignout\\Models\\SchedulesPeriods.period = Oratorysignout\\Models\\StudentsSchedules.period AND Oratorysignout\\Models\\SchedulesPeriods.schedule_id = ' . $schedule->id)
            ->orderBy('Oratorysignout\\Models\\StudentsSchedules.period ASC');

        $query = $builder->getQuery()->execute([
            'student_id' => $student->id,
            'quarter' => $quarter,
            'cycle_day' => $cycleDay,
        ]);

        $name_from = null;
        if (count($query) === 1) {
            $row = $query[0];

            /** @var SchedulesPeriods $period */
            $period = $row['oratorysignout\\Models\\SchedulesPeriods'];

            /** @var StudentsSchedules $studentSchedule */
            $studentSchedule = $row['oratorysignout\\Models\\StudentsSchedules'];

            $name_from = $studentSchedule->room;
        } else return $this->sendNotFound();

        $builder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\LogsStudents')
            ->columns(['Oratorysignout\\Models\\LogsStudents.*'])
            ->where('(Oratorysignout\\Models\\LogsStudents.timestamp BETWEEN :period_start: AND :period_end:) AND Oratorysignout\\Models\\LogsStudents.student_id = :student_id:')
            ->orderBy('Oratorysignout\\Models\\LogsStudents.timestamp DESC')
            ->limit(1);

        $params = [
            'student_id' => $student->id,
            'period_start' => strval($date) . $period->start_time . '00',
            'period_end' => strval($date) . $period->end_time . '00'
        ];

        /** @var LogsStudents[] $query */
        $logsQuery = $builder->getQuery()->execute($params);

        if (count($logsQuery) === 1) {
            /** @var LogsStudents $row */
            $row = $logsQuery[0];
            $name_from = $row->room_to;
        }

        if (strlen($name_from) === 0 && isset($requestBody['room_from']))
            $room_from = $filter->sanitize($requestBody['room_from'], Filter::FILTER_STRING);

        if (!isset($requestBody['room_to']))
            return $this->sendNotFound();
        else
            $room_to = $filter->sanitize($requestBody['room_to'], Filter::FILTER_STRING);

        $room_from = Rooms::findFirst("name = '{$name_from}'");
        if ($room_from === false)
            return $this->sendNotFound();

        $room_to = Rooms::findFirst("name = '{$room_to}'");
        if ($room_to === false)
            return $this->sendNotFound();

        // Begin transaction
        $this->db->begin();

        $log = new LogsStudents();
        $log->student_id = $student->id;
        $log->timestamp = (isset($requestBody['timestamp']) ? $requestBody['timestamp'] : (int)date('YmdHis'));
        $log->room_from = $room_from->name;
        $log->room_to = $room_to->name;

        if (!$log->create()) {
            // Revert transaction
            $this->db->rollback();
            $errors = [];
            foreach ($log->getMessages() as $message) {
                $error = [];
                $error['message'] = $message->getMessage();
                $error['field'] = $message->getField();
                $error['type'] = $message->getType();
                array_push($errors, $error);
            }
            return $this->sendBadRequest([
                "errors" => $errors
            ]);
        }

        // Commit transaction
        $this->db->commit();

        // Notify recipients
        $context = new \ZMQContext();
        try {
            $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'signout_pusher');
            $socket->connect("tcp://127.0.0.1:5555");
            $socket->send(json_encode($log));
        } catch (\ZMQSocketException $e) {
            die($e->getTraceAsString());
        }

        // Send response
        return $this->sendResponse($log);
    }

    public function currentRoomAction()
    {
        $student = Students::findFirst("email = '{$this->getUser()['email']}'");
        if ($student === false)
            return $this->sendNotFound();

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
        $periods = $schedule->getPeriods("start_time <= " . date('Hi') . " AND end_time > " . date('Hi'));
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
            ->innerJoin('Oratorysignout\\Models\\SchedulesPeriods', 'Oratorysignout\\Models\\SchedulesPeriods.period = Oratorysignout\\Models\\StudentsSchedules.period AND Oratorysignout\\Models\\SchedulesPeriods.schedule_id = ' . $schedule->id)
            ->orderBy('Oratorysignout\\Models\\StudentsSchedules.period ASC');

        $query = $builder->getQuery()->execute([
            'student_id' => $student->id,
            'quarter' => $quarter,
            'cycle_day' => $cycleDay,
        ]);

        $response = [];
        if (count($query) === 1) {
            $row = $query[0];

            /** @var SchedulesPeriods $period */
            $period = $row['oratorysignout\\Models\\SchedulesPeriods'];

            /** @var StudentsSchedules $studentSchedule */
            $studentSchedule = $row['oratorysignout\\Models\\StudentsSchedules'];

            $response = [
                'period' => (int)$period->period,
                'start_time' => $period->start_time,
                'end_time' => $period->end_time,
                'room' => $studentSchedule->room
            ];
        } else return $this->sendNotFound();

        $builder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\LogsStudents')
            ->columns(['Oratorysignout\\Models\\LogsStudents.*'])
            ->where('(Oratorysignout\\Models\\LogsStudents.timestamp BETWEEN :period_start: AND :period_end:) AND Oratorysignout\\Models\\LogsStudents.student_id = :student_id:')
            ->orderBy('Oratorysignout\\Models\\LogsStudents.timestamp DESC')
            ->limit(1);

        $params = [
            'student_id' => $student->id,
            'period_start' => strval($date) . $period->start_time . '00',
            'period_end' => strval($date) . $period->end_time . '00'
        ];

        /** @var LogsStudents[] $query */
        $logsQuery = $builder->getQuery()->execute($params);

        if (count($logsQuery) === 1) {
            /** @var LogsStudents $row */
            $row = $logsQuery[0];
            $response['room'] = $row->room_to;
        }

        return $this->sendResponse($response);
    }

}