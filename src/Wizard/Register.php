<?php

namespace Ddrv\Iptool\Wizard;

/**
 * Class Register
 *
 * @property int $id
 * @property array $fields
 */
class Register extends CsvAbstract
{
    /**
     * @var int
     */
    protected $id=0;

    /**
     * @var array
     */
    protected $fields;

    /**
     * Register constructor.
     *
     * @param string $file
     * @throws \InvalidArgumentException
     */
    public function __construct($file)
    {
        try {
            $this->checkFile($file);
        } catch (\InvalidArgumentException $exception) {
            throw $exception;
        }
    }

    /**
     * Set ID column.
     *
     * @param int $column
     * @return $this
     */
    public function setId($column)
    {
        $this->id = $column;
        return $this;
    }

    /**
     * Get ID column.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add field.
     *
     * @param string $name
     * @param int $column
     * @param string $type
     * @return $this
     */
    public function addField($name, $column, $type)
    {
        $this->fields[$name] = array(
            'column' => $column,
            'type' => $type,
        );
        return $this;
    }

    /**
     * Remove field.
     *
     * @param string $name
     * @return $this
     */
    public function removeField($name)
    {
        if (isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }
        return $this;
    }

    /**
     * Get fields.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}