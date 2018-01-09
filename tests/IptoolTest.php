<?php
namespace Ddrv\Tests\Iptool;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Iptool;
use Ddrv\Iptool\Converter;

/**
 * @covers Iptool
 * @covers Converter
 */
class IptoolTest extends TestCase
{
    public function testSimple()
    {
        $time = time();
        $author = 'Unit Test';
        $license = 'Test License';
        $tmpDir = __DIR__.DIRECTORY_SEPARATOR.'tmp';
        $csvDir = __DIR__.DIRECTORY_SEPARATOR.'csv'.DIRECTORY_SEPARATOR.'simple';
        $dbFile = __DIR__.DIRECTORY_SEPARATOR.'dat'.DIRECTORY_SEPARATOR.'iptool.test.dat';
        $converter = new Converter($tmpDir);

        $converter->setAuthor($author);
        $converter->setTime($time);
        $converter->setLicense($license);

        $converter->addCSV('infoCSV',$csvDir.DIRECTORY_SEPARATOR.'info.csv',1);
        $converter->addCSV('networksCSV',$csvDir.DIRECTORY_SEPARATOR.'networks.csv',1);

        $info = array(
            'interval' => array(
                'type' => 'int',
                'column' => 1,
            ),
            'caption' => array(
                'type' => 'string',
                'column' => 2,
                'transform' => 'low',
            ),
            'extendedInfo' => array(
                'type' => 'string',
                'column' => 3,
            ),
        );
        $converter->addRegister('info','infoCSV',0, $info);

        $networks = array(
            'info' => 2,
        );
        $converter->addNetworks('networksCSV', 'ip', 0, 1, $networks);
        $converter->create($dbFile);

        $iptool = new Iptool($dbFile);
        $meta = $iptool->about();

        $this->assertSame($meta['created'], $time);
        $this->assertSame($meta['author'], $author);
        $this->assertSame($meta['license'], $license);
        $this->assertSame($meta['networks']['count'], 4);
        $this->assertSame($meta['networks']['data']['info'][0], 'interval');
        $this->assertSame($meta['networks']['data']['info'][1], 'caption');
        $this->assertSame($meta['networks']['data']['info'][2], 'extendedInfo');

        $info = $iptool->find('0.0.0.1');
        $this->assertSame($info['network']['first'], '0.0.0.0');
        $this->assertSame($info['network']['last'], '63.255.255.255');
        $this->assertSame($info['data']['info']['interval'], 1);
        $this->assertSame($info['data']['info']['caption'], 'some info 5');
        $this->assertSame($info['data']['info']['extendedInfo'], 'some info 6');

        $info = $iptool->find('64.0.10.0');
        $this->assertSame($info['network']['first'], '64.0.0.0');
        $this->assertSame($info['network']['last'], '127.255.255.255');
        $this->assertSame($info['data']['info']['interval'], 2);
        $this->assertSame($info['data']['info']['caption'], 'some info 7');
        $this->assertSame($info['data']['info']['extendedInfo'], 'some info 8');

        $info = $iptool->find('128.100.0.13');
        $this->assertSame($info['network']['first'], '128.0.0.0');
        $this->assertSame($info['network']['last'], '191.255.255.255');
        $this->assertSame($info['data']['info']['interval'], 3);
        $this->assertSame($info['data']['info']['caption'], 'some info 1');
        $this->assertSame($info['data']['info']['extendedInfo'], 'some info 2');

        $info = $iptool->find('202.100.0.13');
        $this->assertSame($info['network']['first'], '192.0.0.0');
        $this->assertSame($info['network']['last'], '255.255.255.255');
        $this->assertSame($info['data']['info']['interval'], 4);
        $this->assertSame($info['data']['info']['caption'], 'some info 3');
        $this->assertSame($info['data']['info']['extendedInfo'], 'some info 4');

        unlink($dbFile);
        $tmpFiles = glob($tmpDir.DIRECTORY_SEPARATOR.'*');
        foreach ($tmpFiles as $tmpFile) {
            if ($tmpFile != $tmpDir.DIRECTORY_SEPARATOR.'.gitkeep') {
                unlink($tmpFile);
            }
        }
    }

    public function testJoinIntervals()
    {
        $time = time();
        $author = 'Unit Test';
        $license = 'Test License';
        $tmpDir = __DIR__.DIRECTORY_SEPARATOR.'tmp';
        $csvDir = __DIR__.DIRECTORY_SEPARATOR.'csv'.DIRECTORY_SEPARATOR.'joinIntervals';
        $dbFile = __DIR__.DIRECTORY_SEPARATOR.'dat'.DIRECTORY_SEPARATOR.'iptool.join.dat';
        $converter = new Converter($tmpDir);

        $converter->setAuthor($author);
        $converter->setTime($time);
        $converter->setLicense($license);

        $converter->addCSV('infoCSV',$csvDir.DIRECTORY_SEPARATOR.'info.csv',1);
        $converter->addCSV('networksCSV',$csvDir.DIRECTORY_SEPARATOR.'networks.csv',1);

        $info = array(
            'name' => array(
                'type' => 'string',
                'column' => 1,
            ),
        );
        $converter->addRegister('info','infoCSV',0, $info);

        $networks = array(
            'info' => 2,
        );
        $converter->addNetworks('networksCSV', 'ip', 0, 1, $networks);
        $converter->create($dbFile);

        $iptool = new Iptool($dbFile);
        $meta = $iptool->about();

        $this->assertSame($meta['created'], $time);
        $this->assertSame($meta['author'], $author);
        $this->assertSame($meta['license'], $license);
        $this->assertSame($meta['networks']['count'], 2);
        $this->assertSame($meta['networks']['data']['info'][0], 'name');

        $info = $iptool->find('0.0.0.1');
        $this->assertSame($info['network']['first'], '0.0.0.0');
        $this->assertSame($info['network']['last'], '63.255.255.255');
        $this->assertSame($info['data']['info']['name'], 'small interval');

        $info = $iptool->find('64.0.10.0');
        $this->assertSame($info['network']['first'], '64.0.0.0');
        $this->assertSame($info['network']['last'], '255.255.255.255');
        $this->assertSame($info['data']['info']['name'], 'big interval');

        unlink($dbFile);
        $tmpFiles = glob($tmpDir.DIRECTORY_SEPARATOR.'*');
        foreach ($tmpFiles as $tmpFile) {
            if ($tmpFile != $tmpDir.DIRECTORY_SEPARATOR.'.gitkeep') {
                unlink($tmpFile);
            }
        }
    }

    public function testMiddleIntervals()
    {
        $time = time();
        $author = 'Unit Test';
        $license = 'Test License';
        $tmpDir = __DIR__.DIRECTORY_SEPARATOR.'tmp';
        $csvDir = __DIR__.DIRECTORY_SEPARATOR.'csv'.DIRECTORY_SEPARATOR.'middleIntervals';
        $dbFile = __DIR__.DIRECTORY_SEPARATOR.'dat'.DIRECTORY_SEPARATOR.'iptool.test.dat';
        $converter = new Converter($tmpDir);

        $converter->setAuthor($author);
        $converter->setTime($time);
        $converter->setLicense($license);

        $converter->addCSV('infoCSV',$csvDir.DIRECTORY_SEPARATOR.'info.csv',1);
        $converter->addCSV('networksCSV',$csvDir.DIRECTORY_SEPARATOR.'networks.csv',1);

        $info = array(
            'name' => array(
                'type' => 'string',
                'column' => 1,
            ),
        );
        $converter->addRegister('info','infoCSV',0, $info);

        $networks = array(
            'info' => 2,
        );
        $converter->addNetworks('networksCSV', 'ip', 0, 1, $networks);
        $converter->create($dbFile);

        $iptool = new Iptool($dbFile);
        $meta = $iptool->about();

        $this->assertSame($meta['created'], $time);
        $this->assertSame($meta['author'], $author);
        $this->assertSame($meta['license'], $license);
        $this->assertSame($meta['networks']['count'], 5);
        $this->assertSame($meta['networks']['data']['info'][0], 'name');

        $info = $iptool->find('0.0.0.1');
        $this->assertSame($info['network']['first'], '0.0.0.0');
        $this->assertSame($info['network']['last'], '63.255.255.255');
        $this->assertEmpty($info['data']['info']['name']);

        $info = $iptool->find('64.0.10.0');
        $this->assertSame($info['network']['first'], '64.0.0.0');
        $this->assertSame($info['network']['last'], '64.255.255.255');
        $this->assertSame($info['data']['info']['name'], 'interval 1');

        $info = $iptool->find('83.10.10.1');
        $this->assertSame($info['network']['first'], '65.0.0.0');
        $this->assertSame($info['network']['last'], '127.255.255.255');
        $this->assertEmpty($info['data']['info']['name']);

        $info = $iptool->find('173.255.10.0');
        $this->assertSame($info['network']['first'], '128.0.0.0');
        $this->assertSame($info['network']['last'], '191.255.255.255');
        $this->assertSame($info['data']['info']['name'], 'interval 2');

        $info = $iptool->find('203.255.10.5');
        $this->assertSame($info['network']['first'], '192.0.0.0');
        $this->assertSame($info['network']['last'], '255.255.255.255');
        $this->assertEmpty($info['data']['info']['name']);

        unlink($dbFile);
        $tmpFiles = glob($tmpDir.DIRECTORY_SEPARATOR.'*');
        foreach ($tmpFiles as $tmpFile) {
            if ($tmpFile != $tmpDir.DIRECTORY_SEPARATOR.'.gitkeep') {
                unlink($tmpFile);
            }
        }
    }
}