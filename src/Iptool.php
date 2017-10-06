<?php
namespace Ddrv\Iptool {

    /**
     * Class Iptool
     *
     * @property boolean  $isCorrect
     * @property array    $errors
     * @property array    $meta
     * @property integer  $fileSize
     * @property resource $db
     */
    class Iptool {
        /**
         * Parser version.
         */
        const VERSION = '1';

        /**
         * Correct read database flag.
         *
         * @var boolean
         */
        protected $isCorrect=false;

        /**
         * Errors.
         *
         * @var array
         */
        protected $errors=array();

        /**
         * Metadata.
         *
         * @var array
         */
        protected $meta=array();

        /**
         * Size of database file.
         *
         * @var integer
         */
        protected $fileSize;

        /**
         * Database file handler.
         *
         * @var resource
         */
        protected $db;

        /**
         * Iptool constructor.
         *
         * @param string $databaseFile
         */
        public function __construct($databaseFile) {
            if (!is_readable($databaseFile)) {
                $this->errors[] = 'can\'t read file '.$databaseFile ;
                return;
            }
            $this->db = fopen($databaseFile, 'rb');
            $d = fread($this->db, 4);
            $dit = substr($d,0,3);
            $letter = substr($d,3,1);
            if ($dit !== 'DIT' || !in_array($letter,array('C','I','L'))) {
                fclose($this->db);
                $this->errors[] = 'file '.$databaseFile.' is not Ddrv\\Iptool database' ;
                return;
            }
            $len = $letter=='C'?1:4;
            $tmp = unpack($letter.'headerLen', fread($this->db, $len));
            $headerLen = $tmp['headerLen'];
            $header = fread($this->db,$headerLen);
            $offset = 0;
            $tmp = unpack('Cver/Ccount/IformatLen',substr($header,$offset,6));
            $offset += 6;
            $this->meta = [
                'dit' => $dit,
                'version' => $tmp['ver'],
            ];
            $registersCount = $tmp['count'];
            $registersFormatLen = $tmp['formatLen'];
            $tmp = unpack('A*format',substr($header,$offset,$registersFormatLen));
            $offset += $registersFormatLen;

            $registersFormat = $tmp['format'];
            $tmp = unpack('IregistersDefineLen',substr($header,$offset,4));
            $offset += 4;
            $registersDefineLen = $tmp['registersDefineLen'];

            for($i=0;$i<$registersCount;$i++) {
                $tmp = unpack($registersFormat,substr($header,$offset,$registersDefineLen));
                $this->meta['registers'][$tmp['name']] = array(
                    'pack' => $tmp['pack'],
                    'len' => $tmp['len'],
                    'items' => $tmp['items'],
                );
                $offset += $registersDefineLen;
            }
            $tmp = unpack($registersFormat,substr($header,$offset,$registersDefineLen));
            $this->meta['networks'] = array(
                'pack' => $tmp['pack'],
                'len' => $tmp['len'],
                'items' => $tmp['items'],
            );
            $offset += $registersDefineLen;
            $this->meta['index'] = array_values(unpack('I256',substr($header,$offset,1024)));
            $offset = strlen($header)+$len+4;
            $this->meta['networks']['offset'] = $offset;
            $offset += ($this->meta['networks']['len'] * $this->meta['networks']['items']);
            foreach ($this->meta['registers'] as $r=>$register) {
                $this->meta['registers'][$r]['offset'] = $offset;
                $offset += ($register['len'] * ($register['items']+1));
            }
            $this->fileSize = filesize($databaseFile);
            $this->isCorrect = true;
        }

        public function __destruct() {
            if (is_resource($this->db)) fclose($this->db);
        }


        public function find($ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) return false;
            if (!$this->isCorrect) return false;
            $registers = false;
            $data = array();
            $octet = (int)$ip;
            $long = pack('N',ip2long($ip));
            $start = $stop = $this->meta['index'][$octet];
            while($octet < 255 && $this->meta['index'][$octet] == $start) {
                $stop = $this->meta['index'][$octet];
                $octet++;
            }
            if ($stop == $start) {
                $stop = $this->meta['networks']['items'];
            }
            if ($start > 0) {
                $start--;
            }
            $blockCount = $stop-$start;
            $seek = $this->meta['networks']['offset']+($start*$this->meta['networks']['len']);
            fseek($this->db,$seek);
            $blocks = fread($this->db,$blockCount*$this->meta['networks']['len']);
            $offset = 0;
            $step = ceil(sqrt($blockCount));

            $check = substr($blocks, $offset + ($this->meta['networks']['len'] * $step), 4);
            while ($check && $long >= $check && ($offset < ($blockCount)*$this->meta['networks']['len'])) {
                $offset += $this->meta['networks']['len'] * $step;
                $check = substr($blocks, $offset + ($this->meta['networks']['len'] * $step), 4);
            }

            do {
                if ($offset > strlen($blocks)) {
                    $offset = strlen($blocks)-$this->meta['networks']['len'];
                }
                $block = substr($blocks,$offset,$this->meta['networks']['len']);
                $first = substr($block,0,4);
                $next = substr($blocks,$offset+$this->meta['networks']['len'],4);
                if ($first <= $long && ($long < $next || $next === false)) {
                    $registers = unpack($this->meta['networks']['pack'],substr($block,4));
                }
                $offset += $this->meta['networks']['len'];
            } while($offset < strlen($blocks) && !$registers);
            if (!$registers) {
                foreach ($this->meta['registers'] as $r=>$register) {
                    $registers[$r] = 0;
                }
            }
            if (!$next) {
                $next = pack('N',ip2long('255.255.255.255')+1);
            }
            $network = unpack('Nfirst/Nlast',$first.$next);
            $data['network'] = array(long2ip($network['first']),long2ip($network['last']-1));
            foreach ($registers as $register=>$item) {
                $data['data'][$register] = $this->getRegisterRecord($register,$item);
            }
            return $data;
        }

        public function getRegisterRecord($register,$item) {
            if ($item > $this->meta['registers'][$register]['items']) $item = 0;
            $seek = $this->meta['registers'][$register]['offset']+($item * $this->meta['registers'][$register]['len']);
            fseek($this->db,$seek);
            $data = unpack($this->meta['registers'][$register]['pack'],fread($this->db,$this->meta['registers'][$register]['len']));
            return $data;
        }

        public function getErrors() {
            return $this->errors;
        }

        public function about() {
            $about = array(
                'created' => 0,
                'author' => '',
                'license' => '',
                'networks' => array(
                    'count' => $this->meta['networks']['items'],
                    'data' => array(),
                ),
            );
            $register = array('offset'=>0,'len'=>0,'items'=>0);
            foreach ($this->meta['registers'] as $r=>$register) {
                $about['networks']['data'][$r] = array_keys(unpack($register['pack'],str_pad('',$register['len'],' ')));
            }
            $offset = $register['offset'] + ($register['len'] * ($register['items']+1));
            fseek($this->db,$offset);
            $info = fread($this->db,$this->fileSize-$offset);
            $tmp = unpack('N1created/A128author/A*license', $info);
            $about = array_replace($about, $tmp);
            return $about;
        }

        /**
         * Return array of register rows
         *
         * @param $register
         * @return array
         */
        public function getRegister($register) {
            if (!isset($this->meta['registers'][$register])) return array();
            $result = array();
            $seek = $this->meta['registers'][$register]['offset']+$this->meta['registers'][$register]['len'];
            fseek($this->db,$seek);
            for($i = 1;$i<=$this->meta['registers'][$register]['items'];$i++) {
                $result[$i] = unpack($this->meta['registers'][$register]['pack'],fread($this->db,$this->meta['registers'][$register]['len']));
            }
            return $result;
        }
    }
}