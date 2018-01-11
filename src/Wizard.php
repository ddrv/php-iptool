<?php

namespace Ddrv\Iptool;

use Ddrv\Iptool\Wizard\Network;
use Ddrv\Iptool\Wizard\Register;
use Ddrv\Iptool\Wizard\TypeAbstract;
use PDO;
use PDOStatement;
use PDOException;

/**
 * Class Wizard
 *
 * @const int FORMAT_VERSION
 * @property string $tmpDir
 * @property string $author
 * @property string $license
 * @property int    $time
 * @property Network[]  $networks
 * @property PDO $pdo
 * @property PDOStatement $insertIps
 * @property PDOStatement $insertRegister
 * @property string $unique
 */
class Wizard
{
    /**
     * @const int
     */
    const FORMAT_VERSION = 2;

    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $license;

    /**
     * @var integer
     */
    protected $time;

    /**
     * @var array
     */
    protected $networks;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var PDOStatement
     */
    protected $insertIps;

    /**
     * @var string
     */
    protected $unique;

    /**
     * Wizard constructor.
     *
     * @param string $tmpDir
     * @throws \InvalidArgumentException
     */
    public function __construct($tmpDir)
    {
        if (!is_string($tmpDir)) {
            throw new \InvalidArgumentException('incorrect tmpDir');
        }
        if (!is_dir($tmpDir)) {
            throw new \InvalidArgumentException('tmpDir is not a directory');
        }
        if (!is_writable($tmpDir)) {
            throw new \InvalidArgumentException('tmpDir is not a writable');
        }
        $this->unique = uniqid();
        $this->tmpDir = $tmpDir;
    }

    /**
     * Set author.
     *
     * @param string $author
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAuthor($author)
    {
        if (!is_string($author)) {
            throw new \InvalidArgumentException('incorrect author');
        }
        if (mb_strlen($author) > 64) $author = mb_substr($author,0,64);
        $this->author = $author;
        return $this;
    }

    /**
     * Set license.
     *
     * @param string $license
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setLicense($license)
    {
        if (!is_string($license)) {
            throw new \InvalidArgumentException('incorrect license');
        }
        $this->license = $license;
        return $this;
    }

    /**
     * Set time.
     *
     * @param integer $time
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTime($time)
    {
        if (!is_int($time) || $time < 0) {
            throw new \InvalidArgumentException('incorrect time');
        }
        $this->time = $time;
        return $this;
    }

    /**
     * Add network.
     *
     * @param Network $network
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addNetwork($network)
    {
        if (!($network instanceof Network)) {
            throw new \InvalidArgumentException('incorrect network');
        }
        $this->networks[] = $network;
        return $this;
    }

    /**
     * Compile database.
     *
     * @param string $filename
     * @throws PDOException
     */
    public function compile($filename)
    {
        if (!is_string($filename)) {
            throw new \InvalidArgumentException('incorrect filename');
        }
        if (file_exists($filename) && !is_writable($filename)) {
            throw new \InvalidArgumentException('file not writable');
        }
        if (!file_exists($filename) && !is_writable(dirname($filename))) {
            throw new \InvalidArgumentException('directory not writable');
        }
        if (empty($this->time)) $this->time = time();

        $tmpDb = $this->tmpDir.DIRECTORY_SEPARATOR.'iptool.wizard.'.$this->unique.'.sqlite';
        try {
            $this->pdo = new PDO('sqlite:' . $tmpDb);
            $this->pdo->exec('PRAGMA foreign_keys = 1;PRAGMA encoding = \'UTF-8\';');
        } catch (PDOException $e) {
            throw  $e;
        }
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->createTmpDb();

        foreach ($this->networks as $network) {
            $this->addNetworkInTmpDb($network);
        }
        $this->pdo = null;

    }

    /**
     * Create tmp sqlite database.
     */
    protected function createTmpDb()
    {
        $sql = '
            CREATE TABLE `_ips` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT, 
                `ip` INTEGER,
                `action` TEXT, 
                `parameter` TEXT, 
                `value` TEXT, 
                `offset` TEXT
            );
            CREATE INDEX `ip` ON `_ips` (`ip`);
            CREATE INDEX `parameter` ON `_ips` (`parameter`);
            CREATE INDEX `value` ON `_ips` (`value`);
        ';
        $this->pdo->exec($sql);
        $this->insertIps = $this->pdo->prepare('INSERT '.'INTO `_ips` (`ip`,`action`,`parameter`,`value`) VALUES (:ip,:action,:parameter,:value);');
        $this->insertIps->execute(array(
            'ip' => 0,
            'action' => 'add',
            'parameter' => NULL,
            'value' => NULL,
        ));
    }

    /**
     * Create temporary network.
     *
     * @param Network $network
     * @throws \ErrorException
     */
    protected function addNetworkInTmpDb($network)
    {
        $registers = array();
        foreach ($network->getRegisters() as $name => $register) {
            $registers[$name] = $register['column'];
            $this->addRegisterInTmpDb($register['register'], $name);
        }
        $source = $network->getCsv();

        $firstRow = $network->getFirstRow()-1;
        $csv = fopen($source['file'], 'r');
        for($ignore=0; $ignore < $firstRow; $ignore++) {
            $row = fgetcsv($csv, 4096, $source['delimiter'], $source['enclosure'], $source['escape']);
            unset($row);
        }
        $this->pdo->beginTransaction();
        while ($row = fgetcsv($csv, 4096, $source['delimiter'], $source['enclosure'], $source['escape'])) {
            $firstIpColumn = $network->getFistIpColumn();
            $lastIpColumn = $network->getLastIpColumn();
            if (!isset($row[$firstIpColumn])) {
                throw new \ErrorException('have not column with first ip address');
            }
            if (!isset($row[$lastIpColumn])) {
                throw new \ErrorException('have not column with last ip address');
            }
            // todo: fix get last ip for inetnum type
            $firstIp = $network->getIpType()->getValidValue($row[$firstIpColumn])[0];
            $lastIp = $network->getIpType()->getValidValue($row[$lastIpColumn])[0];
            foreach ($registers as $register => $column) {
                $value = isset($row[$column]) ? $row[$column] : null;
                $this->insertIps->execute(array(
                    'ip' => $firstIp,
                    'action' => 'add',
                    'parameter' => $register,
                    'value' => $value,
                ));
                $this->insertIps->execute(array(
                    'ip' => $lastIp + 1,
                    'action' => 'remove',
                    'parameter' => $register,
                    'value' => $value,
                ));
                if ($value) {
                    $this->pdo->exec('UPDATE `'.$register.'` SET `_used`=\'1\' WHERE `_pk` = \''.addslashes($value).'\';');
                }
            }

        }
        $this->pdo->commit();
    }

    /**
     * Create temporary register.
     *
     * @param Register $register
     * @param string $table
     */
    protected function addRegisterInTmpDb($register, $table)
    {
        $source = $register->getCsv();

        $columns = $register->getFields();
        $fields = array('`_pk`', '`_used`');
        $params = array(':_pk',':_used');
        foreach ($columns as $field=>$data) {
            $create[] = '`' . $field . '` TEXT';
            $fields[] = '`' . $field . '`';
            $params[] = ':' . $field;
        }
        $sql = 'CREATE  TABLE `' . $table . '` (' . implode(',', $fields) . ', CONSTRAINT `_pk` PRIMARY KEY (`_pk`) ON CONFLICT IGNORE);';
        $sql .= 'CREATE INDEX `_used` ON `'.$table.'` (`_used`);';
        $this->pdo->exec($sql);
        $sql = 'INSERT INTO `'.$table.'` (' . implode(',', $fields) . ') VALUES (' . implode(',', $params) . ');';
        $insertStatement = $this->pdo->prepare($sql);

        $firstRow = $register->getFirstRow()-1;
        $csv = fopen($source['file'], 'r');
        for($ignore=0; $ignore < $firstRow; $ignore++) {
            $row = fgetcsv($csv, 4096, $source['delimiter'], $source['enclosure'], $source['escape']);
            unset($row);
        }

        $rowIterator = 0;
        $idColumn = $register->getId()-1;
        $this->pdo->beginTransaction();
        while ($row = fgetcsv($csv, 4096, $source['delimiter'], $source['enclosure'], $source['escape'])) {
            $rowIterator++;
            $rowId = $rowIterator;
            if ($idColumn >= 0 && isset($row[$idColumn])) {
                $rowId = $row[$idColumn];
            }
            $values = array(
                '_pk'=>$rowId,
                '_used'=>0
            );
            foreach ($columns as $field=>$data) {
                $column = $data['column']-1;
                /**
                 * @var TypeAbstract $type
                 */
                $type = $data['type'];
                $value = isset($row[$column])?$row[$column]:null;
                $value = $type->getValidValue($value);
                $values[$field] = $value;
                $type->updatePackFormat($value);
            };
            $insertStatement->execute($values);
        }
        $this->pdo->commit();
    }
}