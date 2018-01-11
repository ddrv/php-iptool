<?php

namespace Ddrv\Tests\Iptool\Wizard\Types;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard\Types\AddressType;

/**
 * @covers AddressType
 *
 * @property array $correctFormats
 */
class AddressTypeTest extends TestCase
{
    /**
     * @var array
     */
    protected $correctFormats = array(
        AddressType::FORMAT_IP,
        AddressType::FORMAT_LONG,
        AddressType::FORMAT_INETNUM
    );

    /**
     * Test create object with incorrect format.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect format
     */
    public function testCreateWithIncorrectFormatBefore()
    {
        do {
            $incorrectFormat = rand(-100,100);
        } while (in_array($incorrectFormat,$this->correctFormats));
        $type = new AddressType($incorrectFormat);
        unset($type);
    }

    /**
     * Create object with correct format.
     */
    public function testCreateWithCorrectFormat()
    {
        foreach ($this->correctFormats as $correctFormat) {
            $type = new AddressType($correctFormat);
            $this->assertSame($correctFormat, $type->getFormat());
            unset($type);
        }
    }

    /**
     * Test getValidValue for type IP.
     */
    public function testGetValidValueForIp()
    {
        $ip = rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.';
        $long = ip2long($ip);
        $type = new AddressType(AddressType::FORMAT_IP);
        $addr = $type->getValidValue($ip);
        $this->assertSame($long,$addr[0]);
        $this->assertArrayNotHasKey(1, $addr);
    }

    /**
     * Test getValidValue for type Long.
     */
    public function testGetValidValueForLong()
    {
        $ip = rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.';
        $long = ip2long($ip);
        $type = new AddressType(AddressType::FORMAT_LONG);
        $addr = $type->getValidValue($long);
        $this->assertSame($long,$addr[0]);
        $this->assertArrayNotHasKey(1, $addr);
    }

    /**
     * Test getValidValue for type Inetnum.
     */
    public function testGetValidValueForInetnum()
    {
        $type = new AddressType(AddressType::FORMAT_INETNUM);
        $addr = $type->getValidValue('127.0.0.0 - 127.255.255.255');
        $this->assertSame(ip2long('127.0.0.0'),$addr[0]);
        $this->assertSame(ip2long('127.255.255.255'),$addr[1]);
        $addr = $type->getValidValue('83.8.0.0/13');
        $this->assertSame(ip2long('83.8.0.0'),$addr[0]);
        $this->assertSame(ip2long('83.15.255.255'),$addr[1]);
    }
}