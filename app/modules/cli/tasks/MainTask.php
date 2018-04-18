<?php

namespace Oratorysignout\Modules\Cli\Tasks;

use DateTime;
use Google_Client;
use Oratorysignout\Models\LogsStudents;
use Oratorysignout\Models\Schedules;
use Oratorysignout\Models\SchedulesPeriods;
use Oratorysignout\Models\Students;
use Oratorysignout\Models\StudentsSchedules;
use Oratorysignout\Models\Teachers;
use Oratorysignout\Models\TeachersSchedules;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use React\EventLoop\Factory;

/**
 * Class MainTask
 * @package Oratorysignout\Modules\Cli\Tasks
 */
class MainTask extends \Phalcon\Cli\Task implements MessageComponentInterface, WsServerInterface
{
    public function mainAction()
    {
        $loop = Factory::create();
        $wsServer = new \Ratchet\WebSocket\WsServer($this);
        $wsServer->enableKeepAlive($loop);
        $wsServer->setStrictSubProtocolCheck(false);

        $webSock = new \React\Socket\Server('0.0.0.0:9090', $loop); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new \Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                $wsServer
            ),
            $webSock
        );

        $loop->run();
    }

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        $cookiesRaw = $conn->httpRequest->getHeader('Cookie');

        if (count($cookiesRaw)) {
            $cookiesArr = \GuzzleHttp\Psr7\parse_header($cookiesRaw)[0]; // Array of cookies
        }

        if (getenv('TIME_OVERRIDE') !== false && extension_loaded('timecop')) {
            timecop_return();
        }

        $client = new Google_Client();
        $result = $client->verifyIdToken(base64_decode($cookiesArr['gtoken']));
        if ($result === false) {
            $conn->close();
        } else
            $conn->user = $result;

        if (getenv('TIME_OVERRIDE') !== false && extension_loaded('timecop')) {
            $time = DateTime::createFromFormat('YmdHis', getenv('TIME_OVERRIDE'));
            timecop_freeze(mktime($time->format('H'), $time->format('i'), $time->format('s'), $time->format('m'), $time->format('d'), $time->format('Y')));
        }

        echo "User " . $conn->user['email'] . " connected" . PHP_EOL;
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        echo "Connection Closed" . PHP_EOL;
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo $e->getTraceAsString();
    }

    public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
    {
        $json = json_decode($msg, true);
        if (!array_key_exists('action', $json) || !array_key_exists('value', $json)) return;
        switch ($json['action']) {
            case 'get':
                if ($json['value'] == 'currentroom') {
                    $date = (int)date('Ymd');
                    $quarter = Schedules::getQuarter(substr(strval($date), 4, 4));
                    $schedule = Schedules::getSchedule($date);
                    if ($schedule === false) return;

                    $cycleDay = Schedules::getCycleDay($date);
                    if ($cycleDay === false) return;

                    /** @var SchedulesPeriods $periods */
                    $periods = $schedule->getPeriods("start_time <= " . date('Hi') . " AND end_time > " . date('Hi'));
                    $periodNums = [];
                    foreach ($periods as $period) {
                        $periodNums[] = (int)$period->period;
                    }
                    unset($period);

                    if (count(explode('.student@oratoryprep', $conn->user['email'])) === 2) {
                        $student = Students::findFirst("email = '{$conn->user['email']}'");
                        if ($student === false) return;

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

                        } else $response = null;

                        return $conn->send(json_encode([
                            'data_type' => 'room',
                            'data' => $response
                        ]));
                    } else {
                        $teacher = Teachers::findFirst("email = '{$conn->user['email']}'");
                        if ($teacher === false) return;

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

                        if (count($query) === 1) {
                            $row = $query[0];

                            /** @var SchedulesPeriods $period */
                            $period = $row['oratorysignout\\Models\\SchedulesPeriods'];

                            /** @var TeachersSchedules $teacherSchedule */
                            $teacherSchedule = $row['oratorysignout\\Models\\TeachersSchedules'];

                            return $conn->send(json_encode([
                                'data_type' => 'room',
                                'data' => [
                                    'period' => (int)$period->period,
                                    'start_time' => $period->start_time,
                                    'end_time' => $period->end_time,
                                    'room' => $teacherSchedule->room
                                ]
                            ]));
                        } else return $conn->send(json_encode([
                            'data_type' => 'room',
                            'data' => null
                        ]));
                    }
                }
                break;
        }
    }

    /**
     * If any component in a stack supports a WebSocket sub-protocol return each supported in an array
     * @return array
     * @todo This method may be removed in future version (note that will not break code, just make some code obsolete)
     */
    function getSubProtocols()
    {
        return ['student', 'teacher'];
    }

}