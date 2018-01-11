<?php

namespace Ddrv\Tests\Iptool\Wizard\Types;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Types\NumericType;

/**
 * @covers NumericType
 *
 * @property array $correctModes
 */
class NumericTypeTest extends TestCase
{

    /**
     * @var array
     */
    protected $correctModes = array(
        \PHP_ROUND_HALF_UP,
        \PHP_ROUND_HALF_DOWN,
        \PHP_ROUND_HALF_EVEN,
        \PHP_ROUND_HALF_ODD,
    );

    /**
     * Create object with array precision.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage precision must be positive integer or 0
     */
    public function testCreateWithArrayPrecision()
    {
        $type = new NumericType(array());
        unset($type);
    }

    /**
     * Create object with float precision.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage precision must be positive integer or 0
     */
    public function testCreateWithFloatPrecision()
    {
        $type = new NumericType(2.5);
        unset($type);
    }

    /**
     * Create object with string precision.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage precision must be positive integer or 0
     */
    public function testCreateWithStringPrecision()
    {
        $type = new NumericType('a');
        unset($type);
    }

    /**
     * Create object with negative precision.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage precision must be positive integer or 0
     */
    public function testCreateWithNegativePrecision()
    {
        $type = new NumericType(-1);
        unset($type);
    }

    /**
     * Create object with incorrect mode.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mode incorrect
     */
    public function testCreateWithIncorrectMode()
    {
        do {
            $incorrectMode = rand(-100,100);
        } while (in_array($incorrectMode,$this->correctModes));
        $type = new NumericType(2, $incorrectMode);
        unset($type);
    }

    /**
     * Create object with string mode.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mode incorrect
     */
    public function testCreateWithStringMode()
    {
        $type = new NumericType(2, 'mode');
        unset($type);
    }

    /**
     * Create object with array mode.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mode incorrect
     */
    public function testCreateWithArrayMode()
    {
        $type = new NumericType(2, array(1,3));
        unset($type);
    }

    /**
     * Create object with string min.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage min incorrect
     */
    public function testCreateWithStringMin()
    {
        $correctMode = $this->correctModes[rand(0, count($this->correctModes)-1)];
        $type = new NumericType(2, $correctMode, 'min');
        unset($type);
    }

    /**
     * Create object with string max.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage max incorrect
     */
    public function testCreateWithStringMax()
    {
        $type = new NumericType(2, 2, 1,'max');
        unset($type);
    }

    /**
     * Create object with min > max.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage max can not be less than min
     */
    public function testCreateWithMinGreaterMax()
    {
        $type = new NumericType(2, 2, 100,99.99);
        unset($type);
    }

    /**
     * Test setters and getters.
     */
    public function testSettersAndGetters()
    {
        $type = new NumericType();
        $type->setPrecision(2)
            ->setMode(\PHP_ROUND_HALF_EVEN)
            ->setMin(5)
            ->setMax(20.05)
        ;
        $this->assertSame(2, $type->getPrecision());
        $this->assertSame(\PHP_ROUND_HALF_EVEN, $type->getMode());
        $this->assertSame(5, $type->getMin());
        $this->assertSame(20.05, $type->getMax());
    }

    /**
     * Test getValidValue.
     */
    public function testGetValidValue()
    {
        $type = new NumericType();
        $type->setPrecision(2)
            ->setMode(\PHP_ROUND_HALF_DOWN)
            ->setMin(5)
            ->setMax(20.05)
        ;
        $this->assertSame(15.33, $type->getValidValue(15.335));
        $type->setMode(\PHP_ROUND_HALF_UP);
        $this->assertSame(15.34, $type->getValidValue(15.335));
        $type->setMode(\PHP_ROUND_HALF_EVEN);
        $this->assertSame(15.34, $type->getValidValue(15.335));
        $type->setMode(\PHP_ROUND_HALF_ODD);
        $this->assertSame(15.33, $type->getValidValue(15.331));
        $this->assertSame(20.05, $type->getValidValue(115.331));
        $type->setPrecision(0);
        $this->assertSame(0, $type->getPrecision());
        $this->assertSame(5, $type->getValidValue(-30));
        $type->setMin();
        $this->assertSame(-30, $type->getValidValue(-30));
        $type->setMax(null);
        $this->assertSame(30, $type->getValidValue(30));
    }
}