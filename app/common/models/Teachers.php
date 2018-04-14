<?php

namespace Oratorysignout\Models;

use Phalcon\Db\Column;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Model\Validator\Email as Email;

/**
 * Teachers
 *
 * @package Oratorysignout\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2017-04-27, 16:24:28
 */
class Teachers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $first_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $middle_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $last_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $email;

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $this->validate(
            new Email(
                [
                    'field' => 'email',
                    'required' => true,
                ]
            )
        );

        if ($this->validationHasFailed() == true) {
            return false;
        }

        return true;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
    }

    /**
     * @return array
     */
    public function metaData()
    {
        return [
            MetaData::MODELS_ATTRIBUTES => [
                "id",
                "first_name",
                "last_name",
                "email",
            ],

            MetaData::MODELS_PRIMARY_KEY => [
                "id",
            ],

            MetaData::MODELS_NON_PRIMARY_KEY => [
                "first_name",
                "last_name",
                "email",
            ],

            // Every column that doesn't allows null values
            MetaData::MODELS_NOT_NULL => [
                "id",
                "first_name",
                "last_name",
                "email",
            ],

            // Every column and their data types
            MetaData::MODELS_DATA_TYPES => [
                "id" => Column::TYPE_BIGINTEGER,
                "first_name" => Column::TYPE_VARCHAR,
                "last_name" => Column::TYPE_VARCHAR,
                "email" => Column::TYPE_VARCHAR,
            ],

            // The columns that have numeric data types
            MetaData::MODELS_DATA_TYPES_NUMERIC => [
                "id" => true,
            ],

            // The identity column, use boolean false if the model doesn't have
            // an identity column
            MetaData::MODELS_IDENTITY_COLUMN => "id",

            // How every column must be bound/casted
            MetaData::MODELS_DATA_TYPES_BIND => [
                "id" => Column::BIND_PARAM_INT,
                "first_name" => Column::BIND_PARAM_STR,
                "last_name" => Column::BIND_PARAM_STR,
                "email" => Column::BIND_PARAM_STR,
            ],

            // Fields that must be ignored from INSERT SQL statements
            MetaData::MODELS_AUTOMATIC_DEFAULT_INSERT => [
                'id'
            ],

            // Fields that must be ignored from UPDATE SQL statements
            MetaData::MODELS_AUTOMATIC_DEFAULT_UPDATE => [],

            // Default values for columns
            MetaData::MODELS_DEFAULT_VALUES => [
                "first_name" => '',
                "last_name" => '',
                "email" => '',
            ],

            // Fields that allow empty strings
            MetaData::MODELS_EMPTY_STRING_VALUES => [
                "first_name" => '',
                "last_name" => '',
                "email" => '',
            ],
        ];
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'teachers__';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Teachers[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Teachers
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
        ];
    }

}