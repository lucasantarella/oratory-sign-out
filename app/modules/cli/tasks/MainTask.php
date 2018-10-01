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
use Phalcon\Filter;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use React\EventLoop\Factory;
use ZMQ;

/**
 * Class MainTask
 * @package Oratorysignout\Modules\Cli\Tasks
 */
class MainTask extends \Phalcon\Cli\Task implements MessageComponentInterface, WsServerInterface
{
    /** @var \SplObjectStorage $clients */
    private $clients;

    /** @var ConnectionInterface[] */
    private $connectedUsers = [];

    public function mainAction()
    {
        $this->clients = new \SplObjectStorage;

        $loop = Factory::create();
        $wsServer = new \Ratchet\WebSocket\WsServer($this);
        $wsServer->enableKeepAlive($loop);
        $wsServer->setStrictSubProtocolCheck(false);

        // Listen for the web server to make a ZeroMQ push after an ajax request
        $context = new \React\ZMQ\Context($loop);
        $pull = $context->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
        $pull->on('message', array($this, 'handleMessage'));

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

        $this->clients->attach($conn, ['email' => $conn->user['email']]);
        $this->connectedUsers[$conn->user['email']] = $conn;

        echo "[" . date('YmdHis') . "] User " . $conn->user['email'] . " connected" . PHP_EOL;
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        echo "[" . date('YmdHis') . "] Connection Closed" . PHP_EOL;
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

    public function handleMessage($payload)
    {
        $obj = unserialize($payload);
        if (is_array($obj) && array_key_exists('handler', $obj) && array_key_exists('data', $obj)) {
            $handler = $this->di->getShared('filter')->sanitize($obj['handler'], Filter::FILTER_ALPHANUM);
            if (isset($handler) && isset($obj['data']) && method_exists($this, $handler))
                try {
                    echo "Calling method \$this->{$handler} with data: " . PHP_EOL;
                     echo json_encode($obj['data'], JSON_PRETTY_PRINT);
                    echo PHP_EOL;
                    $this->{$handler}($obj['data']);
                } catch (\Exception $e) {
                    echo $e->getTraceAsString() . PHP_EOL;
                };
        }
    }

    /**
     * @param LogsStudents $log
     */
    public function onSignOut($log)
    {
        $from_room = $log->getRoomFrom();
        $to_room = $log->getRoomTo();

        $date = (int)date('YmdHis');
        $info = Schedules::getDateTimeInfo($date);

        if ($info === false || $info['period'] === false) return;

        /** @var SchedulesPeriods $period */
        $period = $info['period'];

        $periodStartTime = (int)(substr($date, 0, 8) . $period->start_time . '00');
        $periodEndTime = (int)(substr($date, 0, 8) . $period->end_time . '00');

        // Get the teachers that are in the room
        $scheduledTeachersBuilder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\Rooms')
            ->columns(['Oratorysignout\\Models\\Teachers.email', 'Oratorysignout\\Models\\TeachersSchedules.room'])
            ->where('Oratorysignout\\Models\\Rooms.name = :from_room: OR Oratorysignout\\Models\\Rooms.name = :to_room:', ['from_room' => $from_room->name, 'to_room' => $to_room->name])
            ->innerJoin('Oratorysignout\\Models\\TeachersSchedules', 'Oratorysignout\\Models\\Rooms.name = Oratorysignout\\Models\\TeachersSchedules.room AND Oratorysignout\\Models\\TeachersSchedules.period = ' . $period->period . ' AND Oratorysignout\\Models\\TeachersSchedules.quarter = ' . $info['quarter'] . ' AND Oratorysignout\\Models\\TeachersSchedules.cycle_day = ' . $info['cycleDay'])
            ->innerJoin('Oratorysignout\\Models\\Teachers', 'Oratorysignout\\Models\\Teachers.id = Oratorysignout\\Models\\TeachersSchedules.teacher_id')
            ->groupBy(['Oratorysignout\\Models\\Teachers.id']);

        // Iterate over teachers who are scheduled and send them notifications
        foreach ($scheduledTeachersBuilder->getQuery()->execute() as $row) {
            $email = $row['email'];
            $room = $row['room'];
            if (isset($this->connectedUsers[$email])) {
                $conn = $this->connectedUsers[$email];
                $conn->send(json_encode(['data_type' => 'update', 'data' => ['room' => $room]]));
            }
        }

        // Get the teacher for the room the student is scheduled for to update current location
        $builder = $this->modelsManager->createBuilder()
            ->from('Oratorysignout\\Models\\StudentsSchedules')
            ->columns(['Oratorysignout\\Models\\StudentsSchedules.*', 'Oratorysignout\\Models\\Teachers.*'])
            ->where('Oratorysignout\\Models\\StudentsSchedules.period = :period:')
            ->andWhere('Oratorysignout\\Models\\StudentsSchedules.student_id = :student_id:')
            ->andWhere('Oratorysignout\\Models\\StudentsSchedules.quarter = :quarter:')
            ->andWhere('Oratorysignout\\Models\\StudentsSchedules.cycle_day = :cycle_day:')
            ->innerJoin('Oratorysignout\\Models\\TeachersSchedules', 'Oratorysignout\\Models\\StudentsSchedules.room = Oratorysignout\\Models\\TeachersSchedules.room AND Oratorysignout\\Models\\TeachersSchedules.period = ' . $period->period . ' AND Oratorysignout\\Models\\TeachersSchedules.quarter = ' . $info['quarter'] . ' AND Oratorysignout\\Models\\TeachersSchedules.cycle_day = ' . $info['cycleDay'])
            ->innerJoin('Oratorysignout\\Models\\Teachers', 'Oratorysignout\\Models\\Teachers.id = Oratorysignout\\Models\\TeachersSchedules.teacher_id');

        $query = $builder->getQuery()->execute([
            'student_id' => $log->student_id,
            'quarter' => $info['quarter'],
            'cycle_day' => $info['cycleDay'],
            'period' => $period->period
        ]);

        echo "[" . date('YmdHis') . "] User " . $log->getStudent()->email . " signed out to: " . $log->room_to . " from " . $log->room_from . PHP_EOL;

        foreach ($query as $row) {
            /** @var StudentsSchedules $studentSchedule */
            $studentSchedule = $row['oratorysignout\\Models\\StudentsSchedules'];

            /** @var Teachers $teacher */
            $teacher = $row['oratorysignout\\Models\\Teachers'];

            if (isset($this->connectedUsers[$teacher->email])) {
                $conn = $this->connectedUsers[$teacher->email];
                echo "[" . date('YmdHis') . "] Teacher " . $teacher->email . " alerted" . PHP_EOL;
                $conn->send(json_encode(['data_type' => 'update', 'data' => ['room' => $studentSchedule->room]]));
            }
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