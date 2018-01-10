<?php

namespace Ddrv\Iptool\Wizard\Types;

use Ddrv\Iptool\Wizard\TypeAbstract;

/**
 * Class Decimal
 *
 * @property int $precision
 * @property int|float|double|null $min
 * @property int|float|double|null $max
 * @property int $mode
 */
class Decimal extends TypeAbstract
{
    /**
     * @var int
     */
    protected $precision;

    /**
     * @var int|float|double|null
     */
    protected $min;

    /**
     * @var int|float|double|null
     */
    protected $max;

    /**
     * @var int
     */
    protected $mode = \PHP_ROUND_HALF_DOWN;

    /**
     * Decimal constructor.
     *
     * @param int $precision
     * @param int $mode
     * @param int|float|double|null $min
     * @param int|float|double|null $max
     */
    public function __construct($precision=0, $mode=\PHP_ROUND_HALF_DOWN, $min=null, $max=null)
    {
        $this->setPrecision($precision);
        $this->setMode($mode);
        $this->setMin($min);
        $this->setMax($max);
    }

    /**
     * Get valid value.
     *
     * @param mixed $value
     * @return int|float|double
     */
    public function getValidValue($value)
    {
        if ($this->min !== null && $value < $this->min) $value = $this->min;
        if ($this->max !== null && $value > $this->max) $value = $this->max;
        $value = round((float)$value, $this->precision, $this->mode);
        if ($this->precision == 0) $value = (int)$value;
        return $value;
    }

    /**
     * Set precision.
     *
     * @param int $precision
     * @return $this
     */
    public function setPrecision($precision = 0)
    {
        if (!is_integer($precision) || $precision < 0) {
            throw new \InvalidArgumentException('precision must be positive integer or 0');
        }
        $this->precision = $precision;
        return $this;
    }

    /**
     * Get precision.
     *
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * Set mode.
     *
     * @param int $mode
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMode($mode=\PHP_ROUND_HALF_DOWN)
    {
        if (!in_array($mode, array(\PHP_ROUND_HALF_UP, \PHP_ROUND_HALF_DOWN, \PHP_ROUND_HALF_EVEN, \PHP_ROUND_HALF_ODD))) {
            throw new \InvalidArgumentException('mode incorrect');
        }
        $this->mode = $mode;
        return $this;
    }

    /**
     * Get mode.
     *
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set max.
     *
     * @param int|float|double|null $max
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMax($max = null)
    {
        if (!is_int($max) && !is_float($max) && !is_double($max) && !is_null($max)) {
            throw new \InvalidArgumentException('max incorrect');
        }
        if (!is_null($this->min) && $this->min > $max) {
            throw new \InvalidArgumentException('max can not be less than min');
        }
        $this->max = $max;
        return $this;
    }

    /**
     * Get max.
     *
     * @return float|int|null
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Set min.
     *
     * @param int|float|double|null $min
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMin($min = null)
    {
        if (!is_int($min) && !is_float($min) && !is_double($min) && !is_null($min)) {
            throw new \InvalidArgumentException('min incorrect');
        }
        if (!is_null($this->max) && $this->max < $min) {
            throw new \InvalidArgumentException('min can not be greater than max');
        }
        $this->min = $min;
        return $this;
    }

    /**
     * Get min.
     *
     * @return float|int|null
     */
    public function getMin()
    {
        return $this->min;
    }
}