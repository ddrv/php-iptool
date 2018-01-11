<?php

namespace Ddrv\Iptool\Wizard\Types;

use Ddrv\Iptool\Wizard\TypeAbstract;

/**
 * Class NumericType
 *
 * @const int FORMAT_IP
 * @const int FORMAT_LONG
 * @const int FORMAT_INETNUM
 * @property int $format
 */
class AddressType extends TypeAbstract
{
    /**
     * @const int
     */
    const FORMAT_IP = 0;

    /**
     * @const int
     */
    const FORMAT_LONG = 1;

    /**
     * @const int
     */
    const FORMAT_INETNUM = 2;

    /**
     * @var int
     */
    protected $format;

    /**
     * NumericType constructor.
     *
     * @param int $format
     */
    public function __construct($format=self::FORMAT_IP)
    {
        $this->setFormat($format);
    }

    /**
     * Get valid value.
     *
     * @param mixed $value
     * @return array
     */
    public function getValidValue($value)
    {
        $result = array();
        switch ($this->format) {
            case self::FORMAT_IP:
                $result[] = ip2long($value);
                break;
            case self::FORMAT_INETNUM:
                $ips = self::parseInetnum($value);
                $result = array(
                    ip2long($ips[0]),
                    ip2long($ips[1]),
                );
                break;
            default:
                $result[] = $value;
                break;
        }
        return $result;
    }

    /**
     * Set format.
     *
     * @param int $format
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setFormat($format)
    {
        if (!is_int($format) || !in_array($format,array(self::FORMAT_IP,self::FORMAT_LONG,self::FORMAT_INETNUM))) {
            throw new \InvalidArgumentException('incorrect format');
        }
        $this->format = $format;
        return $this;
    }

    /**
     * Get format.
     *
     * @return int
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Get first and last IP addresses by prefix or inetnum
     *
     * @param string $prefixOrInetnum
     * @return array
     */
    public static function parseInetnum($prefixOrInetnum)
    {
        $result = [null,null];
        if (strpos($prefixOrInetnum,'-') !== false) {
            $d = explode('-',$prefixOrInetnum);
            $result = array(
                trim($d[0]),
                trim($d[1]),
            );
        }
        if (strpos($prefixOrInetnum,'/') !== false) {
            $d = explode('/',$prefixOrInetnum);
            $firstIp = ip2long((string) $d[0]);
            $prefix = filter_var($d[1], \FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 32]
            ]);
            if (false === $firstIp or false === $prefix) {
                return $result;
            }
            $netsize = 1 << (32 - $prefix);
            $lastIp = $firstIp + $netsize - 1;
            if ($lastIp >= (1 << 32)) {
                return $result;
            }
            $result = array(
                long2ip($firstIp),
                long2ip($lastIp),
            );
        }
        return $result;
    }
}