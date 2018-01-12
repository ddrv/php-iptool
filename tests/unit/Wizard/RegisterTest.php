<?php

namespace Ddrv\Tests\Iptool\Wizard;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Register;
use Ddrv\Iptool\Wizard\Fields\NumericField;

/**
 * @covers Register
 *
 * @property string $registerCsv
 */
class RegisterTest extends TestCase
{
    /**
     * @var string
     */
    protected $registerCsv = \IPTOOL_TEST_CSV_DIR.DIRECTORY_SEPARATOR.'simple'.DIRECTORY_SEPARATOR.'countries.csv';

    /**
     * Create object with incorrect filename.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage parameter file must be string
     */
    public function testCreateWithIncorrectFilename()
    {
        $register = new Register(-1);
        unset($register);
    }

    /**
     * Create object with nonexistent file.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage can not read the file
     */
    public function testCreateWithNonexistentFile()
    {
        $register = new Register('file_not_exists.php');
        unset($register);
    }

    /**
     * Test setCsv with array encoding.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding must be string
     */
    public function testSetCsvWithArrayEncoding()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv(array());
    }

    /**
     * Test setCsv with unsupported encoding.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding unsupported
     */
    public function testSetCsvWithUnsupportedEncoding()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('unsupportedEncoding');
    }

    /**
     * Test setCsv with array delimiter.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithArrayDelimiter()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', array());
    }

    /**
     * Test setCsv with longer delimiter.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithLongerDelimiter()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', 'word');
    }

    /**
     * Test setCsv with empty string delimiter.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithEmptyStringDelimiter()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', '');
    }

    /**
     * Test setCsv with array closure.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithArrayEnclosure()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', ',', array());
    }

    /**
     * Test setCsv with longer closure.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEnclosure()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', ',','word');
    }

    /**
     * Test setCsv with short closure.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithShortEnclosure()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', ',','');
    }

    /**
     * Test setCsv with array escape.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithArrayEscape()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', ',', '"', array());
    }

    /**
     * Test setCsv with longer escape.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEscape()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', ',','"', 'word');
    }

    /**
     * Test setCsv with short escape.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithShortEscape()
    {
        $register = new Register($this->registerCsv);
        $register->setCsv('UTF-8', ',','"', '');
    }

    /**
     * Test default values of CSV settings.
     */
    public function testDefaultCsvSets()
    {
        $register = new Register($this->registerCsv);
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
        $register = new Register($this->registerCsv);
        $register->setCsv('ASCII', ';','\'', '/');
        $sets = $register->getCsv();
        $this->assertSame('ASCII',$sets['encoding']);
        $this->assertSame(';',$sets['delimiter']);
        $this->assertSame('\'',$sets['enclosure']);
        $this->assertSame('/',$sets['escape']);
    }

    /**
     * Test setFirstRow with string.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithString()
    {
        $register = new Register($this->registerCsv);
        $register->setFirstRow('first');
    }

    /**
     * Test setFirstRow with float.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithFloat()
    {
        $register = new Register($this->registerCsv);
        $register->setFirstRow(6.4);
    }

    /**
     * Test setFirstRow with zero.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithZero()
    {
        $register = new Register($this->registerCsv);
        $register->setFirstRow(0);
    }

    /**
     * Test setFirstRow with negative.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithNegativeInt()
    {
        $register = new Register($this->registerCsv);
        $register->setFirstRow(-1);
    }

    /**
     * Test setFirstRow with correct value.
     */
    public function testCorrectSetFirstRow()
    {
        $register = new Register($this->registerCsv);
        $register->setFirstRow(2);
        $row = $register->getFirstRow();
        $this->assertSame(2, $row);
    }

    /**
     * Test setId with string.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer or 0
     */
    public function testSetIdWithString()
    {
        $register = new Register($this->registerCsv);
        $register->setId('first');
    }

    /**
     * Test setId with float.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer or 0
     */
    public function testSetIdWithFloat()
    {
        $register = new Register($this->registerCsv);
        $register->setId(2.5);
    }

    /**
     * Test setId with negative.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer or 0
     */
    public function testSetIdWithNegativeInt()
    {
        $register = new Register($this->registerCsv);
        $register->setId(-5);
    }

    /**
     * Test setId with correct value.
     */
    public function testCorrectSetId()
    {
        $register = new Register($this->registerCsv);
        $register->setId(1);
        $row = $register->getId();
        $this->assertSame(1, $row);
    }

    /**
     * Test AddField with incorrect name.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect name
     */
    public function testAddFieldWithIncorrectName()
    {
        $int = new NumericField(2, 1, 0, 10);
        $register = new Register($this->registerCsv);
        $register->addField('%$#%', 2, $int);
    }

    /**
     * Test AddField with incorrect column.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddFieldWithIncorrectColumn()
    {
        $int = new NumericField(2, 1, 0, 10);
        $register = new Register($this->registerCsv);
        $register->addField('name', 2.5, $int);
    }

    /**
     * Test AddField with incorrect type.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage type incorrect
     */
    public function testAddFieldWithIncorrectType()
    {
        $register = new Register($this->registerCsv);
        $register->addField('name', 2,  'int');
    }

    /**
     * Test AddField with correct values.
     */
    public function testCorrectAddField()
    {
        $int = new NumericField(2, 1, 0, 10);
        $register = new Register($this->registerCsv);
        $register->addField('name', 2,  $int);
        $array = $register->getFields();
        $this->assertArrayHasKey('name', $array);
        $this->assertSame(2, $array['name']['column']);
        $this->assertTrue(is_a($array['name']['type'], NumericField::class));
        $register->removeField('name');
        $array = $register->getFields();
        $this->assertArrayNotHasKey('name', $array);
    }
}