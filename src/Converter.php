<?php
namespace Ddrv\Iptool;

use \PDO;
use \PDOException;

/**
 * Class Converter
 *
 * @property integer $time
 * @property string  $author
 * @property string  $license
 * @property string  $temporaryDir
 * @property array   $iterator
 * @property array   $encodings
 * @property array   $types
 * @property array   $csv
 * @property array   $registers
 * @property array   $networks
 * @property array   $ipData
 * @property array   $errors
 * @property array   $tmpFiles
 * @property array   $meta
 * @property PDO     $pdo
 *
 */
class Converter
{
    /**
     * Parser version.
     */
    const VERSION = '1';

    /**
     * @var integer
     */
    protected $time = 0;

    /**
     * @var string
     */
    protected $author = '';

    /**
     * @var string
     */
    protected $license = '';

    /**
     * @var string
     */
    protected $temporaryDir;

    /**
     * @var array
     */
    protected $iterator = array(
        'csv' => 0,
        'register' => 0,
        'network' => 0,
    );

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $encodings=array(
        'ASCII',
        'EUC-JP',
        'eucJP-win',
        'UCS-4',
        'UCS-4BE',
        'UCS-4LE',
        'UCS-2',
        'UCS-2BE',
        'UCS-2LE',
        'UTF-32',
        'UTF-32BE',
        'UTF-32LE',
        'UTF-16',
        'UTF-16BE',
        'UTF-16LE',
        'UTF-7',
        'UTF7-IMAP',
        'UTF-8',
        'SJIS',
        'SJIS-win',
        'ISO-2022-JP',
        'ISO-2022-JP-MS',
        'CP932',
        'CP51932',
        'SJIS-mac',
        'MacJapanese',
        'SJIS-Mobile#DOCOMO',
        'SJIS-DOCOMO',
        'SJIS-Mobile#KDDI',
        'SJIS-KDDI',
        'SJIS-Mobile#SOFTBANK',
        'SJIS-SOFTBANK',
        'UTF-8-Mobile#DOCOMO',
        'UTF-8-DOCOMO',
        'UTF-8-Mobile#KDDI-A',
        'UTF-8-Mobile#KDDI-B',
        'UTF-8-KDDI',
        'UTF-8-Mobile#SOFTBANK',
        'UTF-8-SOFTBANK',
        'ISO-2022-JP-MOBILE#KDDI',
        'ISO-2022-JP-KDDI',
        'JIS',
        'JIS-ms',
        'CP50220',
        'CP50220raw',
        'CP50221',
        'CP50222',
        'ISO-8859-1',
        'ISO-8859-2',
        'ISO-8859-3',
        'ISO-8859-4',
        'ISO-8859-5',
        'ISO-8859-6',
        'ISO-8859-7',
        'ISO-8859-8',
        'ISO-8859-9',
        'ISO-8859-10',
        'ISO-8859-13',
        'ISO-8859-14',
        'ISO-8859-15',
        'ISO-8859-16',
        'byte2be',
        'byte2le',
        'byte4be',
        'byte4le',
        'BASE64',
        'HTML-ENTITIES',
        '7bit',
        '8bit',
        'EUC-CN',
        'CP936',
        'GB18030',
        'HZ',
        'EUC-TW',
        'CP950',
        'BIG-5',
        'EUC-KR',
        'UHC',
        'CP949',
        'ISO-2022-KR',
        'Windows-1251',
        'CP1251',
        'Windows-1252',
        'CP1252',
        'CP866',
        'IBM866',
        'KOI8-R',
        'KOI8-U',
        'ArmSCII-8',
    );

    /**
     * @var array
     */
    protected $types=array('small','int','long','float','double','string');

    /**
     * @var array
     */
    protected $csv;

    /**
     * @var array
     */
    protected $registers;

    /**
     * @var array
     */
    protected $networks;

    /**
     * @var array
     */
    protected $ipData;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var array
     */
    protected $tmpFiles;

    /**
     * @var array
     */
    protected $meta;

    /**
     * Convertor constructor.
     *
     * @param string $tmp
     */
    public function __construct($tmp = null)
    {
        $this->errors = array();
        $this->temporaryDir = (is_dir($tmp))?$tmp:sys_get_temp_dir();
    }

    /**
     * Get errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set author of database
     *
     * @param string $author
     */
    public function setAuthor($author)
    {
        if (mb_strlen($author) > 64) $author = mb_substr($author,0,64);
        $this->author = $author;
    }

    /**
     * Set creation time of database
     *
     * @param integer $time
     */
    public function setTime($time)
    {
        $time = (int)$time;
        if ($time < 0) {
            $time = 0;
        }
        $this->time = $time;
    }

    /**
     * Set license of database
     *
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * Add source.
     *
     * @param string $name
     * @param string $file
     * @param bool $ignoreFirstRows
     * @param string $encoding
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     */
    public function addCSV($name,$file,$ignoreFirstRows=true,$encoding='UTF-8',$delimiter=',',$enclosure='"',$escape='\\')
    {
        $this->iterator['csv']++;
        $srcId = 'CSV #'.$this->iterator['csv'];
        if (!is_string($name) || !preg_match('/^[a-z0-9]+$/ui',$name)) {
            $this->errors[] = $srcId.' Parameter name must be string (a-z0-9)';
        }
        if (!is_string($file)){
            $this->errors[] = $srcId.' Parameter file must be string';
        }
        $test = @fopen($file,'rb');
        if ($test === false) {
            $this->errors[] = $srcId.' Can\'t open file';
        } else {
            fclose($test);
        }
        if (!in_array($encoding,$this->encodings)) {
            $this->errors[] = $srcId.' Parameter encoding must be '.implode(',',$this->encodings);
        }
        if (mb_strlen($delimiter) > 1) {
            $this->errors[] = $srcId.' Parameter delimiter must be one symbol';
        }
        if (mb_strlen($enclosure) > 1) {
            $this->errors[] = $srcId.' Parameter enclosure must be one symbol';
        }
        if (mb_strlen($escape) > 1) {
            $this->errors[] = $srcId.' Parameter escape must be one symbol';
        }
        if (!empty($this->errors)) return;
        $this->csv[$name]['file'] = $file;
        $this->csv[$name]['ignoreFirstRows'] = empty($ignoreFirstRows)?0:(int)$ignoreFirstRows;
        $this->csv[$name]['encoding'] = $encoding;
        $this->csv[$name]['delimiter'] = $delimiter;
        $this->csv[$name]['enclosure'] = $enclosure;
        $this->csv[$name]['escape'] = $escape;
    }

    /**
     * Add register.
     *
     * @param string  $name
     * @param string  $csv
     * @param integer $key
     * @param array   $fields
     */
    public function addRegister($name, $csv, $key, $fields)
    {
        $fieldIterator = 0;
        $transforms = array('up','low','none');
        $this->iterator['register']++;
        $rgId = 'Register #'.$this->iterator['register'];
        if (!is_string($name) || !preg_match('/^[a-z0-9]+$/ui',$name)) {
            $this->errors[] = $rgId.' Parameter name must be string (a-z0-9)';
        }
        if (!is_string($csv)) {
            $this->errors[] = $rgId.' Parameter csv must be string';
        } elseif (empty($this->csv[$csv])) {
            $this->errors[] = $rgId.' Need adding csv with function addCSV()';
        }
        if ((int)$key != $key) {
            $this->errors[] = $rgId.' Parameter key must be integer';
        }

        foreach ($fields as $field=>$datum) {
            $fieldIterator++;
            if (!is_string($field) || !preg_match('/^[a-z]+$/ui',$field)) {
                $this->errors[] = $rgId.'Name for field #'.$fieldIterator.' must be string (a-z)';
            }
            if (!is_string($datum['type']) || !in_array($datum['type'],$this->types)) {
                $this->errors[] = $rgId.' Parameter type for field #'.$fieldIterator.' must be '.implode(', ',$this->types);
            }
            if (isset($datum['transform']) && (!is_string($datum['transform']) || !in_array($datum['transform'], $transforms))) {
                $this->errors[] = $rgId.' Parameter transform for field #'.$fieldIterator.' must be '.implode(', ',$transforms);
            }
            if ((int)$datum['column'] != $datum['column']) {
                $this->errors[] = $rgId.' Parameter column for field #'.$fieldIterator.' must be integer';
            }
        }
        if (!empty($this->errors)) return;
        $this->registers[$name]['csv'] = $csv;
        $this->registers[$name]['key'] = $key;
        $this->registers[$name]['fields'] = $fields;
    }

    /**
     * Add networks.
     *
     * @param string $csv
     * @param string $ipFormat
     * @param integer $firstIp
     * @param integer $lastIp
     * @param array $registers
     */
    public function addNetworks($csv,$ipFormat,$firstIp,$lastIp,$registers)
    {
        $registersIterator = 0;
        $this->iterator['network']++;
        $netId = 'Network #'.$this->iterator['network'];
        if (!is_string($csv)) {
            $this->errors[] = $netId.' Parameter csv must be string';
        } elseif (empty($this->csv[$csv])) {
            $this->errors[] = $netId.' Need adding csv with function addCSV()';
        }
        $ipFormats = array('ip','long','inetnum');
        if (!is_string($ipFormat) || !in_array($ipFormat,$ipFormats)) {
            $this->errors[] = $netId.' Parameter ipFormat must be '.implode(', ',$ipFormats);
        }
        if ((int)$firstIp != $firstIp) {
            $this->errors[] = $netId.' Parameter firstIp must be integer';
        }
        if ((int)$lastIp != $lastIp) {
            $this->errors[] = $netId.' Parameter lastIp must be integer';
        }
        if (empty($registers)) {
            $this->errors[] = $netId.' Parameter registers can\'t be empty';
        } elseif (!is_array($registers)) {
            $this->errors[] = $netId.' Parameter registers must be array Register=>Column';
        }

        foreach ($registers as $register=>$column) {
            $registersIterator++;
            $this->ipData[$register] = $register;
            if (empty($this->registers[$register])) {
                $this->errors[] = $netId.' Need adding register #'.$registersIterator.' with function addRegister()';
            }
            if ((int)$column != $column) {
                $this->errors[] = $netId.' Value of register #'.$registersIterator.' must be integer';
            }
        }
        if (!empty($this->errors)) return;
        $this->networks[] = array(
            'csv'       => $csv,
            'ipFormat'  => $ipFormat,
            'firstIp'   => $firstIp,
            'lastIp'    => $lastIp,
            'registers' => $registers,
        );
    }

    /**
     * Create database from added sources.
     *
     * @param string $file
     */
    public function create($file)
    {
        if (!empty($this->errors)) return null;
        $tmpDb = $this->temporaryDir . DIRECTORY_SEPARATOR . uniqid().'tmp.sqlite';
        try {
            $this->pdo = new PDO('sqlite:' . $tmpDb);
            $this->pdo->exec('PRAGMA foreign_keys = 1;PRAGMA encoding = \'UTF-8\';');
        } catch (PDOException $e) {
            return;
        }
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->createTmpDb();
        $registers = $this->createTmpRegisters();
        $networks = $this->createTmpNetworks();

        /* Remove temporary SQLite database */
        if (is_writable($tmpDb)) unlink($tmpDb);

        /* Create header */
        $header = pack('C', self::VERSION);
        $header .= pack('C', count($this->meta['registers']));
        $nameLen = 1;
        $packLen = strlen($this->meta['networks']['pack']);
        $len = ($this->meta['networks']['len'] > 255)?'I1':'C1';
        $itm = ($this->meta['networks']['items'] > 255)?'I1':'C1';
        foreach ($this->meta['registers'] as $registerName => $register) {
            if (strlen($registerName) > $nameLen) $nameLen = strlen($registerName);
            if (strlen($register['pack']) > $packLen) $packLen = strlen($register['pack']);
            if (($register['len'] > 255) && $len == 'C1') $len = 'I1';
            if (($register['items'] > 255) && $itm == 'C1') $itm = 'I1';
        }
        $pack = 'A'.$nameLen.'A'.$packLen.$len.$itm;
        $unpack = 'A'.$nameLen.'name/A'.$packLen.'pack/'.$len.'len/'.$itm.'items';
        $header .= pack('I',strlen($unpack));
        $header .= pack('A*',$unpack);
        $header .= pack('I',strlen(pack($pack,'','',0,0)));
        foreach ($this->meta['registers'] as $registerName => $register) {
            $header .= pack($pack,$registerName,$register['pack'],$register['len'],$register['items']);
        }
        $header .= pack($pack,'n',$this->meta['networks']['pack'],$this->meta['networks']['len'],$this->meta['networks']['items']);
        $header .= $this->packArray('I*',$this->meta['index']);
        $headerLen = strlen($header);
        $letter = 'C';
        if ($headerLen > 255) $letter = 'I';

        /* Create binary database */
        $database = fopen($file,'w');
        fwrite($database,'DIT'.$letter.pack($letter,$headerLen).$header);

        /* Write networks to database */
        $stream = fopen($networks,'rb');
        stream_copy_to_stream($stream,$database);
        fclose($stream);

        /* Remove networks temporary file */
        if (is_writable($networks)) unlink($networks);

        /* Write registers to database */
        foreach ($registers as $register) {
            $stream = fopen($register,'rb');
            stream_copy_to_stream($stream,$database);
            fclose($stream);
            /* Remove register temporary file */
            if (is_writable($register)) unlink($register);
        }
        $time = empty($this->time)?time():$this->time;
        fwrite($database,pack('N1A128',$time,$this->author));
        fwrite($database,pack('A*',$this->license));
        fclose($database);
        return;
    }

    /**
     * Create temporary database.
     */
    protected function createTmpDb()
    {
        if (empty($this->registers)) return;
        foreach ($this->registers as $table=>$register) {
            $fields = array('`_pk`', '`_used`');
            $params = array(':_pk',':_used');
            $fieldToColumn = array();
            $fieldToColumn['_pk'] = $register['key'];
            foreach ($register['fields'] as $f=>$field) {
                $create[] = '`' . $f . '` TEXT';
                $fields[] = '`' . $f . '`';
                $params[] = ':' . $f;
                $fieldToColumn[$f] = $field['column'];
            }
            $sql = 'CREATE '.'TABLE `' . $table . '` (' . implode(',', $fields) . ', CONSTRAINT `_pk` PRIMARY KEY (`_pk`) ON CONFLICT IGNORE);';
            $sql .= 'CREATE '.'INDEX `_used` ON `'.$table.'` (`_used`);';
            $this->pdo->exec($sql);
            $sql = 'INSERT '.'INTO `'.$table.'` (' . implode(',', $fields) . ') VALUES (' . implode(',', $params) . ');';
            $prepare['insert'][$table] = $this->pdo->prepare($sql);
            $this->pdo->beginTransaction();
            $file = $this->csv[$register['csv']];
            $csv = fopen($file['file'], 'r');
            $rowIterator = 0;
            if ($csv !== false) {
                if (!empty($file['ignoreFirstRows'])) {
                    for($ignore=0; $ignore < $file['ignoreFirstRows']; $ignore++) {
                        $row = fgetcsv($csv, 4096, $file['delimiter'], $file['enclosure'], $file['escape']);
                        unset($row);
                    }
                }
                while ($row = fgetcsv($csv, 4096, $file['delimiter'], $file['enclosure'], $file['escape'])) {
                    $rowIterator++;
                    $rowId = ($register['key'] < 0)?$rowIterator:(isset($row[$register['key']]) ? $row[$register['key']] : $rowIterator);
                    $values = array('_pk'=>$rowId,'_used'=>0);
                    foreach ($fieldToColumn as $f=>$c) {
                        $values[$f] = isset($row[$c]) ? $row[$c] : null;
                        if (isset($register['fields'][$f]['transform']) && is_string($register['fields'][$f]['transform'])) {
                            switch ($register['fields'][$f]['transform']) {
                                case 'up': $values[$f] = mb_strtoupper($values[$f]); break;
                                case 'low': $values[$f] = mb_strtolower($values[$f]); break;
                            }
                        }
                        if (isset($this->registers[$table]['fields'][$f]) && $this->registers[$table]['fields'][$f]['type'] == 'string') {
                            $l = strlen($values[$f]);
                            if (!isset($this->registers[$table]['fields'][$f]['len'])) {
                                $this->registers[$table]['fields'][$f]['len'] = $l;
                            } elseif ($l > $this->registers[$table]['fields'][$f]['len']) {
                                $this->registers[$table]['fields'][$f]['len'] = $l;
                            }
                        }
                    }
                    /**
                     * @var \PDOStatement $p
                     */
                    $p = $prepare['insert'][$table];
                    $p->execute($values);
                }
                fclose($csv);
            }
            $this->pdo->commit();
        }

        $sql = 'CREATE '.'TABLE `_ips` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `ip` INTEGER,`action` TEXT, `parameter` TEXT, `value` TEXT, `offset` TEXT); CREATE INDEX `ip` ON `_ips` (`ip`); CREATE INDEX `parameter` ON `_ips` (`parameter`); CREATE INDEX `value` ON `_ips` (`value`);';
        $this->pdo->exec($sql);
        $prepare['insert']['ips'] = $this->pdo->prepare('INSERT '.'INTO `_ips` (`ip`,`action`,`parameter`,`value`) VALUES (:ip,:action,:parameter,:value);');
        foreach ($this->networks as $network) {
            $file = $this->csv[$network['csv']];
            $csv = fopen($file['file'], 'r');
            if ($csv !== false) {
                if (!empty($file['ignoreFirstRows'])) {
                    for ($ignore=0; $ignore < $file['ignoreFirstRows']; $ignore++) {
                        $row = fgetcsv($csv, 4096, $file['delimiter'], $file['enclosure'], $file['escape']);
                        unset($row);
                    }
                }
                $this->pdo->beginTransaction();
                while ($row = fgetcsv($csv, 4096, $file['delimiter'], $file['enclosure'], $file['escape'])) {
                    if (!isset($row[$network['firstIp']])) {
                        $this->errors[] = $network['csv'].' haven\'t column '.$network['firstIp'];
                    }
                    if ($network['lastIp'] != $network['firstIp'] && !isset($row[$network['lastIp']])) {
                        $this->errors[] = $network['csv'].' haven\'t column '.$network['lastIp'];
                    }
                    if (!empty($this->errors)) {
                        return;
                    }
                    $fip = $row[$network['firstIp']];
                    $lip = $row[$network['lastIp']];
                    $firstIp = false;
                    $lastIp = false;
                    switch ($network['ipFormat']) {
                        case 'inetnum':
                            if ($fip == $lip) {
                                $addrs = $this->parseInetnum($fip);
                                $firstIp = $addrs['first'];
                                $lastIp = $addrs['last'];
                            } else {
                                $addrs = $this->parseInetnum($fip);
                                $firstIp = $addrs['first'];
                                $addrs = $this->parseInetnum($lip);
                                $lastIp = $addrs['last'];
                            }
                            break;
                        case 'ip':
                            $firstIp = ip2long($fip);
                            $lastIp = ip2long($lip);
                            break;
                        case 'long':
                            $firstIp = $fip;
                            $lastIp = $lip;
                            break;
                    }
                    /**
                     * @var \PDOStatement $s
                     */

                    $s = $prepare['insert']['ips'];
                    foreach ($network['registers'] as $register => $column) {
                        $value = isset($row[$column]) ? $row[$column] : null;
                        $s->execute(array(
                            'ip' => $firstIp,
                            'action' => 'add',
                            'parameter' => $register,
                            'value' => $value,
                        ));
                        $s->execute(array(
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
                fclose($csv);
                $this->pdo->commit();
            }
        }
    }

    /**
     * Create temporary registers files.
     *
     * @return array
     */
    protected function createTmpRegisters()
    {
        if (empty($this->registers)) return array();
        $files = array();
        foreach ($this->registers as $table=>$register) {
            $offset = 0;
            $fields = array('`_pk`');
            $files[$table] = $this->temporaryDir . DIRECTORY_SEPARATOR . $table.'.'.uniqid().'.tmp';
            $format = array();
            $empty = array();
            foreach ($register['fields'] as $f=>$field) {
                $fields[] = '`'.$f.'`';
                $empty[$f] = null;
                switch ($field['type']) {
                    case 'string':
                        $format['pack'][] = 'A'.$field['len'];
                        $format['unpack'][] = 'A'.$field['len'].$f;
                        break;
                    case 'small':
                        $format['pack'][] = 'c';
                        $format['unpack'][] = 'c'.$f;
                        break;
                    case 'int':
                        $format['pack'][] = 'i';
                        $format['unpack'][] = 'i'.$f;
                        break;
                    case 'long':
                        $format['pack'][] = 'l';
                        $format['unpack'][] = 'l'.$f;
                        break;
                    case 'float':
                        $format['pack'][] = 'f';
                        $format['unpack'][] = 'f'.$f;
                        break;
                    case 'double':
                        $format['pack'][] = 'd';
                        $format['unpack'][] = 'd'.$f;
                        break;
                }
            }
            $pack = implode('',$format['pack']);
            $bin = self::packArray($pack,$empty);
            $this->meta['registers'][$table]['pack'] = implode('/',$format['unpack']);
            $this->meta['registers'][$table]['len'] = strlen($bin);
            $tmpFile = fopen($files[$table],'w');
            $data = $this->pdo->query('SELECT '.implode(',',$fields).' FROM `'.$table.'` WHERE `_used` = \'1\'');
            fwrite($tmpFile,$bin);
            $this->pdo->beginTransaction();
            while($row = $data->fetch()) {
                $rowId = $row['_pk'];
                unset($row['_pk']);
                $check = 0;
                foreach ($row as $cell=>$cellValue) {
                    if (!empty($cellValue)) $check = 1;
                }
                $bin = self::packArray($pack,$row);
                if ($check) {
                    $offset ++;
                    fwrite($tmpFile,$bin);
                }
                $this->pdo->exec('UPDATE '.'`_ips` SET `offset` =\''.($check?$offset:0).'\' WHERE `parameter` = \''.$table.'\' AND `value`=\''.$rowId.'\';');
            }
            $this->meta['registers'][$table]['items'] = $offset;
            $this->pdo->commit();
            fclose($tmpFile);
        }
        return $files;
    }

    /**
     * Create temporary networks files
     *
     * @return string
     */
    protected function createTmpNetworks()
    {
        $ip = 0;
        $fields = array();
        $values = array();
        $format = array();
        if (empty($this->registers)) return null;
        foreach ($this->registers as $register=>$null) {
            $format['pack'][$register] = 'C';
            $format['unpack'][$register] = 'C'.$register;
            if ($this->meta['registers'][$register]['items'] > 255) {
                $format['pack'][$register] = 'I';
                $format['unpack'][$register] = 'I'.$register;
            }
            $fields[$register] = null;
            $values[$register][] = 0;
        }
        $pack = implode('',$format['pack']);
        $empty = self::packArray($pack,$fields);
        $bin = pack('N',$ip).$empty;
        $this->meta['networks']['pack'] = implode('/',$format['unpack']);
        $offset = 0;
        $this->meta['networks']['len'] = strlen($bin);
        $this->meta['index'][0] = 0;
        $file = $this->temporaryDir.DIRECTORY_SEPARATOR.'networks.'.uniqid().'.tmp';
        $tmpFile = fopen($file,'w');
        $ipinfo = $this->pdo->query('SELECT * '.'FROM `_ips` ORDER BY `ip` ASC, `action` DESC, `id` ASC;');
        while ($row = $ipinfo->fetch()) {
            if ($row['ip'] !== $ip) {
                foreach ($values as $param=>$v) {
                    $fields[$param] = array_pop($v);
                }
                fwrite($tmpFile,pack('N',$ip).self::packArray($pack,$fields));
                $octet = (int)long2ip($ip);
                if (!isset($this->meta['index'][$octet])) $this->meta['index'][$octet] = $offset;
                $offset++;
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
                $fields[$param] = array_pop($v);
            }
            $octet = (int)long2ip($ip);
            if (!isset($this->meta['index'][$octet])) $this->meta['index'][$octet] = $offset;
            $offset++;
            fwrite($tmpFile, pack('N', $ip) . self::packArray($pack, $fields));
        }
        $this->meta['networks']['items'] = $offset;
        for($i=1;$i<=255;$i++) {
            if (!isset($this->meta['index'][$i])) $this->meta['index'][$i] = $this->meta['index'][$i-1];
        }
        ksort($this->meta['index']);
        fclose($tmpFile);
        unset($ip);
        return $file;
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

    /**
     * Get first and last IP addresses by prefix or inetnum
     *
     * @param string $prefixOrInetnum
     * @return array
     */
    public static function parseInetnum($prefixOrInetnum)
    {
        $result = ['first'=>null,'last'=>null];
        if (strpos($prefixOrInetnum,'-') !== false) {
            $d = explode('-',$prefixOrInetnum);
            $result['first'] = trim($d[0]);
            $result['last'] = trim($d[1]);
        }
        if (strpos($prefixOrInetnum,'/') !== false) {
            $d = explode('/',$prefixOrInetnum);
            $ipnum = ip2long((string) $d[0]);
            $prefix = filter_var($d[1], \FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 32]
            ]);
            if (false === $ipnum or false === $prefix) {
                return $result;
            }
            $netsize = 1 << (32 - $prefix);
            $end_num = $ipnum + $netsize - 1;
            if ($end_num >= (1 << 32)) {
                return $result;
            }
            $result['first'] = $ipnum;
            $result['last'] = $end_num;
        }
        return $result;
    }
}