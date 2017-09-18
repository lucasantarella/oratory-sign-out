<?php

namespace Oratorysignout\Models;

use DateTime;
use DateTimeZone;
use Oratorysignout\CommonLibrary;
use Oratorysignout\Modules\Api\Controllers\SchedulesController;
use Phalcon\Db\Column;
use Phalcon\Mvc\Model\MetaData;

/**
 * Schedules
 *
 * @package Oratorysignout\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2017-04-27, 16:23:05
 */
class Schedules extends \Phalcon\Mvc\Model
{

	/**
	 *
	 * @var string
	 * @Primary
	 * @Column(type="string", length=20, nullable=false)
	 */
	public $id;

	/**
	 *
	 * @var string
	 * @Column(type="string", length=50, nullable=false)
	 */
	public $name;

	/**
	 *
	 * @var integer
	 * @Column(type="integer", length=1, nullable=true)
	 */
	public $default;

	/**
	 * Returns the schedule for a specified date, if possible.
	 * @param int $date
	 * @return bool|Schedules
	 */
	public static function getSchedule($date)
	{
		// If the requested day is a weekend, then it does not belong to the cycle
		if (CommonLibrary::isWeekend($date))
			return false;

		$exception = SchedulesExceptions::findFirst("(ignored_from_cycle = 1 OR ignore_day = 1) AND (date = {$date})");
		if ($exception !== false) {
			if ($exception->ignore_day) // If the exception is to ignore the day (i.e. no school) then return false
				return false;
			if (!is_null($exception->schedule_id)) // If the exception is a specific schedule type, return that
				return $exception->schedule_id;

			return Schedules::getDefault();
		} else
			return Schedules::getDefault();
	}

	/**
	 * @param $monthDay
	 * @return bool|int
	 */
	public static function getQuarter($monthDay)
	{
		$quarter = SchedulesQuarters::findFirst("start_date <= {$monthDay} AND end_date >= {$monthDay}");
		if ($quarter !== false)
			return (int)$quarter->quarter_num;
		else return false;
	}

	/**
	 * @param string $datetime YmdHis formatted timestamp.
	 * @return array|bool
	 *          'quarter' => SchedulesQuarters,
	 *          'cycleDay' => int|boolean,
	 *          'schedule' => Schedules,
	 *          'period' => SchedulesPeriods,
	 */
	public static function getDateTimeInfo($datetime)
	{
		$date = DateTime::createFromFormat('YmdHis', $datetime, new DateTimeZone('America/New_York'));
		if ($date === false)
			return false;

		$yearMonthDay = $date->format('Ymd');
		$monthDay = $date->format('md');
		$hourMinute = $date->format('Hi');
		$quarter = self::getQuarter($monthDay);
		$cycleDay = self::getCycleDay($yearMonthDay);
		$schedule = self::getSchedule($yearMonthDay);
		$period = ($cycleDay !== false && $schedule !== false) ? SchedulesPeriods::findAtTime($hourMinute, $schedule) : false;

		return [
			'quarter' => $quarter,
			'cycleDay' => $cycleDay,
			'schedule' => $schedule,
			'period' => $period,
		];
	}

	/**
	 * @param string $fromDate
	 * @return array
	 */
	public static function getNonCycleDays($fromDate = null)
	{
		if (is_null($fromDate))
			$fromDate = getenv('CYCLE_START_DATE');

		// Find days that have overridden cycle days or are ignored from the calendar
		$ignored = SchedulesExceptions::find("(ignored_from_cycle = 1 OR ignore_day = 1) AND (date >= " . $fromDate . ")");
		$ignoredArray = [];
		foreach ($ignored as $date) {
			$ignoredArray[] = (int)$date->date;
		}

		return $ignoredArray;
	}

	/**
	 * @param $date
	 * @return int|false
	 */
	public static function getCycleDay($date)
	{
		// If the requested day is a weekend, then it does not belong to the cycle
		if (CommonLibrary::isWeekend($date))
			return false;

		$exception = SchedulesExceptions::findFirst($date);
		if ($exception !== false) {
			if ($exception->ignore_day) // If the exception is to ignore the day (i.e. no school) then return false
				return false;
			if (!is_null($exception->cycle_day_override)) // If the exception is a specific cycle day, return that
				return $exception->cycle_day_override;
		}

		$ignored = self::getNonCycleDays();
		$result = CommonLibrary::getWorkingDays(getenv('CYCLE_START_DATE'), $date, $ignored) % 8;
		return ($result <= 0) ? 8 : $result;
	}

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->hasMany('id', 'Oratorysignout\Models\SchedulesExceptions', 'schedule_id', ['alias' => 'SchedulesExceptions']);
		$this->hasMany('id', 'Oratorysignout\Models\SchedulesPeriods', 'schedule_id', ['alias' => 'Periods']);
	}

	/**
	 * @return array
	 */
	public function metaData()
	{
		return [
			MetaData::MODELS_ATTRIBUTES => [
				"id",
				"name",
				"default",
			],

			MetaData::MODELS_PRIMARY_KEY => [
				"id",
			],

			MetaData::MODELS_NON_PRIMARY_KEY => [
				"name",
				"default",
			],

			// Every column that doesn't allows null values
			MetaData::MODELS_NOT_NULL => [
				"name",
				"default",
			],

			// Every column and their data types
			MetaData::MODELS_DATA_TYPES => [
				"id" => Column::TYPE_BIGINTEGER,
				"name" => Column::TYPE_VARCHAR,
				"default" => Column::TYPE_BOOLEAN,
			],

			// The columns that have numeric data types
			MetaData::MODELS_DATA_TYPES_NUMERIC => [
				"id" => true,
				"default" => true,
			],

			// The identity column, use boolean false if the model doesn't have
			// an identity column
			MetaData::MODELS_IDENTITY_COLUMN => "id",

			// How every column must be bound/casted
			MetaData::MODELS_DATA_TYPES_BIND => [
				"id" => Column::BIND_PARAM_INT,
				"name" => Column::BIND_PARAM_STR,
				"default" => Column::BIND_PARAM_BOOL,
			],

			// Fields that must be ignored from INSERT SQL statements
			MetaData::MODELS_AUTOMATIC_DEFAULT_INSERT => [
				'id'
			],

			// Fields that must be ignored from UPDATE SQL statements
			MetaData::MODELS_AUTOMATIC_DEFAULT_UPDATE => [],

			// Default values for columns
			MetaData::MODELS_DEFAULT_VALUES => [
				"name" => '',
				"default" => false,
			],

			// Fields that allow empty strings
			MetaData::MODELS_EMPTY_STRING_VALUES => [
				'name'
			],
		];
	}

	/**
	 * @param mixed $parameters
	 * @return SchedulesPeriods[]
	 */
	public function getPeriods($parameters = null)
	{
		return $this->getRelated('Periods', $parameters);
	}

	/**
	 * @return Schedules
	 */
	public static function getDefault()
	{
		return self::findFirst('default = 1');
	}

	/**
	 * Returns table name mapped in the model.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return 'schedules__';
	}

	/**
	 * Allows to query a set of records that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return Schedules[]
	 */
	public static function find($parameters = null)
	{
		return parent::find($parameters);
	}

	/**
	 * Allows to query the first record that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return Schedules
	 */
	public static function findFirst($parameters = null)
	{
		return parent::findFirst($parameters);
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'id' => (int)$this->id,
			'name' => $this->name,
		];
	}

}
