<?php
namespace Ddrv\Tests\Iptool;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Types\Decimal;

/**
 * @covers Decimal
 */
class WizardTypesTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage precision must be positive integer or 0
     */
    public function testCreateDecimalTypeWithIncorrectPrecision()
    {
        $type = new Decimal(array());
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mode incorrect
     */
    public function testCreateDecimalTypeWithIncorrectMode()
    {
        $type = new Decimal(2, 7);
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mode incorrect
     */
    public function testCreateDecimalTypeWithStringMode()
    {
        $type = new Decimal(2, 'mode');
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage min incorrect
     */
    public function testCreateDecimalTypeWithStringMin()
    {
        $type = new Decimal(2, 2, 'min');
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage max incorrect
     */
    public function testCreateDecimalTypeWithStringMax()
    {
        $type = new Decimal(2, 2, 1,'max');
        unset($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage max can not be less than min
     */
    public function testCreateDecimalTypeWithMinGreaterMax()
    {
        $type = new Decimal(2, 2, 100,99.99);
        unset($type);
    }

    /**
     * Correct set params
     */
    public function testCorrectSetId()
    {
        $type = new Decimal();
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
        $type = new Decimal();
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