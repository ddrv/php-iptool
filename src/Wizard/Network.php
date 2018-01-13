<?php

namespace Ddrv\Iptool\Wizard;

/**
 * Class Network.
 *
 * @const int IP_TYPE_ADDRESS
 * @const int IP_TYPE_LONG
 * @const int IP_TYPE_INETNUM
 * @property int $firstIpColumn
 * @property int $lastIpColumn
 * @property int $ipType
 */
class Network extends CsvAbstract
{
    /**
     * @const int
     */
    const IP_TYPE_ADDRESS = 0;

    /**
     * @const int
     */
    const IP_TYPE_LONG = 1;

    /**
     * @const int
     */
    const IP_TYPE_INETNUM = 2;

    /**
     * @var int
     */
    protected $firstIpColumn;

    /**
     * @var int
     */
    protected $lastIpColumn;

    /**
     * @var int
     */
    protected $ipType;

    /**
     * Network constructor.
     *
     * @param string $file CSV source file
     * @param int $ipType
     * @param int $firstIpColumn
     * @param int $lastIpColumn
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
        if (!in_array($ipType, array(self::IP_TYPE_ADDRESS, self::IP_TYPE_LONG, self::IP_TYPE_INETNUM))) {
            throw new \InvalidArgumentException('incorrect ipType');
        }
        $this->firstIpColumn = $firstIpColumn;
        $this->lastIpColumn = $lastIpColumn;
        $this->ipType = $ipType;
    }

    /**
     * Get first ip column.
     *
     * @return int
     */
    public function getFistIpColumn() {
        return $this->firstIpColumn;
    }

    /**
     * Get last ip column.
     *
     * @return int
     */
    public function getLastIpColumn() {
        return $this->lastIpColumn;
    }

    /**
     * Get ipType.
     *
     * @return int
     */
    public function getIpType()
    {
        return $this->ipType;
    }

    /**
     * Get long IP address.
     * @param mixed $value
     * @param bool $last
     * @return int
     */
    public function getLongIp($value, $last=false)
    {
        switch ($this->ipType) {
            case self::IP_TYPE_ADDRESS:
                $result = ip2long($value);
                break;
            case self::IP_TYPE_INETNUM:
                $ips = self::parseInetnum($value);
                $result = $ips[(int)$last];
                break;
            default:
                $result = (int)$value;
                if ($result < 0) $result = 0;
                if ($result > (1 << 32)) $result = (1 << 32);
                break;
        }
        return $result;
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
                ip2long(trim($d[0])),
                ip2long(trim($d[1])),
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
            $netSize = 1 << (32 - $prefix);
            $lastIp = $firstIp + $netSize - 1;
            if ($lastIp >= (1 << 32)) {
                return $result;
            }
            $result = array(
                $firstIp,
                $lastIp,
            );
        }
        return $result;
    }
}
