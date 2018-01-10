<?php
namespace Ddrv\Tests\Iptool;

use Ddrv\Iptool\Wizard\Types\StringType;
use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Types\NumericType;

/**
 * @covers NumericType
 * @covers StringType
 */
class WizardTypesTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage precision must be positive integer or 0
     */
    public function testCreateNumericTypeWithIncorrectPrecision()
    {
        $type = new NumericType(array());
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mode incorrect
     */
    public function testCreateNumericTypeWithIncorrectMode()
    {
        $type = new NumericType(2, 7);
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mode incorrect
     */
    public function testCreateNumericTypeWithStringMode()
    {
        $type = new NumericType(2, 'mode');
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage min incorrect
     */
    public function testCreateNumericTypeWithStringMin()
    {
        $type = new NumericType(2, 2, 'min');
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage max incorrect
     */
    public function testCreateNumericTypeWithStringMax()
    {
        $type = new NumericType(2, 2, 1,'max');
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage max can not be less than min
     */
    public function testCreateNumericTypeWithMinGreaterMax()
    {
        $type = new NumericType(2, 2, 100,99.99);
        unset($type);
    }

    /**
     * Correct set params
     */
    public function testCorrectNumericType()
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
     * Test validation
     */
    public function testCorrectValidValue()
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect transform
     */
    public function testCreateStringTypeWithIncorrectTransformBefore()
    {
        $type = new StringType(-1);
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect transform
     */
    public function testCreateStringTypeWithIncorrectTransformAfter()
    {
        $type = new StringType(4);
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect maxLength
     */
    public function testCreateStringTypeWithIncorrectMaxLengthAsNegativeInt()
    {
        $type = new StringType(StringType::TRANSFORM_NONE,-1);
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect maxLength
     */
    public function testCreateStringTypeWithIncorrectMaxLengthAsZero()
    {
        $type = new StringType(StringType::TRANSFORM_UPPER,0);
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect maxLength
     */
    public function testCreateStringTypeWithIncorrectMaxLengthAsFloat()
    {
        $type = new StringType(StringType::TRANSFORM_LOWER,.5);
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect maxLength
     */
    public function testCreateStringTypeWithIncorrectMaxLengthAsString()
    {
        $type = new StringType(StringType::TRANSFORM_NONE,'max');
        unset($type);
    }

    public function testStringType()
    {
        $type = new StringType();
        $type->setMaxLength(3)
            ->setTransform(StringType::TRANSFORM_NONE);
        $result = $type->getValidValue('SoMe TeXt');
        $this->assertSame('SoM', $result);
        $type->setMaxLength(6);
        $result = $type->getValidValue('SoMe TeXt');
        $this->assertSame('SoMe T', $result);
        $type->setTransform(StringType::TRANSFORM_LOWER);
        $result = $type->getValidValue('SoMe TeXt');
        $this->assertSame('some t', $result);
        $type->setTransform(StringType::TRANSFORM_UPPER);
        $result = $type->getValidValue('SoMe TeXt');
        $this->assertSame('SOME T', $result);
        $transform = $type->getTransform();
        $maxLength = $type->getMaxLength();
        $this->assertSame(StringType::TRANSFORM_UPPER, $transform);
        $this->assertSame(6, $maxLength);
    }
}