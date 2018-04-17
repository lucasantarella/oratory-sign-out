<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 9/18/17
 * Time: 5:19 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Rooms;
use Oratorysignout\Models\Schedules;
use Oratorysignout\Models\SchedulesPeriods;
use Oratorysignout\Models\Teachers;
use Oratorysignout\Models\TeachersSchedules;
use Phalcon\Filter;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class TeachersController extends AuthRequiredControllerBase
{

    public function teachersAction()
    {
        $builder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\Teachers');

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

    public function teacherAction($id)
    {
        $teacher = Teachers::findFirst($id);
        if ($teacher !== false)
            return $this->sendResponse($teacher);
        else
            return $this->sendNotFound();
    }

    public function teacherScheduleAction($teacher_id, $date = null)
    {
        $teacher = Teachers::findFirst($teacher_id);
        if ($teacher === false)
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
            ->from('Oratorysignout\\Models\\TeachersSchedules')
            ->columns(['Oratorysignout\\Models\\TeachersSchedules.*', 'Oratorysignout\\Models\\SchedulesPeriods.*'])
            ->inWhere('Oratorysignout\\Models\\TeachersSchedules.period', $periodNums)
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.teacher_id = :teacher_id:')
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.quarter = :quarter:')
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.cycle_day = :cycle_day:')
            ->innerJoin('Oratorysignout\\Models\\SchedulesPeriods', 'Oratorysignout\\Models\\SchedulesPeriods.period = Oratorysignout\\Models\\TeachersSchedules.period AND Oratorysignout\\Models\\SchedulesPeriods.schedule_id = ' . $schedule->id)
            ->orderBy('Oratorysignout\\Models\\TeachersSchedules.period ASC');

        /** @var TeachersSchedules $query */
        $query = $builder->getQuery()->execute([
            'teacher_id' => $teacher_id,
            'quarter' => $quarter,
            'cycle_day' => $cycleDay,
        ]);

        $response = [];
        foreach ($query as $row) {
            /** @var SchedulesPeriods $period */
            $period = $row['oratorysignout\\Models\\SchedulesPeriods'];

            /** @var TeachersSchedules $teacherSchedule */
            $teacherSchedule = $row['oratorysignout\\Models\\TeachersSchedules'];

            $response[] = [
                'period' => (int)$period->period,
                'start_time' => $period->start_time,
                'end_time' => $period->end_time,
                'room' => $teacherSchedule->room
            ];
        }

        return $this->sendResponse($response);
    }

    public function signOutAction($teacher_id, $name_from = '')
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

        $teacher = Teachers::findFirst($teacher_id);
        if ($teacher === false)
            return $this->sendNotFound();

        $room_from = Rooms::findFirst("name = '{$room_from}'");
        if ($room_from === false)
            return $this->sendNotFound();

        $room_to = Rooms::findFirst("name = '{$room_to}'");
        if ($room_to === false)
            return $this->sendNotFound();

        // Begin transaction
        $this->db->begin();

        $log = new LogsTeachers();
        $log->teacher_id = $teacher_id;
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

        $teacher = Teachers::findFirst("email = '{$this->getUser()['email']}'");
        if ($teacher === false)
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
            ->from('Oratorysignout\\Models\\TeachersSchedules')
            ->columns(['Oratorysignout\\Models\\TeachersSchedules.*', 'Oratorysignout\\Models\\SchedulesPeriods.*'])
            ->inWhere('Oratorysignout\\Models\\TeachersSchedules.period', $periodNums)
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.teacher_id = :teacher_id:')
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.quarter = :quarter:')
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.cycle_day = :cycle_day:')
            ->innerJoin('Oratorysignout\\Models\\SchedulesPeriods', 'Oratorysignout\\Models\\SchedulesPeriods.period = Oratorysignout\\Models\\TeachersSchedules.period AND Oratorysignout\\Models\\SchedulesPeriods.schedule_id = ' . $schedule->id)
            ->orderBy('Oratorysignout\\Models\\TeachersSchedules.period ASC');

        $query = $builder->getQuery()->execute([
            'teacher_id' => $teacher->id,
            'quarter' => $quarter,
            'cycle_day' => $cycleDay,
        ]);

        $name_from = null;
        if (count($query) === 1) {
            $row = $query[0];

            /** @var SchedulesPeriods $period */
            $period = $row['oratorysignout\\Models\\SchedulesPeriods'];

            /** @var TeachersSchedules $teacherSchedule */
            $teacherSchedule = $row['oratorysignout\\Models\\TeachersSchedules'];

            $name_from = $teacherSchedule->room;
        } else return $this->sendNotFound();

        $builder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\LogsTeachers')
            ->columns(['Oratorysignout\\Models\\LogsTeachers.*'])
            ->where('(Oratorysignout\\Models\\LogsTeachers.timestamp BETWEEN :period_start: AND :period_end:) AND Oratorysignout\\Models\\LogsTeachers.teacher_id = :teacher_id:')
            ->orderBy('Oratorysignout\\Models\\LogsTeachers.timestamp DESC')
            ->limit(1);

        $params = [
            'teacher_id' => $teacher->id,
            'period_start' => strval($date) . $period->start_time . '00',
            'period_end' => strval($date) . $period->end_time . '00'
        ];

        /** @var LogsTeachers[] $query */
        $logsQuery = $builder->getQuery()->execute($params);

        if (count($logsQuery) === 1) {
            /** @var LogsTeachers $row */
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

        $log = new LogsTeachers();
        $log->teacher_id = $teacher->id;
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

    public function currentRoomAction()
    {
        $teacher = Teachers::findFirst("email = '{$this->getUser()['email']}'");
        if ($teacher === false)
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
            ->from('Oratorysignout\\Models\\TeachersSchedules')
            ->columns(['Oratorysignout\\Models\\TeachersSchedules.*', 'Oratorysignout\\Models\\SchedulesPeriods.*'])
            ->inWhere('Oratorysignout\\Models\\TeachersSchedules.period', $periodNums)
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.teacher_id = :teacher_id:')
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.quarter = :quarter:')
            ->andWhere('Oratorysignout\\Models\\TeachersSchedules.cycle_day = :cycle_day:')
            ->innerJoin('Oratorysignout\\Models\\SchedulesPeriods', 'Oratorysignout\\Models\\SchedulesPeriods.period = Oratorysignout\\Models\\TeachersSchedules.period AND Oratorysignout\\Models\\SchedulesPeriods.schedule_id = ' . $schedule->id)
            ->orderBy('Oratorysignout\\Models\\TeachersSchedules.period ASC');

        $query = $builder->getQuery()->execute([
            'teacher_id' => $teacher->id,
            'quarter' => $quarter,
            'cycle_day' => $cycleDay,
        ]);

        $response = [];
        if (count($query) === 1) {
            $row = $query[0];

            /** @var SchedulesPeriods $period */
            $period = $row['oratorysignout\\Models\\SchedulesPeriods'];

            /** @var TeachersSchedules $teacherSchedule */
            $teacherSchedule = $row['oratorysignout\\Models\\TeachersSchedules'];

            $response = [
                'period' => (int)$period->period,
                'start_time' => $period->start_time,
                'end_time' => $period->end_time,
                'room' => $teacherSchedule->room
            ];
        } else return $this->sendNotFound();

        return $this->sendResponse($response);
    }

}