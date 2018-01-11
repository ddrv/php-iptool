<?php
namespace Ddrv\Tests\Iptool;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Iptool;
use Ddrv\Iptool\Wizard;
use Ddrv\Iptool\Wizard\Register;
use Ddrv\Iptool\Wizard\Network;
use Ddrv\Iptool\Wizard\Types\StringType;
use Ddrv\Iptool\Wizard\Types\AddressType;

/**
 * @covers Iptool
 * @covers Wizard
 */
class DatabaseTest extends TestCase
{
    public function testSimple()
    {
        $time = time();
        $author = 'Unit Test';
        $license = 'Test License';
        $tmpDir = IPTOOL_TEST_TMP_DIR;
        $csvDir = IPTOOL_TEST_CSV_DIR.DIRECTORY_SEPARATOR.'simple';
        $dbFile = IPTOOL_TEST_TMP_DIR.DIRECTORY_SEPARATOR.'iptool.simple.dat';

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir);
        }

        $wizard = new Wizard($tmpDir);

        $wizard->setAuthor($author);
        $wizard->setTime($time);
        $wizard->setLicense($license);

        $address = (new AddressType(AddressType::FORMAT_IP));
        $countryCode = (new StringType(StringType::TRANSFORM_LOWER))->setMaxLength(2);
        $countryName = (new StringType());
        $countries = (new Register($csvDir.DIRECTORY_SEPARATOR.'countries.csv'))
            ->setCsv('UTF-8')
            ->setId(1)
            ->setFirstRow(2)
            ->addField('code', 2, $countryCode)
            ->addField('name', 3, $countryName)
            ;
        $network = (new Network($csvDir.DIRECTORY_SEPARATOR.'networks.csv', $address, 1,2))
            ->setCsv('UTF-8')
            ->setFirstRow(2)
            ->addRegister('country',3, $countries)
        ;
        $wizard->addNetwork($network);
        $wizard->compile($dbFile);

        $tmpFiles = glob($tmpDir.DIRECTORY_SEPARATOR.'*');
        foreach ($tmpFiles as $tmpFile) {
            unlink($tmpFile);
        }
        rmdir($tmpDir);
    }
}