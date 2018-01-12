<?php

namespace Ddrv\Iptool\Wizard\Fields;

use Ddrv\Iptool\Wizard\FieldAbstract;

/**
 * Class NumericType
 *
 * @property int $precision
 * @property int|float|double|null $min
 * @property int|float|double|null $max
 * @property int|float|double|null $minValue
 * @property int|float|double|null $maxValue
 * @property int $mode
 * @property string $packFormatKey
 * @property int|null $packFormatLength
 */
class NumericField extends FieldAbstract
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
     * @var int|float|double|null
     */
    protected $minValue=0;

    /**
     * @var int|float|double|null
     */
    protected $maxValue=0;

    /**
     * @var int
     */
    protected $mode = \PHP_ROUND_HALF_DOWN;

    /**
     * @var string
     */
    protected $packFormatKey = 'C';

    /**
     * NumericType constructor.
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
     * @throws \InvalidArgumentException
     */
    public function setPrecision($precision = 0)
    {
        if (!is_int($precision) || $precision < 0) {
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

    /**
     * Get format for pack() function.
     *
     * @return string
     */
    public function getPackFormat()
    {
        if ($this->precision == 0) {
            $this->packFormatKey = 'L';
            if ($this->maxValue < (1 << 31) && $this->minValue >= -(1 << 31)) {
                $this->packFormatKey = 'l';
            }
            if ($this->maxValue < (1 << 15) && $this->minValue >= -(1 << 15)) {
                $this->packFormatKey = 's';
            }
            if ($this->maxValue < (1 << 16) && $this->minValue >= 0) {
                $this->packFormatKey = 'S';
            }
            if ($this->maxValue < (1 << 7) && $this->minValue >= -(1 << 7)) {
                $this->packFormatKey = 'c';
            }
            if ($this->maxValue < (1 << 8) && $this->minValue >= 0) {
                $this->packFormatKey = 'C';
            }
        } else {
            $this->packFormatKey = 'f';
        }
        return parent::getPackFormat();
    }

    /**
     * @param mixed $value
     */
    public function updatePackFormat($value)
    {
        $value = $this->getValidValue($value);
        if ($value < $this->minValue) {
            $this->minValue = $value;
        }
        if ($value > $this->maxValue) {
            $this->maxValue = $value;
        }
    }
}