<?php

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Rooms;
use Oratorysignout\Models\Students;
use Oratorysignout\Models\StudentsSchedules;
use Oratorysignout\Models\Teachers;
use Oratorysignout\Models\TeachersSchedules;

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->sendResponse(['test' => 'test']);
    }

    public function importStudentsAction()
    {
        /* Map Rows and Loop Through Them */
        $rows = array_map('str_getcsv', file($_FILES['schedules']['tmp_name']));
        $header = array_shift($rows);
        $csv = array();
        foreach ($rows as $row) {
            $csv[] = array_combine($header, $row);
        }

        $periods = [
            'Period 1' => 1,
            'Common  Work Period' => 2,
            'Period 2' => 3,
            'Period 3A' => 4,
            'Period 3B' => 5,
            'Period 3C' => 6,
            'Period 4' => 7,
        ];

        $response = [];

        $this->db->begin();

        foreach ($csv as $row) {
            $student = Students::findFirst("email = '{$row['StsAdrs_1_01_Cnts_1_01_Contactnum']}'");
            if ($student === false) {
                $student = new Students([
                    'first_name' => $row['StsSt_Firstname'],
                    'last_name' => $row['StsSt_Lastname'],
                    'email' => $row['StsAdrs_1_01_Cnts_1_01_Contactnum'],
                    'grad_year' => $row['StsEnrlls_1_01_Classof']
                ]);
                if (!$student->create()) {
                    // Revert transaction
                    $this->db->rollback();
                    $errors = [];
                    foreach ($student->getMessages() as $message) {
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
            }

            unset($row['StsSt_Firstname']);
            unset($row['StsSt_Lastname']);
            unset($row['StsAdrs_1_01_Cnts_1_01_Contactnum']);
            unset($row['StsEnrlls_1_01_Classof']);
            $values = array_values($row);
            $schedules = [];

            for ($i = 0; $i < count($row); $i += 3) {
                if (is_numeric($values[$i])) {
                    $room = Rooms::findFirst("name = '{$values[$i + 2]}'");
                    if ($room === false) {
                        $room = new Rooms([
                            'name' => $values[$i + 2]
                        ]);
                        if (!$room->create()) {
                            // Revert transaction
                            $this->db->rollback();
                            $errors = [];
                            foreach ($room->getMessages() as $message) {
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
                    }

                    foreach (explode(', ', $values[$i + 1]) as $period_str) {
                        $schedules[] = new StudentsSchedules([
                            'student_id' => $student->id,
                            'quarter' => 4,
                            'cycle_day' => $values[$i],
                            'period' => (array_key_exists($period_str, $periods)) ? $periods[$period_str] : $period_str,
                            'room' => $room->name
                        ]);
                    }
                }
            }

            // Delete all the old schedules
            $this->modelsManager->createQuery("DELETE FROM Oratorysignout\\Models\\StudentsSchedules WHERE Oratorysignout\\Models\\StudentsSchedules.student_id = :student_id:")->execute(['student_id' => $student->id]);

            foreach ($schedules as $schedule) {
                if (!$schedule->create()) {
                    // Revert transaction
                    $this->db->rollback();
                    $errors = [];
                    foreach ($schedule->getMessages() as $message) {
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
            }

            $response[] = [
                'student' => $student,
                'schedules' => $schedules
            ];
        }

        $this->db->commit();

        return $this->sendResponse($response);

    }

    public function importTeachersAction()
    {
        /* Map Rows and Loop Through Them */
        $rows = array_map('str_getcsv', file($_FILES['schedules']['tmp_name']));
        $header = array_shift($rows);
        $csv = array();
        foreach ($rows as $row) {
            $csv[] = array_combine($header, $row);
        }

        $periods = [
            'Period 1' => 1,
            'Common  Work Period' => 2,
            'Period 2' => 3,
            'Period 3A' => 4,
            'Period 3B' => 5,
            'Period 3C' => 6,
            'Period 4' => 7,
        ];

        $response = [];

//        $this->db->begin();

        foreach ($csv as $row) {

            $values = array_values($row);
            $schedules = [];

            if (isset($values[4]) && isset($values[5]) && strlen($values[4]) > 0 && strlen($values[5]) > 0) {

                $teacher = Teachers::findFirst("email = '{$values[5]}'");
                if ($teacher === false) {
                    $teacher = new Teachers([
                        'last_name' => $values[4],
                        'email' => $values[5]
                    ]);
                    if (!$teacher->create()) {
                        // Revert transaction
                        $this->db->rollback();
                        $errors = [];
                        foreach ($teacher->getMessages() as $message) {
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
                }

                for ($i = 0; $i < count($row); $i += 15) {
                    if (is_numeric($values[$i])) {
                        $room = Rooms::findFirst("name = '{$values[$i + 2]}'");
                        if ($room === false) {
                            $room = new Rooms([
                                'name' => $values[$i + 2]
                            ]);
                            if (!$room->create()) {
                                // Revert transaction
//                                $this->db->rollback();
                                $errors = [];
                                foreach ($room->getMessages() as $message) {
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
                        }

                        foreach (explode(', ', $values[$i + 1]) as $period_str) {
                            $schedules[] = new TeachersSchedules([
                                'teacher_id' => $teacher->id,
                                'quarter' => 4,
                                'cycle_day' => $values[$i],
                                'period' => (array_key_exists($period_str, $periods)) ? $periods[$period_str] : $period_str,
                                'room' => $room->name
                            ]);
                        }
                    }
                }

                // Delete all the old schedules
//                $this->modelsManager->createQuery("DELETE FROM Oratorysignout\\Models\\TeachersSchedules WHERE Oratorysignout\\Models\\TeachersSchedules.teacher_id = :teacher_id:")->execute(['teacher_id' => $teacher->id]);

                foreach ($schedules as $schedule) {
                    if (!$schedule->save()) {
                        // Revert transaction
//                        $this->db->rollback();
                        $errors = [];
                        foreach ($schedule->getMessages() as $message) {
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
                }

                $response[] = [
                    'teacher' => $teacher,
                    'schedules' => $schedules
                ];

            }
        }

//        $this->db->commit();

        return $this->sendResponse($response);

    }

}