<?php

namespace Ddrv\Iptool;

use Ddrv\Iptool\Wizard\Fields\NumericField;
use Ddrv\Iptool\Wizard\Fields\StringField;
use Ddrv\Iptool\Wizard\Network;
use Ddrv\Iptool\Wizard\Register;
use Ddrv\Iptool\Wizard\FieldAbstract;
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
 * @property array  $networks
 * @property array  $registers
 * @property array  $relations
 * @property PDO    $pdo
 * @property PDOStatement $insertIps
 * @property PDOStatement $insertRegister
 * @property PDOStatement[][] $prepare
 * @property string $prefix
 * @property array $meta
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
    protected $networks = array();

    /**
     * @var array
     */
    protected $registers = array();

    /**
     * @var array
     */
    protected $relations = array();

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
    protected $prefix;

    /**
     * @var PDOStatement[]
     */
    protected $prepare;

    /**
     * @var array $meta
     */
    protected $meta = array(
        'index' => array(),
        'registers' => array(),
        'relations' => array(
            'pack' => '',
            'unpack' => '',
            'len' => 3,
            'items' => 0,
        ),
        'networks' => array(
            'pack' => '',
            'unpack' => '',
            'len' => 4,
            'items' => 0,
            'fields' => array(),
        ),
    );

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
        $this->tmpDir = $tmpDir;
        $this->prefix = $this->tmpDir.DIRECTORY_SEPARATOR.'iptool.wizard.'.uniqid();
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
     * @param array $map
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addNetwork($network, $map)
    {
        if (!($network instanceof Network)) {
            throw new \InvalidArgumentException('incorrect network');
        }
        $this->networks[] = array(
            'network' => $network,
            'map' => $map,
        );
        return $this;
    }

    /**
     * Add register.
     *
     * @param string $name
     * @param Register $register
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addRegister($name, $register)
    {
        if (!Register::checkName($name)) {
            throw new \InvalidArgumentException('incorrect name');
        }
        if (!($register instanceof Register)) {
            throw new \InvalidArgumentException('incorrect register');
        }
        if (empty($register->getFields())) {
            throw new \InvalidArgumentException('fields of register can not be empty');
        }
        $this->registers[$name] = $register;
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
     * Add relation
     *
     * @param string $parent
     * @param string $column
     * @param string $child
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addRelation($parent, $column, $child)
    {
        if (!isset($this->registers[$parent])) {
            throw new \InvalidArgumentException('parent register not exists');
        }
        if (!isset($this->registers[$child])) {
            throw new \InvalidArgumentException('child register not exists');
        }
        if (!is_string($column)) {
            throw new \InvalidArgumentException('incorrect column');
        }
        $this->relations[$parent][$column] = $child;
        return $this;
    }

    /**
     * Get relations.
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Remove relation.
     *
     * @param string $parent
     * @param string $column
     * @return $this
     */
    public function removeRelation($parent, $column)
    {
        if (isset($this->relations[$parent][$column])) {
            unset($this->registers[$parent][$column]);
        }
        return $this;
    }

    /**
     * Compile database.
     *
     * @param string $filename
     * @throws \PDOException
     * @throws \ErrorException
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

        $tmpDb = $this->prefix.'.db.sqlite';
        try {
            $this->pdo = new PDO('sqlite:' . $tmpDb);
            $this->pdo->exec('PRAGMA foreign_keys = 1;PRAGMA integrity_check = 1;PRAGMA encoding = \'UTF-8\';');
        } catch (PDOException $e) {
            throw  $e;
        }
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->createTmpDb();

        foreach ($this->registers as $table=>$register) {
            $this->addRegisterInTmpDb($register, $table);
        }

        $this->addRelationsInTmpDb();

        foreach ($this->networks as $network) {
            $this->addNetworkInTmpDb($network);
        }

        foreach ($this->registers as $table=>$register) {
            $this->compileRegister($table);
        }

        $this->compileNetwork();

        $this->compileHeader();

        $this->makeFile($filename);

        $this->pdo = null;

        unlink($tmpDb);

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
        $sql = 'INSERT INTO `_ips` (`ip`,`action`,`parameter`,`value`) VALUES (:ip,:action,:parameter,:value);';
        $this->insertIps = $this->pdo->prepare($sql);
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
     * @param array $data
     * @throws \ErrorException
     */
    protected function addNetworkInTmpDb($data)
    {
        /**
         * @var Network $network
         */
        $network = $data['network'];
        $source = $network->getCsv();

        $firstRow = $network->getFirstRow()-1;
        $csv = fopen($source['file'], 'r');
        for($ignore=0; $ignore < $firstRow; $ignore++) {
            $row = fgetcsv($csv, 4096, $source['delimiter'], $source['enclosure'], $source['escape']);
            unset($row);
        }
        $this->pdo->beginTransaction();
        while ($row = fgetcsv($csv, 4096, $source['delimiter'], $source['enclosure'], $source['escape'])) {
            $firstIpColumn = $network->getFistIpColumn()-1;
            $lastIpColumn = $network->getLastIpColumn()-1;
            if (!isset($row[$firstIpColumn])) {
                throw new \ErrorException('have not column with first ip address');
            }
            if (!isset($row[$lastIpColumn])) {
                throw new \ErrorException('have not column with last ip address');
            }
            $firstIp = $network->getLongIp($row[$firstIpColumn], false);
            $lastIp = $network->getLongIp($row[$lastIpColumn], true);
            $insert = $this->pdo->prepare('INSERT INTO `_ips` (`ip`,`action`,`parameter`,`value`) VALUES (:ip,:action,:parameter,:value);');
            foreach ($data['map'] as $column=>$register) {
                $column--;
                $value = isset($row[$column]) ? $row[$column] : null;
                $insert->execute(array(
                    'ip' => $firstIp,
                    'action' => 'add',
                    'parameter' => $register,
                    'value' => $value,
                ));
                $insert->execute(array(
                    'ip' => $lastIp + 1,
                    'action' => 'remove',
                    'parameter' => $register,
                    'value' => $value,
                ));
                if (isset($row[$column])) {
                    $this->meta['networks']['fields'][$register] = null;
                    $this->setUsed($register, $value);
                }
            }
        }
        $this->pdo->commit();
    }

    protected function setUsed($register, $id)
    {
        if (!isset($this->prepare['update'][$register])) {
            $sql = 'UPDATE `'.$register.'` SET `_used`=\'1\' WHERE `_pk` = :pk;';
            $this->prepare['update'][$register] = $this->pdo->prepare($sql);
        }
        $this->prepare['update'][$register]->execute(array('pk'=>$id));
        if (!empty($this->relations[$register])) {
            if (isset($this->meta['networks']['fields'][$register])) unset ($this->meta['networks']['fields'][$register]);
            $sql = 'SELECT 0,`_pk`,`'.implode('`,`',array_keys($this->meta['registers'][$register]['fields'])).'` 
                FROM `'.$register.'` WHERE `_pk` = \''.addslashes($id).'\'';
            $res = $this->pdo->query($sql);
            $row = $res->fetch(PDO::FETCH_NUM);
            foreach ($this->relations[$register] as $column=>$child) {
                if (isset($row[$column])) {
                    $this->setUsed($child, $row[$column]);
                }
            }
        }
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
                 * @var FieldAbstract $type
                 */
                $type = $data['type'];
                $value = isset($row[$column])?$row[$column]:null;
                $value = $type->getValidValue($value);
                $values[$field] = $value;
                $type->updatePackFormat($value);
            };
            $insertStatement->execute($values);
        }
        $format = array();
        $empty = array();
        foreach ($columns as $field=>$data) {
            /**
             * @var FieldAbstract $type
             */
            $type = $data['type'];
            $fieldPackFormat = $type->getPackFormat();
            $format['pack'][] = $fieldPackFormat;
            $format['unpack'][] = $fieldPackFormat.$field;
            $empty[$field] = null;
        };
        $pack = implode('', $format['pack']);
        $bin = self::packArray($pack,$empty);
        $this->meta['registers'][$table]['pack'] = $pack;
        $this->meta['registers'][$table]['unpack'] = implode('/',$format['unpack']);
        $this->meta['registers'][$table]['len'] = strlen($bin);
        $this->meta['registers'][$table]['items'] = 0;
        $this->meta['registers'][$table]['fields'] = $empty;
        $this->pdo->commit();
    }

    /**
     * Create temporary relations
     */
    protected function addRelationsInTmpDb()
    {
        $parentType = new StringField();
        $fieldType = new StringField();
        $childType = new StringField();
        foreach ($this->relations as $parent => $networkRelation) {
            foreach ($networkRelation as $field => $child) {
                $parentType->updatePackFormat($parent);
                $childType->updatePackFormat($child);
                $fieldType->updatePackFormat($field);

                $this->meta['relations']['items'] ++;
                $this->meta['relations']['data'][] = array(
                    $parent,
                    $field,
                    $child
                );
            }
        }
        $this->meta['relations']['pack'] = $parentType->getPackFormat()
            .$fieldType->getPackFormat()
            .$childType->getPackFormat();
        $this->meta['relations']['unpack'] = $parentType->getPackFormat().'p/'
            .$fieldType->getPackFormat().'f/'
            .$childType->getPackFormat().'c';
        $this->meta['relations']['len'] = strlen(pack($this->meta['relations']['pack'], null, null, null));
    }

    /**
     * Compile register from temporary db
     *
     * @param $register
     */
    protected function compileRegister($register)
    {
        $file = fopen($this->prefix.'.reg.'.$register.'.dat', 'w');
        $pack = $this->meta['registers'][$register]['pack'];
        $empty =  $this->meta['registers'][$register]['fields'];
        $bin = self::packArray($pack, $empty);
        fwrite($file,$bin);
        $offset = 0;
        //$data = $this->pdo->query('SELECT * FROM `'.$register.'` WHERE `_used` = \'1\'');
        $data = $this->pdo->query('SELECT * FROM `'.$register.'`');
        $this->pdo->beginTransaction();
        while($row = $data->fetch()) {
            $rowId = $row['_pk'];
            unset($row['_pk']);
            unset($row['_used']);
            $check = 0;
            foreach ($row as $cell=>$cellValue) {
                if (!empty($cellValue)) $check = 1;
            }
            $bin = self::packArray($pack, $row);
            if ($check) {
                $offset ++;
                fwrite($file,$bin);
            }
            $this->pdo->exec('UPDATE `_ips` SET `offset` =\''.($check?$offset:0).'\' WHERE `parameter` = \''.$register.'\' AND `value`=\''.$rowId.'\';');
        }
        $this->meta['registers'][$register]['items'] = $offset;
        $this->pdo->commit();
        fclose($file);
    }

    /**
     * Compile network from temporary db
     */
    protected function compileNetwork()
    {
        $ip = -1;
        $this->meta['networks']['pack'] = '';
        $fields = $this->meta['networks']['fields'];
        $values = array();
        foreach ($fields as $register=>$null) {
            $values[$register] = array();
            $type = new NumericField(0);
            $type->updatePackFormat($this->meta['registers'][$register]['items']);
            $pack = $type->getPackFormat();
            $this->meta['networks']['pack'] .= $pack;
            $this->meta['networks']['unpack'] .= $pack.$register.'/';
        }
        $pack = $this->meta['networks']['pack'];
        $this->meta['networks']['unpack'] = mb_substr($this->meta['networks']['unpack'],0,-1);
        $binaryPrevData = self::packArray($pack, $fields);
        $this->meta['networks']['len'] += strlen($binaryPrevData);
        $offset = 0;
        $this->meta['index'][0] = 0;
        $file = fopen($this->prefix.'.networks.dat','w');
        $ipInfo = $this->pdo->query('SELECT * FROM `_ips` ORDER BY `ip` ASC, `action` DESC, `id` ASC;');
        while ($row = $ipInfo->fetch()) {
            if ($row['ip'] !== $ip) {
                foreach ($values as $param=>$v) {
                    if (!empty($param)) $fields[$param] = array_pop($v);
                }
                $binaryData = self::packArray($pack, $fields);
                if ($binaryData !== $binaryPrevData || empty($ip)) {
                    fwrite($file, pack('N', $ip) . $binaryData);
                    $octet = (int)long2ip($ip);
                    if (!isset($this->meta['index'][$octet])) $this->meta['index'][$octet] = $offset;
                    $offset++;
                    $binaryPrevData = $binaryData;
                }
                $ip = $row['ip'];
            }
            if ($row['action'] == 'remove') {
                $key = array_search($row['offset'],$values[$row['parameter']]);
                if ($key !== false) {
                    unset($values[$row['parameter']][$key]);
                }
            } else {
                $values[$row['parameter']][] = $row['offset'];
            }
        }
        if ($ip < ip2long('255.255.255.255')) {
            foreach ($values as $param => $v) {
                if (!empty($param)) $fields[$param] = array_pop($v);
            }
            $binaryData = self::packArray($pack, $fields);
            if ($binaryData !== $binaryPrevData) {
                $octet = (int)long2ip($ip);
                if (!isset($this->meta['index'][$octet])) $this->meta['index'][$octet] = $offset;
                $offset++;
                fwrite($file, pack('N', $ip) . $binaryData);
            }
        }
        $this->meta['networks']['items'] = $offset;
        for($i=1;$i<=255;$i++) {
            if (!isset($this->meta['index'][$i])) $this->meta['index'][$i] = $this->meta['index'][$i-1];
        }
        ksort($this->meta['index']);
        fclose($file);
        unset($ip);
    }

    /**
     * Compile header.
     */
    protected function compileHeader()
    {
        /*
         * Format version.
         */
        $header = pack('C', self::FORMAT_VERSION);

        /*
         * Count of registers.
         */
        $header .= pack('C', count($this->meta['registers']));

        $nameLen = 1;
        $packLen = strlen($this->meta['networks']['pack']);
        $lenType = new NumericField();
        $lenType->updatePackFormat($this->meta['networks']['len']);
        $itmType = new NumericField();
        $itmType->updatePackFormat($this->meta['networks']['items']);
        foreach ($this->meta['registers'] as $registerName => $register) {
            if (strlen($registerName) > $nameLen) $nameLen = strlen($registerName);
            if (strlen($register['unpack']) > $packLen) $packLen = strlen($register['unpack']);
            $lenType->updatePackFormat($register['len']);
            $itmType->updatePackFormat($register['items']);
        }
        $len = $lenType->getPackFormat();
        $itm = $itmType->getPackFormat();

        $pack = 'A'.$nameLen.'A'.$packLen.$len.$itm;
        $unpack = 'A'.$nameLen.'name/A'.$packLen.'pack/'.$len.'len/'.$itm.'items';

        /*
         * Length of registers define unpack format.
         */
        $header .= pack('I',strlen($unpack));

        /*
         * Length of relations pack format.
         */
        $lenRelationsFormat = strlen($this->meta['relations']['unpack']);
        $header .= pack('C', $lenRelationsFormat);

        /*
         * Relation length.
         */
        $header .= pack('S', $this->meta['relations']['len']);

        /*
         * Relations count.
         */
        $header .= pack('C', $this->meta['relations']['items']);

        /*
         * Relations pack format (parent, column, child).
         */
        $header .= $this->meta['relations']['unpack'];

        /*
         * Relations.
         */
        foreach ($this->meta['relations']['data'] as $relation) {
            $header .= self::packArray(
                $this->meta['relations']['pack'],
                $relation
            );
        }

        /*
         * Registers define unpack format.
         */
        $header .= pack('A*',$unpack);

        /*
         * Length of registers define length.
         */
        $header .= pack('I',strlen(pack($pack,'','',0,0)));

        /**
         * Registers define.
         */
        foreach ($this->meta['registers'] as $registerName => $register) {
            $header .= pack(
                $pack,
                $registerName,
                $register['unpack'],
                $register['len'],
                $register['items']
            );
        }

        /*
         * Networks define.
         */
        $header .= pack(
            $pack,
            'n',
            $this->meta['networks']['unpack'],
            $this->meta['networks']['len'],
            $this->meta['networks']['items']
        );

        /*
         * Index.
         */
        $header .= $this->packArray('I*',$this->meta['index']);

        /*
         * Control word and header length.
         */
        $headerLength = strlen($header);
        $header = 'DIT'.pack('I', $headerLength).$header;

        $file = fopen($this->prefix.'.header', 'w');
        fwrite($file, $header);
        fclose($file);
    }

    /**
     * Make file.
     *
     * @param string $fileName
     */
    protected function makeFile($fileName)
    {
        /*
         * Create binary database.
         */
        $tmp = $this->prefix.'.database.dat';
        $database = fopen($tmp,'w');

        /*
         * Write header to database.
         */
        $file = $this->prefix.'.header';
        $stream = fopen($file, 'rb');
        stream_copy_to_stream($stream, $database);
        fclose($stream);
        if (is_writable($file)) unlink($file);

        /*
         * Write networks to database.
         */
        $file = $this->prefix.'.networks.dat';
        $stream = fopen($file, 'rb');
        stream_copy_to_stream($stream, $database);
        fclose($stream);
        if (is_writable($file)) unlink($file);

        foreach ($this->meta['registers'] as $register=>$data) {
            $file = $this->prefix.'.reg.'.$register.'.dat';
            $stream = fopen($file, 'rb');
            stream_copy_to_stream($stream, $database);
            fclose($stream);
            if (is_writable($file)) unlink($file);
        }

        $time = empty($this->time)?time():$this->time;
        fwrite($database,pack('N1A128',$time,$this->author));
        fwrite($database,pack('A*',$this->license));
        fclose($database);

        rename($tmp, $fileName);
    }

    /**
     * Function-helper for pack arrays
     *
     * @param string $format
     * @param array $array
     * @return string
     */
    public static function packArray($format,$array)
    {
        $packParams = array_values($array);
        array_unshift($packParams,$format);
        return call_user_func_array('pack',$packParams);
    }
}
