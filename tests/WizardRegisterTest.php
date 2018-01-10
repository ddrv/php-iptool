<?php
namespace Ddrv\Tests\Iptool;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Register;
use Ddrv\Iptool\Wizard\Types\Decimal;

/**
 * @covers Register
 */
class WizardRegisterTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage parameter file must be string
     */
    public function testCreateWithIncorrectFilename()
    {
        $register = new Register(-1);
        unset($register);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage can not read the file
     */
    public function testCreateWithNonexistentFile()
    {
        $register = new Register('file_not_exists.php');
        unset($register);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding must be string
     */
    public function testSetCsvWithNonStringEncoding()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding unsupported
     */
    public function testSetCsvWithUnsupportedEncoding()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('unsupportedEncoding');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringDelimiter()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithLongerDelimiter()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', 'word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithShortDelimiter()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', '');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringEnclosure()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEnclosure()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',','word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithShortEnclosure()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',','');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithNonStringEscape()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',', '"', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEscape()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',','"', 'word');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithShortEscape()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setCsv('UTF-8', ',','"', '');
    }

    /**
     * Test default values of CSV settings.
     */
    public function testDefaultCsvSets()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
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
        $register = new Register(__DIR__.'/csv/simple/info.csv');
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
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow('first');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithFloat()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow(6.4);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithZero()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithNegativeInt()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow(-1);
    }

    /**
     * Correct set first row
     */
    public function testCorrectSetFirstRow()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setFirstRow(2);
        $row = $register->getFirstRow();
        $this->assertSame(2, $row);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testSetIdWithString()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setId('first');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testSetIdWithFloat()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setId(2.5);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testSetIdWithZero()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setId(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testSetIdWithNegativeInt()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setId(-5);
    }

    /**
     * Correct set first row
     */
    public function testCorrectSetId()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->setId(1);
        $row = $register->getId();
        $this->assertSame(1, $row);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect name
     */
    public function testAddFieldWithIncorrectName()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->addField('%$#%', 2, 'int');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddFieldWithIncorrectColumn()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->addField('name', 2.5, 'int');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage type incorrect
     */
    public function testAddFieldWithIncorrectType()
    {
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->addField('name', 2,  'int');
    }

    /**
     * Correct addField.
     */
    public function testCorrectAddField()
    {
        $int = new Decimal(2, 1, 0, 10);
        $register = new Register(__DIR__.'/csv/simple/info.csv');
        $register->addField('name', 2,  $int);
        $array = $register->getFields();
        $this->assertArrayHasKey('name', $array);
        $this->assertSame(2, $array['name']['column']);
        $this->assertTrue(is_a($array['name']['type'], Decimal::class));
        $register->removeField('name');
        $array = $register->getFields();
        $this->assertArrayNotHasKey('name', $array);
    }
}