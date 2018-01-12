<?php

namespace Ddrv\Tests\Iptool\Wizard\Types;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Fields\StringField;

/**
 * @covers StringField
 * @property array $correctTransforms;
 */
class StringFieldTest extends TestCase
{

    /**
     * @var array
     */
    protected $correctTransforms = array(
        StringField::TRANSFORM_NONE,
        StringField::TRANSFORM_LOWER,
        StringField::TRANSFORM_UPPER
    );

    /**
     * Create object with incorrect transform.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect transform
     */
    public function testCreateWithIncorrectTransform()
    {
        do {
            $incorrectTransform = rand(-100,100);
        } while (in_array($incorrectTransform, $this->correctTransforms));
        $type = new StringField($incorrectTransform);
        unset($type);
    }

    /**
     * Create object with correct transform.
     */
    public function testCreateWithCorrectTransform()
    {
        foreach ($this->correctTransforms as $correctTransform) {
            $type = new StringField($correctTransform);
            $this->assertSame($correctTransform, $type->getTransform());
            unset($type);
        }
    }

    /**
     * Create object with negative maxLength.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect maxLength
     */
    public function testCreateWithNegativeMaxLength()
    {
        $type = new StringField(StringField::TRANSFORM_NONE,-1);
        unset($type);
    }

    /**
     * Create object with zero maxLength.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect maxLength
     */
    public function testCreateWithZeroMaxLength()
    {
        $type = new StringField(StringField::TRANSFORM_UPPER,0);
        unset($type);
    }

    /**
     * Create object with float maxLength.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect maxLength
     */
    public function testCreateWithFloatMaxLength()
    {
        $type = new StringField(StringField::TRANSFORM_LOWER,.5);
        unset($type);
    }

    /**
     * Create object with string maxLength.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect maxLength
     */
    public function testCreateWithStringMaxLength()
    {
        $type = new StringField(StringField::TRANSFORM_NONE,'max');
        unset($type);
    }

    /**
     * Test getValidValue.
     */
    public function testGetValidValue()
    {
        $type = new StringField();
        $type->setMaxLength(3)
            ->setTransform(StringField::TRANSFORM_NONE);
        $result = $type->getValidValue('SoMe TeXt');
        $this->assertSame('SoM', $result);
        $type->setMaxLength(6);
        $result = $type->getValidValue('SoMe TeXt');
        $this->assertSame('SoMe T', $result);
        $type->setTransform(StringField::TRANSFORM_LOWER);
        $result = $type->getValidValue('SoMe TeXt');
        $this->assertSame('some t', $result);
        $type->setTransform(StringField::TRANSFORM_UPPER);
        $result = $type->getValidValue('SoMe TeXt');
        $this->assertSame('SOME T', $result);
        $transform = $type->getTransform();
        $maxLength = $type->getMaxLength();
        $this->assertSame(StringField::TRANSFORM_UPPER, $transform);
        $this->assertSame(6, $maxLength);
    }
}