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
     * @param string $type
     * @param integer $firstAddressColumn
     * @param integer $lastAddressColumn
     */
    public function __construct($file, $type, $firstAddressColumn, $lastAddressColumn)
    {
        $this->checkFile($file);
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