<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 4/25/17
 * Time: 6:12 PM
 */

date_default_timezone_set('America/New_York');
error_reporting(E_ALL);

const START_CYCLE_DATE = "20170406";

/**
 * @param string $message
 * @param $body
 */
function send($message = '', $body)
{
	echo("\n" . $message . "```json\n" .json_encode($body, JSON_PRETTY_PRINT) . "\n```\n\n");
}

/**
 * @return PDO
 */
function getPDO()
{
	return new PDO('mysql:host=localhost;dbname=oratory_sign_out', 'root', '');
}

/**
 * Returns the no. of business days between two dates and it skips the ignored
 * @param $startDate
 * @param $endDate
 * @param array $ignored
 * @return float|int
 */
function getWorkingDays($startDate, $endDate, $ignored = [])
{
	// do strtotime calculations just once
	$endDate = strtotime($endDate);
	$startDate = strtotime($startDate);


	//The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
	//We add one to inlude both dates in the interval.
	$days = ($endDate - $startDate) / 86400 + 1;

	$no_full_weeks = floor($days / 7);
	$no_remaining_days = fmod($days, 7);

	//It will return 1 if it's Monday,.. ,7 for Sunday
	$the_first_day_of_week = date("N", $startDate);
	$the_last_day_of_week = date("N", $endDate);

	//---->The two can be equal in leap years when february has 29 days, the equal sign is added here
	//In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
	if ($the_first_day_of_week <= $the_last_day_of_week) {
		if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
		if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
	} else {
		// (edit by Tokes to fix an edge case where the start day was a Sunday
		// and the end day was NOT a Saturday)

		// the day of the week for start is later than the day of the week for end
		if ($the_first_day_of_week == 7) {
			// if the start date is a Sunday, then we definitely subtract 1 day
			$no_remaining_days--;

			if ($the_last_day_of_week == 6) {
				// if the end date is a Saturday, then we subtract another day
				$no_remaining_days--;
			}
		} else {
			// the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
			// so we skip an entire weekend and subtract 2 days
			$no_remaining_days -= 2;
		}
	}

	//The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
	$workingDays = $no_full_weeks * 5;
	if ($no_remaining_days > 0) {
		$workingDays += $no_remaining_days;
	}

	//We subtract the ignored
	foreach ($ignored as $ignore) {
		$time_stamp = strtotime($ignore);
		//If the ignore doesn't fall in weekend
		if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N", $time_stamp) != 6 && date("N", $time_stamp) != 7)
			$workingDays--;
	}

	return $workingDays - 1;
}

/**
 * @param string $fromDate
 * @return array
 */
function getNonCycleDays($fromDate = START_CYCLE_DATE)
{
	$ignoredQuery = getPDO()->prepare('SELECT * FROM schedules__exceptions WHERE ignored = 1 AND date >= :date');
	$ignoredQuery->execute(['date' => $fromDate]);
	return array_column($ignoredQuery->fetchAll(PDO::FETCH_ASSOC), 'date');
}

/**
 * @param int $schedule_id
 * @return array
 */
function getSchedulePeriods($schedule_id = 1)
{
	$periods = getPDO()->prepare('SELECT period, start_time, end_time FROM schedules__periods WHERE schedules__periods.schedule_id = :schedule_id');
	$periods->execute(['schedule_id' => (int)$schedule_id]);
//	if ($periods->rowCount() > 0)
	return $periods->fetchAll(PDO::FETCH_ASSOC);
//	else return getSchedulePeriods(getDefaultSchedule()['id']);
}

/**
 * @param $date
 * @return bool|array
 */
function getSchedule($date)
{
	$exceptionsQuery = getPDO()->prepare('SELECT schedules__exceptions.schedule_id as id, schedules__.name FROM schedules__exceptions LEFT JOIN schedules__ ON schedules__exceptions.schedule_id = schedules__.id WHERE ignored = 0 AND date = :date ');
	$exceptionsQuery->execute(['date' => (int)$date]);
	if ($exceptionsQuery->rowCount() == 1)
		return $exceptionsQuery->fetch(PDO::FETCH_ASSOC);
	else return getDefaultSchedule();
}

/**
 * @return bool|array
 */
function getDefaultSchedule()
{
	$scheduleQuery = getPDO()->query('SELECT schedules__.id, schedules__.name FROM schedules__ WHERE schedules__.default = 1');
	if ($scheduleQuery->rowCount() == 1)
		return $scheduleQuery->fetch(PDO::FETCH_ASSOC);
	else return false;
}

/**
 * @param $day
 * @return int
 */
function getCycleDay($day)
{
	$ignored = getNonCycleDays();

	$result = getWorkingDays(START_CYCLE_DATE, $day, $ignored) % 8;

	return ($result === 0) ? 8 : $result;
}

/**
 * @param $time
 * @param int $schedule_id
 * @return bool|int
 */
function getPeriod($time, $schedule_id = 0)
{
	if ($schedule_id === 0)
		$schedule_id = getDefaultSchedule()['id'];

	$exceptionsQuery = getPDO()->prepare('SELECT schedules__periods.period FROM schedules__periods WHERE start_time <= :time AND end_time >= :time AND schedule_id = :schedule_id');
	$exceptionsQuery->execute(['time' => (int)$time, 'schedule_id' => (int)$schedule_id]);
	if ($exceptionsQuery->rowCount() == 1)
		return (int)$exceptionsQuery->fetch(PDO::FETCH_ASSOC)['period'];
	else return false;
}

/**
 * @param $monthDay
 * @return bool|int
 */
function getQuarter($monthDay)
{
	$quarterQuery = getPDO()->prepare('SELECT schedules__quarters.quarter_num FROM schedules__quarters WHERE schedules__quarters.start_date <= :date AND schedules__quarters.end_date >= :date GROUP BY quarter_num');
	$quarterQuery->execute(['date' => (int)$monthDay]);
	if ($quarterQuery->rowCount() == 1)
		return (int)$quarterQuery->fetch(PDO::FETCH_ASSOC)['quarter_num'];
	else return false;
}

/**
 * @param string $datetime YmdHis formatted timestamp.
 * @return array|bool
 */
function getDateTimeInfo($datetime)
{
	$date = DateTime::createFromFormat('YmdHis', $datetime, new DateTimeZone('America/New_York'));
	if ($date === false)
		return false;

	$yearMonthDay = $date->format('Ymd');
	$monthDay = $date->format('md');
	$hourMinute = $date->format('Hi');

	$quarter = getQuarter($monthDay);
	$cycleDay = getCycleDay($yearMonthDay);
	$schedule = getSchedule($yearMonthDay);
	$periods = getSchedulePeriods($schedule['id']);
	$period = getPeriod($hourMinute, $schedule['id']);

	return [
		'quarter' => $quarter,
		'cycleDay' => $cycleDay,
		'schedule' => array_merge($schedule, ['periods' => $periods]),
		'period' => $period,
	];
}

/**
 * @param int $student_id
 * @param int $quarter_num
 * @param int $cycle_day
 * @param int $period
 * @return bool
 */
function getStudentRoom($student_id = 0, $quarter_num = 1, $cycle_day = 1, $period = 1)
{
	$roomQuery = getPDO()->prepare('SELECT * FROM students__schedules WHERE students__schedules.student_id = :id AND students__schedules.quarter = :quarter AND students__schedules.cycle_day = :cycle_day AND students__schedules.period = :period');
	$roomQuery->execute([
		'id' => (int)$student_id,
		'quarter' => (int)$quarter_num,
		'cycle_day' => (int)$cycle_day,
		'period' => (int)$period,
	]);
	if ($roomQuery->rowCount() == 1)
		return $roomQuery->fetch(PDO::FETCH_ASSOC)['room'];
	else return false;
}

/**
 * @param $student_id
 * @param $datetime
 * @return string
 */
function getStudentDesignatedRoom($student_id, $datetime)
{
	$studentQuery = getPDO()->prepare('SELECT students__.* FROM students__ WHERE students__.id = :id');
	$studentQuery->execute(['id' => (int)$student_id]);
	if ($studentQuery->rowCount() == 1) {
		$student = $studentQuery->fetch(PDO::FETCH_ASSOC);

		$scheduleInfo = getDateTimeInfo($datetime);

		return getStudentRoom($student['id'], $scheduleInfo['quarter'], $scheduleInfo['cycleDay'], $scheduleInfo['period']);

	} else return false;
}

/**
 * @param $room
 * @param $datetime
 * @return array
 */
function getStudentsDesignatedInRoom($room, $datetime)
{
	$scheduleInfo = getDateTimeInfo($datetime);
	$studentsQuery = getPDO()->prepare('SELECT students__.* FROM students__schedules LEFT JOIN students__ ON students__schedules.student_id = students__.id WHERE students__schedules.room = :room AND students__schedules.quarter = :quarter AND students__schedules.cycle_day = :cycle_day AND students__schedules.period = :period');
	$studentsQuery->execute([
		'room' => $room,
		'quarter' => (int)$scheduleInfo['quarter'],
		'cycle_day' => (int)$scheduleInfo['cycleDay'],
		'period' => (int)$scheduleInfo['period'],
	]);
	return $studentsQuery->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param $student_id
 * @return bool|mixed
 */
function getStudent($student_id)
{
	$studentQuery = getPDO()->prepare('SELECT * FROM students__ WHERE id = :id');
	$studentQuery->execute(['id' => $student_id]);
	if ($studentQuery->rowCount() === 1)
		return $studentQuery->fetch(PDO::FETCH_ASSOC);
	else return false;
}

/**
 * @param $student_id
 * @param $date
 */
function getStudentSchedule($student_id, $date)
{
	$student = getStudent($student_id);
	if ($student !== false) {
		$schedule = getSchedule($date);
		$cycleDay = getCycleDay($date);

		$periodsAndRoomsQuery = getPDO()->prepare('SELECT schedules__periods.period, students__schedules.room, schedules__periods.start_time, schedules__periods.end_time FROM schedules__periods LEFT JOIN students__schedules ON students__schedules.period = schedules__periods.period AND students__schedules.student_id = :student_id AND students__schedules.cycle_day = :cycle_day WHERE schedules__periods.schedule_id = :schedule_id');
		$periodsAndRoomsQuery->execute([
			'schedule_id' => $schedule['id'],
			'cycle_day' => $cycleDay,
			'student_id' => $student['id'],
		]);
		return $periodsAndRoomsQuery->fetchAll(PDO::FETCH_ASSOC);
	} else return false;
}

send("Cycle Day for 04/26/2017:\n", getCycleDay(20170426));

send("Schedule Type for 04/26/2017:\n", getSchedule(20170426));

send("Period Times for 04/26/2017:\n", getSchedulePeriods(getSchedule(20170426)['id']));

send("Schedule Info for 04/26/2017 at 12:00:00 PM:\n", getDateTimeInfo(20170426120000));

send("Period No. for a(n) 'Daily Schedule' scheduled day at 12:00:00 PM:\n", getPeriod(1200, 1));

send("Period No. for a(n) 'Late X' scheduled day at 12:00:00 PM:\n", getPeriod(1200, 2));

send("Period No. for a(n) 'Early X' scheduled day at 12:00:00 PM:\n", getPeriod(1200, 3));

send("Period No. for a(n) 'Delayed Opening' scheduled day at 12:00:00 PM:\n", getPeriod(1200, 4));

send("Period No. for a(n) 'Half Day' scheduled day at 12:00:00 PM:\n", getPeriod(1200, 5));

send("Room Name for Student #1 in Quarter 4 on Cycle Day #2 during Period #5:\n", getStudentRoom(1, 4, 2, 5));

send("Room Name for Student #1 on 04/26/2017 at 12:00:00 PM\n", getStudentDesignatedRoom(1, 20170426120000));

send("Students in Room '204' on 04/26/2017 at 12:00:00 PM\n", getStudentsDesignatedInRoom('204', 20170426120000));

send("Schedule for Student #1 on 04/26/2017:\n", getStudentSchedule(1, 20170426));

send("Schedule for Student #2 on 04/26/2017:\n", getStudentSchedule(2, 20170426));