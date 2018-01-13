<?php

namespace Ddrv\Iptool\Wizard\Fields;

use Ddrv\Iptool\Wizard\FieldAbstract;

/**
 * Class StringType
 *
 * @const int TRANSFORM_NONE
 * @const int TRANSFORM_LOWER
 * @const int TRANSFORM_UPPER
 * @property string $transform
 * @property int|null $maxLength
 */
class StringField extends FieldAbstract
{
    /**
     * @const int
     */
    const TRANSFORM_NONE = 0;

    /**
     * @const int
     */
    const TRANSFORM_LOWER = 1;

    /**
     * @const int
     */
    const TRANSFORM_UPPER = 2;

    /**
     * @var int
     */
    protected $transform;

    /**
     * @var int|null
     */
    protected $maxLength;

    /**
     * @var string
     */
    protected $packFormatKey = 'A';

    /**
     * StringType constructor.
     *
     * @param int $transform
     * @param int|null $maxLength
     */
    public function __construct($transform=0, $maxLength=null)
    {
        $this->setTransform($transform);
        $this->setMaxLength($maxLength);
    }

    /**
     * Get valid value.
     *
     * @param mixed $value
     * @return string
     */
    public function getValidValue($value)
    {
        if ($this->maxLength !== null && mb_strlen($value) > $this->maxLength) $value = mb_substr($value,0,$this->maxLength);
        switch ($this->transform) {
            case self::TRANSFORM_LOWER:
                $value = mb_strtolower($value);
                break;
            case self::TRANSFORM_UPPER:
                $value = mb_strtoupper($value);
                break;
        }
        return $value;
    }

    /**
     * Set precision.
     *
     * @param int $transform
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTransform($transform = 0)
    {
        if (!is_integer($transform) || !in_array($transform,array(self::TRANSFORM_NONE, self::TRANSFORM_LOWER, self::TRANSFORM_UPPER))) {
            throw new \InvalidArgumentException('incorrect transform');
        }
        $this->transform = $transform;
        return $this;
    }

    /**
     * Get transform.
     *
     * @return int
     */
    public function getTransform()
    {
        return $this->transform;
    }

    /**
     * Set max lenght.
     *
     * @param int|null $maxLength
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMaxLength($maxLength = null)
    {
        if (is_null($maxLength)) {
            $this->maxLength = null;
            return $this;
        }
        if (!is_int($maxLength) || $maxLength < 1) {
            throw new \InvalidArgumentException('incorrect maxLength');
        }
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * Get max length.
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    public function updatePackFormat($value)
    {
        $length = strlen($value);
        if ($length > $this->packFormatLength) $this->packFormatLength = $length;
    }
}
