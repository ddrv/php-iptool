<?php
ini_set("memory_limit", "64M");
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Converter.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Iptool.php');
$tmpDir = __DIR__.DIRECTORY_SEPARATOR.'tmp';
$converter = new \Ddrv\Iptool\Converter($tmpDir);
$dbFile = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'iptool.geo.country.dat';

$url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country-CSV.zip';
$tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'tmp.zip';
copy($url,$tmpFile);
$zip = new ZipArchive();
if ($zip->open($tmpFile) !== true) die;
$i = -1;
$zipPath = null;
do {
    $i++;
    $csv = $zip->getNameIndex($i);
    preg_match('/(?<file>(?<zipPath>.*)\/GeoLite2-Country-Blocks-IPv4\.csv)$/ui', $csv, $m);
} while ($i < $zip->numFiles && empty($m['file']));
$zipPath = $m['zipPath'];
$zip->close();

$locations = 'zip://' . $tmpFile . '#'.$zipPath.DIRECTORY_SEPARATOR.'GeoLite2-Country-Locations-en.csv';
$geo = 'zip://' . $tmpFile . '#' . $m['file'];

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
$converter->addCSV('locations',$locations);
$converter->addCSV('geo',$geo);

/**
 * Add register Country.
 **/
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

/**
 * Add networks.
 */
$geo = array(
     'country' => 1,
);
$converter->addNetworks('geo', 'inetnum', 0, 0, $geo);

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
print_r($iptool->find('81.32.17.89'));