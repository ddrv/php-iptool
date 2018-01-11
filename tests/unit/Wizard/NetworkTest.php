<?php

namespace Ddrv\Tests\Iptool\Wizard;

use Ddrv\Iptool\Wizard\Types\AddressType;
use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Network;
use Ddrv\Iptool\Wizard\Register;
use Ddrv\Iptool\Wizard\Types\NumericType;

/**
 * @covers Network
 *
 * @property string $networksCsv
 * @property string $registerCsv
 * @property AddressType $ipType
 */
class NetworkTest extends TestCase
{

    /**
     * @var string
     */
    protected $networksCsv = \IPTOOL_TEST_CSV_DIR.DIRECTORY_SEPARATOR.'simple'.DIRECTORY_SEPARATOR.'networks.csv';

    /**
     * @var string
     */
    protected $registerCsv = \IPTOOL_TEST_CSV_DIR.DIRECTORY_SEPARATOR.'simple'.DIRECTORY_SEPARATOR.'countries.csv';

    /**
     * @var AddressType
     */
    protected $ipType;

    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->ipType = new AddressType();
    }

    /**
     * Create object with icorrect filename.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage parameter file must be string
     */
    public function testCreateWithIncorrectFilename()
    {
        $network = new Network(-1, $this->ipType, 1, 2);
        unset($network);
    }

    /**
     * Create object with nonexistsen file.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage can not read the file
     */
    public function testCreateWithNonexistentFile()
    {
        $network = new Network('file_not_exists.php', $this->ipType, 1, 2);
        unset($network);
    }

    /**
     * Create object with incorrect ipType.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect ipType
     */
    public function testCreateWithIncorrectIpType()
    {
        $network = new Network($this->networksCsv, 'ip', 1, 2);
        unset($network);
    }

    /**
     * Create object with negative firstIpColumn.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage firstIpColumn must be positive integer
     */
    public function testCreateWithNegativeFirstIpColumn()
    {
        $network = new Network($this->networksCsv, $this->ipType, -1, 2);
        unset($network);
    }

    /**
     * Create object with zero firstIpColumn.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage firstIpColumn must be positive integer
     */
    public function testCreateWithZeroFirstIpColumn()
    {
        $network = new Network($this->networksCsv, $this->ipType, 0, 2);
        unset($network);
    }

    /**
     * Create object with float firstIpColumn.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage firstIpColumn must be positive integer
     */
    public function testCreateWithFloatFirstIpColumn()
    {
        $network = new Network($this->networksCsv, $this->ipType, 0.5, 2);
        unset($network);
    }

    /**
     * Create object with string firstIpColumn.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage firstIpColumn must be positive integer
     */
    public function testCreateWithStringFirstIpColumn()
    {
        $network = new Network($this->networksCsv, $this->ipType, 'a', 2);
        unset($network);
    }

    /**
     * Create object with negative lastIpColumn.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage lastIpColumn must be positive integer
     */
    public function testCreateWithNegativeLastIpColumn()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, -1);
        unset($network);
    }

    /**
     * Create object with zero lastIpColumn.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage lastIpColumn must be positive integer
     */
    public function testCreateWithZeroLastIpColumn()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 0);
        unset($network);
    }

    /**
     * Create object with float lastIpColumn.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage lastIpColumn must be positive integer
     */
    public function testCreateWithFloatLastIpColumn()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2.5);
        unset($network);
    }

    /**
     * Create object with string lastIpColumn.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage lastIpColumn must be positive integer
     */
    public function testCreateWithStringLastIpColumn()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 'b');
        unset($network);
    }

    /**
     * Test setCsv with array encoding.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding must be string
     */
    public function testSetCsvWithArrayEncoding()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv(array());
    }

    /**
     * Test setCsv with unsupported encoding.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage encoding unsupported
     */
    public function testSetCsvWithUnsupportedEncoding()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('unsupportedEncoding');
    }

    /**
     * Test setCsv with array delimiter.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithArrayDelimiter()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', array());
    }

    /**
     * Test setCsv with longer delimiter.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithLongerDelimiter()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', 'word');
    }

    /**
     * Test setCsv with short delimiter.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage delimiter must be string with a length of 1 character
     */
    public function testSetCsvWithShortDelimiter()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', '');
    }

    /**
     * Test setCsv with array enclosure.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithArrayEnclosure()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', ',', array());
    }

    /**
     * Test setCsv with longer enclosure.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEnclosure()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', ',','word');
    }

    /**
     * Test setCsv with short enclosure.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage enclosure must be string with a length of 1 character
     */
    public function testSetCsvWithShortEnclosure()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', ',','');
    }

    /**
     * Test setCsv with array escape.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithArrayEscape()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', ',', '"', array());
    }

    /**
     * Test setCsv with longer escape.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithLongerEscape()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', ',','"', 'word');
    }

    /**
     * Test setCsv with short escape.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage escape must be string with a length of 1 character
     */
    public function testSetCsvWithShortEscape()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('UTF-8', ',','"', '');
    }

    /**
     * Test default values of CSV settings.
     */
    public function testDefaultCsvSets()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
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
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setCsv('ASCII', ';','\'', '/');
        $sets = $network->getCsv();
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
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setFirstRow('first');
    }

    /**
     * Test setFirstRow with float.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithFloat()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setFirstRow(6.4);
    }

    /**
     * Test setFirstRow with zero.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithZero()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setFirstRow(0);
    }

    /**
     * Test setFirstRow with negative.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage row must be positive integer
     */
    public function testSetFirstRowWithNegativeInt()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setFirstRow(-1);
    }

    /**
     * Test setFirstRow with correct value.
     */
    public function testCorrectSetFirstRow()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->setFirstRow(2);
        $row = $network->getFirstRow();
        $this->assertSame(2, $row);
    }

    /**
     * Test addRegister with incorrect name.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect name
     */
    public function testAddRegisterWithIncorrectName()
    {
        $register = new Register($this->registerCsv);
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->addRegister('%$#%', 2, $register);
    }

    /**
     * Test addRegister with string column.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddRegisterWithStringColumn()
    {
        $register = new Register($this->registerCsv);
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->addRegister('name', 'two', $register);
    }

    /**
     * Test addRegister with float column.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddRegisterWithFloatColumn()
    {
        $register = new Register($this->registerCsv);
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->addRegister('name', 3.5, $register);
    }

    /**
     * Test addRegister with zero column.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddRegisterWithZeroColumn()
    {
        $register = new Register($this->registerCsv);
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->addRegister('name', 0, $register);
    }

    /**
     * Test addRegister with negative column.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage column must be positive integer
     */
    public function testAddRegisterWithNegativeColumn()
    {
        $register = new Register($this->registerCsv);
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->addRegister('name', -42, $register);
    }

    /**
     * Test addRegister with incorrect register.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect register
     */
    public function testAddRegisterWithIncorrectRegister()
    {
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->addRegister('name', 2, 'register');
    }

    /**
     * Test addRegister with empty fields.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage fields of register can not be empty
     */
    public function testAddRegisterWithEmptyFields()
    {
        $register = new Register($this->registerCsv);
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->addRegister('name', 2, $register);
    }

    /**
     * Correct add register.
     */
    public function testCorrectAddRegister()
    {
        $register = new Register($this->registerCsv);
        $register->addField('field1', 1, new NumericType());
        $network = new Network($this->networksCsv, $this->ipType, 1, 2);
        $network->addRegister('register1', 2, $register);
        $data = $network->getRegisters();
        $this->assertArrayHasKey('register1', $data);
        $network->removeRegister('register1');
        $data = $network->getRegisters();
        $this->assertArrayNotHasKey('register1', $data);
    }
}