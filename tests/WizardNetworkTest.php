<?php
namespace Ddrv\Tests\Iptool;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Network;
use Ddrv\Iptool\Wizard\Register;
use Ddrv\Iptool\Wizard;
use Ddrv\Iptool\Wizard\Types\Decimal;

/**
 * @covers Wizard
 */
class WizardNetworkTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage parameter file must be string
     */
    public function testCreateWithIncorrectFilename()
    {
        $network = new Network(-1, 'ip', 1, 2);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage can not read the file
     */
    public function testCreateWithNonexistentFile()
    {
        $network = new Network('file_not_exists.php', 'ip', 1, 2);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage firstIpColumn must be positive integer
     */
    public function testCreateWithFirstIpColumnAsNegativeInt()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', -1, 2);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage firstIpColumn must be positive integer
     */
    public function testCreateWithFirstIpColumnAsZero()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 0, 2);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage firstIpColumn must be positive integer
     */
    public function testCreateWithFirstIpColumnAsFloat()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 0.5, 2);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage firstIpColumn must be positive integer
     */
    public function testCreateWithFirstIpColumnAsString()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 'a', 2);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage lastIpColumn must be positive integer
     */
    public function testCreateWithLastIpColumnAsNegativeInt()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, -1);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage lastIpColumn must be positive integer
     */
    public function testCreateWithLastIpColumnAsZero()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 0);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage lastIpColumn must be positive integer
     */
    public function testCreateWithLastIpColumnAsFloat()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2.5);
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage lastIpColumn must be positive integer
     */
    public function testCreateWithLastIpColumnAsString()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 'b');
        unset($network);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding must be string
     */
    public function testSetCsvWithNonStringEncoding()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding unsupported
     */
    public function testSetCsvWithUnsupportedEncoding()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('unsupportedEncoding');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringDelimiter()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithLongerDelimiter()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', 'word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithShortDelimiter()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', '');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringEnclosure()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', ',', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEnclosure()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', ',','word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithShortEnclosure()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', ',','');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringEscape()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', ',', '"', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEscape()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', ',','"', 'word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithShortEscape()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('UTF-8', ',','"', '');
    }

    /**
     * Test default values of CSV settings.
     */
    public function testDefaultCsvSets()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $sets = $network->getCsv();
        $this->assertSame('UTF-8',$sets['encoding']);
        $this->assertSame(',',$sets['delimiter']);
        $this->assertSame('"',$sets['enclosure']);
        $this->assertSame('\\',$sets['escape']);
    }

    /**
     * Test setCsv method.
     */
    public function testSetCsv()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setCsv('ASCII', ';','\'', '/');
        $sets = $network->getCsv();
        $this->assertSame('ASCII',$sets['encoding']);
        $this->assertSame(';',$sets['delimiter']);
        $this->assertSame('\'',$sets['enclosure']);
        $this->assertSame('/',$sets['escape']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithString()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setFirstRow('first');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithFloat()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setFirstRow(6.4);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithZero()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setFirstRow(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithNegativeInt()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setFirstRow(-1);
    }

    /**
     * Correct set first row
     */
    public function testCorrectSetFirstRow()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->setFirstRow(2);
        $row = $network->getFirstRow();
        $this->assertSame(2, $row);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect name
     */
    public function testAddRegisterWithIncorrectName()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->addRegister('%$#%', 2, $register);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddRegisterWithIncorrectColumnAsString()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->addRegister('name', 'two', $register);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddRegisterWithIncorrectColumnAsFloat()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->addRegister('name', 3.5, $register);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddRegisterWithIncorrectColumnAsZero()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->addRegister('name', 0, $register);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddRegisterWithIncorrectColumnAsNegativeInt()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->addRegister('name', -42, $register);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect register
     */
    public function testAddRegisterWithIncorrectRegister()
    {
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->addRegister('name', 2, 'register');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage fields of register can not be empty
     */
    public function testAddRegisterWithEmptyFields()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->addRegister('name', 2, $register);
    }

    /**
     * Correct add register
     */
    public function testCorrectAddRegister()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->addField('field1', 1, new Decimal());
        $network = new Network(__DIR__.'/csv/simple/networks.csv', 'ip', 1, 2);
        $network->addRegister('register1', 2, $register);
        $data = $network->getRegisters();
        $this->assertArrayHasKey('register1', $data);
        $network->removeRegister('register1');
        $data = $network->getRegisters();
        $this->assertArrayNotHasKey('register1', $data);
    }
}