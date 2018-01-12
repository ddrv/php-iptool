<?php
namespace Ddrv\Tests\Iptool;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Iptool;
use Ddrv\Iptool\Wizard;
use Ddrv\Iptool\Wizard\Register;
use Ddrv\Iptool\Wizard\Network;
use Ddrv\Iptool\Wizard\Fields\StringField;
use Ddrv\Iptool\Wizard\Fields\NumericField;

/**
 * @covers Iptool
 * @covers Wizard
 */
class DatabaseTest extends TestCase
{
    /**
     * Compile simple database.
     *
     * @throws \ErrorException
     */
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

        $countries = (new Register($csvDir.DIRECTORY_SEPARATOR.'countries.csv'))
            ->setCsv('UTF-8')
            ->setFirstRow(2)
            ->setId(1)
            ->addField('code', 2, new StringField(StringField::TRANSFORM_LOWER, 2))
            ->addField('name', 3, new StringField())
        ;
        $cities = (new Register($csvDir.DIRECTORY_SEPARATOR.'cities.csv'))
            ->setCsv('UTF-8')
            ->setFirstRow(2)
            ->setId(1)
            ->addField('name', 2, new StringField(0))
            ->addField('country', 3, new NumericField(0))
        ;
        $network = (new Network($csvDir.DIRECTORY_SEPARATOR.'networks.csv', Network::IP_TYPE_ADDRESS, 1, 2))
            ->setCsv('UTF-8')
            ->setFirstRow(2)
        ;

        $wizard = (new Wizard($tmpDir))
            ->setAuthor($author)
            ->setTime($time)
            ->setLicense($license)
            ->addRegister('city', $cities)
            ->addRegister('country', $countries)
            ->addRelation('city', 3, 'country')
            ->addNetwork(
                $network,
                array(
                    3 => 'city',
                )
            )
        ;
        $wizard->compile($dbFile);


        $tmpFiles = glob($tmpDir.DIRECTORY_SEPARATOR.'*');
        foreach ($tmpFiles as $tmpFile) {
            unlink($tmpFile);
        }
        rmdir($tmpDir);
    }
}