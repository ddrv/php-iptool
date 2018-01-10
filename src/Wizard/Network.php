<?php

namespace Ddrv\Iptool\Wizard;

/**
 * Class Network
 *
 * @property array $registers
 */
class Network extends CsvAbstract
{
    /**
     * @var array
     */
    protected $registers;

    /**
     * Network constructor.
     *
     * @param string $file CSV source file
     * @param string $ipType
     * @param integer $firstIpColumn
     * @param integer $lastIpColumn
     * @throws \InvalidArgumentException
     */
    public function __construct($file, $ipType, $firstIpColumn, $lastIpColumn)
    {
        try {
            $this->checkFile($file);
        } catch (\InvalidArgumentException $exception) {
            throw $exception;
        }
        if (!is_int($firstIpColumn) || $firstIpColumn < 1) {
            throw new \InvalidArgumentException('firstIpColumn must be positive integer');
        }
        if (!is_int($lastIpColumn) || $lastIpColumn < 1) {
            throw new \InvalidArgumentException('lastIpColumn must be positive integer');
        }
    }

    /**
     * Add register
     *
     * @param string $name
     * @param int $column
     * @param Register $register
     * @return $this
     */
    public function addRegister($name, $column, $register)
    {

        if (!$this->checkName($name)) {
            throw new \InvalidArgumentException('incorrect name');
        }
        if (!is_int($column) || $column < 1) {
            throw new \InvalidArgumentException('column must be positive integer');
        }
        if (!($register instanceof Register)) {
            throw new \InvalidArgumentException('incorrect register');
        }
        if (empty($register->getFields())) {
            throw new \InvalidArgumentException('fields of register can not be empty');
        }
        $this->registers[$name] = array(
            'column' => $column,
            'register' => $register,
        );
        return $this;
    }

    /**
     * Remove register.
     *
     * @param string $name
     * @return $this
     */
    public function removeRegister($name)
    {
        if (isset($this->registers[$name])) {
            unset($this->registers[$name]);
        }
        return $this;
    }

    /**
     * Get registers.
     *
     * @return array
     */
    public function getRegisters()
    {
        return $this->registers;
    }
}