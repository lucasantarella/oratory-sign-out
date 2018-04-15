<?php

/* Map Rows and Loop Through Them */
$rows = array_map('str_getcsv', file(__DIR__ . '/students.csv'));
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

foreach ($csv as $row) {
    $student = $row;
    unset($student['StsSt_Firstname']);
    unset($student['StsSt_Lastname']);
    unset($student['StsAdrs_1_01_Cnts_1_01_Contactnum']);
    $values = array_values($student);
    $schedule = [];

    for ($i = 0; $i < count($student); $i += 3) {
        if (is_numeric($values[$i])) {
            foreach (explode(', ', $values[$i + 1]) as $period_str) {
                $schedule[] = [
                    'cycle_day' => $values[$i],
                    'period' => (array_key_exists($period_str, $periods)) ? $periods[$period_str] : $period_str,
                    'room' => $values[$i - 1]
                ];
            }
        }
    }

    array_multisort(
        array_column($schedule, 'cycle_day'), SORT_ASC,
        array_column($schedule, 'period'), SORT_ASC,
        $schedule);
    var_dump($schedule);
}
