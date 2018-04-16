<?php

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Students;
use Oratorysignout\Models\StudentsSchedules;

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->sendResponse(['test' => 'test']);
    }

    public function importAction()
    {
        /* Map Rows and Loop Through Them */
        $rows = array_map('str_getcsv', file($_FILES['students']['tmp_name']));
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
                    'email' => $row['StsAdrs_1_01_Cnts_1_01_Contactnum']
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
            $values = array_values($row);
            $schedules = [];

            for ($i = 0; $i < count($row); $i += 3) {
                if (is_numeric($values[$i])) {
                    foreach (explode(', ', $values[$i + 1]) as $period_str) {
                        $schedules[] = new StudentsSchedules([
                            'student_id' => $student->id,
                            'quarter' => 4,
                            'cycle_day' => $values[$i],
                            'period' => (array_key_exists($period_str, $periods)) ? $periods[$period_str] : $period_str,
                            'room' => $values[$i + 2]
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

}