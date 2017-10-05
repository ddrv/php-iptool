<?php
ini_set("memory_limit", "64M");
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Converter.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Iptool.php');
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

/**
 * Set author.
 */
$converter->setAuthor('Ivan Dudarev');

/**
 * Set license.
 */
$converter->setLicense('MIT');

/**
 * Add source files.
 */
$converter->addCSV('locations',$locations,2);
$converter->addCSV('networks',$networks,2);

/**
 * Add register Geo.
 **/
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

/**
 * Add networks.
 */
$data = array(
     'geo' => 2,
);
$converter->addNetworks('networks', 'long', 0, 1, $data);

/**
 * Create Database.
 */
$converter->create($dbFile);

/**
 * Delete temporary file
 */
unlink($tmpFile);

/**
 * Get information about created database
 */
$iptool = new \Ddrv\Iptool\Iptool($dbFile);
print_r($iptool->about());

/**
 * Search IP Address data
 */
print_r($iptool->find('95.215.84.0'));