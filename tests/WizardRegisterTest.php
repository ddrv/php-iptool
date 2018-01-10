<?php
namespace Ddrv\Tests\Iptool;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Iptool;
use Ddrv\Iptool\Wizard;

/**
 * @covers Wizard
 */
class WizardRegisterTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage parameter file must be string
     */
    public function testCreateWithIncorrectFilename()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage can not read the file
     */
    public function testCreateWithNonexistentFile()
    {
        $register = new \Ddrv\Iptool\Wizard\Register('file_not_exists.php');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding must be string
     */
    public function testSetCsvWithNonStringEncoding()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding unsupported
     */
    public function testSetCsvWithUnsupportedEncoding()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('unsupportedEncoding');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringDelimiter()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithLongerDelimiter()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', 'word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithShortDelimiter()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', '');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringEnclosure()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEnclosure()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',','word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithShortEnclosure()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',','');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringEscape()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',', '"', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEscape()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',','"', 'word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithShortEscape()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',','"', '');
    }

    /**
     * Test default values of CSV settings.
     */
    public function testDefaultCsvSets()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $sets = $register->getCsv();
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
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('ASCII', ';','\'', '/');
        $sets = $register->getCsv();
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
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow('first');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithFloat()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow(6.4);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithZero()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow(0);
    }

    /**
     * Correct set first row
     */
    public function testCorrectSetFirstRow()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow(2);
        $row = $register->getFirstRow();
        $this->assertSame(2, $row);
    }

    /**
     * @-expectedException \InvalidArgumentException
     * @-expectedExceptionMessage column must be positive integer

    public function testSetIdWithString()
    {
        $register = new \Ddrv\Iptool\Wizard\Register(__DIR__.'/csv/simple/info.csv');
        $register->setId('first');
    }
     */
}