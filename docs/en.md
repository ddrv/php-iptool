# 1. Install

## 1.1. Requiriments
* PHP 5.3 or later
* PHP Extention PDO
* PHP Extention PDO SQLite

## 1.2. Install with composer
1. Add package to requires:
    ```text
    php composer.phar require ddrv/iptool
    ```
2. Include autoload file
    ```php
    include('vendor/autoload.php');
    ```
## 1.3. Install without composer
1. Download [Archive](https://github.com/ddrv/iptool/archive/master.zip)
2. Unzip archive to /path/to/libraries/
3. Include files
    ```php
    /* If need converter */
    require_once('/path/to/libraries/iptool/src/Converter.php');
    /* If need finder */
    require_once('/path/to/libraries/iptool/src/Iptool.php');
    ```
# 2. Using

## 2.1. Initialization IP Tool
```php
$iptool = new \Ddrv\Iptool\Iptool('/path/to/iptool.database');
```

## 2.2. Get information about created database
```php
print_r($iptool->about());
```
```text
Array
(
    [created] => 1507199627
    [author] => Ivan Dudarev
    [license] => MIT
    [networks] => Array
        (
            [count] => 276148
            [data] => Array
                (
                    [country] => Array
                        (
                            [0] => code
                            [1] => name
                        )
                )
        )
)
```
## 2.3. Search information about IP Address
```php
print_r($iptool->find('81.32.17.89'));
```
```text
Array
(
    [network] => Array
        (
            [0] => 81.32.0.0
            [1] => 81.48.0.0
        )
    [data] => Array
        (
            [country] => Array
                (
                    [code] => es
                    [name] => Spain
                )
        )
)
```

## 2.4. Get all items in register
```php
print_r($iptool->getRegister('country'));
```
```text
Array
(
    [1] => Array
        (
            [code] => cn
            [name] => China
        )
    [2] => Array
        (
            [code] => es
            [name] => Spain
        )
...
    [N] => Array
        (
            [code] => jp
            [name] => Japan
        )
)
```

## 2.5. Get register item
```php
print_r($iptool->getRegister('country',2));
```
```text
Array
    (
        [code] => cn
        [name] => China
    )
)
```
## 2.6. Create database

Use Converter class for create database
1. Prepare files describing the intervals and registers from which you are going to compile a binary database. These files should look like a relational database table.
    ###### for example
    Intervals file `/path/to/cvs/networks.csv`
    ```text
    first_ip,last_ip,register_id
    "0.0.0.0","63.255.255.255",89
    "64.0.0.0","127.255.255.255",192
    "128.0.0.0","191.255.255.255",2
    "192.0.0.0","255.255.255.255",17
    ```
    Register file `/path/to/cvs/info.csv`.
    ```text
    Unused row. e.g. copiryght. Second row is a header, also unused.
    id,interval_num,data1,data2
    2,3,"some info 1","some info 2"
    17,4,"some info 3","some info 4"
    89,1,"some info 5","some info 6"
    192,2,"some info 7","some info 8"
    34,"unused row","some info 9","some info 10"
    ```
1. Initialization converter
    ```php
    $tmpDir = 'path/to/dir/for/temporary/files';
    $converter = new \Ddrv\Iptool\Converter($tmpDir);
    ```
    
1. Set creation time.
    ```php
    /**
     * $time - time in unixstamp format.
     */
    $converter->setTime(1507638600); // 2017/10/10 15:30:00
    ```

1. Set author information
    ```php
    /**
     * $author can be a string no longer than 64 characters.
     */
    $author = 'Name Surname';
    $converter->setAuthor($author);
    ```

1. Set license of database
    ```php
    /**
     * $license may be the name of a public license or the text of a license. The length is unlimited.
     */
    $license = 'MIT';
    $converter->setLicense($license);
    ```

1. Add prepared files. Use the addCSV() method with parameters:
    * unique key for file. Required;
    * path to csv file. Required;
    * count ignored first row (default 0);
    * CSV file ecnoding (default UTF-8);
    * delimeter of CSV (default ,);
    * enclosure of CSV (default ");
    * escape of CSV (default \).
    ```php
    $converter->addCSV('infoCSV','/path/to/cvs/info.csv',2);
    $converter->addCSV('networksCSV','/path/to/cvs/networks.csv',1);
    ```

1. Define format of registers
    ```php
    $info = array(
        'interval' => array(
            /**
             * Type can be:
             * small - for integer from -128 to 127;
             * int - integer;
             * long - long integer;
             * float - float;
             * double - double (use it for coordinats);
             * string - string.
             */
            'type' => 'int',
            /**
             * The column number from which to take the parameter value. The account is from 0
             */
            'column' => 1,
        ),
        'caption' => array(
            'type' => 'string',
            'column' => 2,
            /**
             * For string type you can add a parameter transform. It can be:
             * low - converts a string to lowercase;
             * up - converts a string to uppercase;
             * none - leave the string as is. Default.
             */
            'transform' => 'low',
        ),
        'extendedInfo' => array(
            'type' => 'string',
            'column' => 3,
        ),
    );
    ```
1. Add definite registers. Use the addRegister() method with parameters:
    * Register name. Required;
    * unique key of CSV file (from method addCSV). Required;
    * number of ID column. The account is from 0;
    * definite format of register.
    ```php
    $converter->addRegister('info','infoCSV',0, $info);
    ```
1. Define format of intervals
    ```php
    $networks = array(
        /**
         * key is a name register
         * value ia number of column with ID of register
         */
        'info' => 2,
    );
    ```

1. Add definite intervals. Use the addNetworks() method with parameters:
    * unique key of intervals CSV file (from method addCSV). Required;
    * format IP address in CSV file. May be:
        * ip (for example, 123.123.123.123);
        * long (for example 1361051648);
        * inetnum (for example 1.0.0.0/24).
    * column of first IP adderss. The account is from 0;
    * column of last IP adderss. The account is from 0;
    * definite format of intervals.
    ```php
    $converter->addNetworks('networksCSV', 'ip', 0, 1, $networks);
    ```
1. Run compile database
    ```php
    $errors = $converter->getErrors();
    if (!$errors) {
        $dbFile = 'path/to/created/database.file';
        $converter->create($dbFile);
    } else {
        print_r($errors);
    }
    ```
    Wait and use!

# 3. Database format

|Size|Caption|
|---|---|
|3|A control word that confirms the authenticity of the file. It is always equal to DIT|
|4|Header size (L)|
|1|Iptool format version|
|1|Registers count (RC)|
|4|Size of registers metadata unpack formats (RF)|
|1|Size of relations unpack format (LRF)|
|2|Size of relation define (LRD)|
|1|Relations count (RLC)|
|LRF|Relation unpack format|
|RLC*LRD|Relations|
|4|Size of registers metadata(RD)|
|RF|Registers metadata unpack format|
|RD*(RC+1)|Registers metadata|
|1024|Index of first octets|
|?|Database of intervals|
|?|Database of Register 1|
|?|Database of Register 2|
|...|...|
|?|Database of Register RC|
|4|Unixstamp of database creation time|
|128|Author|
|?|Database license|

# 4. Examples
## 4.1. Create DB from GeoLite2 Country
```php
<?php
$tmpDir = __DIR__.DIRECTORY_SEPARATOR.'tmp';
$converter = new \Ddrv\Iptool\Converter($tmpDir);
$dbFile = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'iptool.geo.country.dat';

$url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country-CSV.zip';
$tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'geolite2country.zip';
copy($url,$tmpFile);
$zip = new ZipArchive();
if ($zip->open($tmpFile) !== true) die;
$i = -1;
$zipPath = null;
do {
    $i++;
    $csv = $zip->getNameIndex($i);
    preg_match('/(?<file>(?<zipPath>.*)\/GeoLite2\-Country\-Blocks\-IPv4\.csv)$/ui', $csv, $m);
} while ($i < $zip->numFiles && empty($m['file']));
$zipPath = $m['zipPath'];
$zip->close();

$locations = 'zip://' . $tmpFile . '#'.$zipPath.DIRECTORY_SEPARATOR.'GeoLite2-Country-Locations-en.csv';
$networks = 'zip://' . $tmpFile . '#' . $m['file'];

/* Set author. */
$converter->setAuthor('Ivan Dudarev');

/* Set license. */
$converter->setLicense('MIT');

/* Add source files. */
$converter->addCSV('locations',$locations,1);
$converter->addCSV('networks',$networks,1);

/* Add register Country. */
$country = array(
    'code' => array(
        'type' => 'string',
        'column' => 4,
        'transform' => 'low',
    ),
    'name' => array(
        'type' => 'string',
        'column' => 5,
    ),
);
$converter->addRegister('country','locations',0, $country);

/* Add networks. */
$data = array(
     'country' => 1,
);
$converter->addNetworks('networks', 'inetnum', 0, 0, $data);

/* Create Database. */
$converter->create($dbFile);

/* Delete temporary file. */
unlink($tmpFile);

/* Get information about created database. */
$iptool = new \Ddrv\Iptool\Iptool($dbFile);
print_r($iptool->about());

/* Search IP Address data. */
print_r($iptool->find('95.215.84.0'));
```
## 4.2. Create DB from GeoLite2 City
```php
<?php
$tmpDir = __DIR__.DIRECTORY_SEPARATOR.'tmp';
$converter = new \Ddrv\Iptool\Converter($tmpDir);
$dbFile = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'iptool.geo.city.dat';

$url = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCity_CSV/GeoLiteCity-latest.zip';
$tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'geolite2city.zip';
copy($url,$tmpFile);
$zip = new ZipArchive();
if ($zip->open($tmpFile) !== true) die;
$i = -1;
$zipPath = null;
do {
    $i++;
    $csv = $zip->getNameIndex($i);
    preg_match('/(?<file>(?<zipPath>.*)\/GeoLiteCity\-Blocks\.csv)$/ui', $csv, $m);
} while ($i < $zip->numFiles && empty($m['file']));
$zipPath = $m['zipPath'];
$zip->close();

$locations = 'zip://' . $tmpFile . '#'.$zipPath.DIRECTORY_SEPARATOR.'GeoLiteCity-Location.csv';
$networks = 'zip://' . $tmpFile . '#' . $m['file'];

/* Set author. */
$converter->setAuthor('Ivan Dudarev');

/* Set license. */
$converter->setLicense('MIT');

/* Add source files. */
$converter->addCSV('locations',$locations,2);
$converter->addCSV('networks',$networks,2);

/* Add register Geo. */
$geo = array(
    'geonames' => array(
        'type' => 'int',
        'column' => 0,
    ),
    'country' => array(
        'type' => 'string',
        'column' => 1,
        'transform' => 'low',
    ),
    'region' => array(
        'type' => 'string',
        'column' => 2,
    ),
    'city' => array(
        'type' => 'string',
        'column' => 3,
    ),
    'latitude' => array(
        'type' => 'double',
        'column' => 5,
    ),
    'longitude' => array(
        'type' => 'double',
        'column' => 6,
    ),
);
$converter->addRegister('geo','locations',0, $geo);

/* Add networks. */
$data = array(
     'geo' => 2,
);
$converter->addNetworks('networks', 'long', 0, 1, $data);

/* Create Database. */
$converter->create($dbFile);

/* Delete temporary file. */
unlink($tmpFile);

/* Get information about created database. */
$iptool = new \Ddrv\Iptool\Iptool($dbFile);
print_r($iptool->about());

/* Search IP Address data. */
print_r($iptool->find('95.215.84.0'));
```
