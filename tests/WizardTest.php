<?php
namespace Ddrv\Tests\Iptool;

use PHPUnit\Framework\TestCase;
use Ddrv\Iptool\Wizard;

/**
 * @covers Wizard
 */
class WizardTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect tmpDir
     */
    public function testCreateWizardWithIncorrectTmpDir()
    {
        $wizard = new Wizard(array());
        unset($wizard);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage tmpDir is not a directory
     */
    public function testCreateWizardWithNotDirectory()
    {
        $wizard = new Wizard('some symbols');
        unset($wizard);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect author
     */
    public function testSetIncorrectUser()
    {
        $wizard = new Wizard(sys_get_temp_dir());
        $wizard->setAuthor(array());
        unset($wizard);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect license
     */
    public function testSetIncorrectLicense()
    {
        $wizard = new Wizard(sys_get_temp_dir());
        $wizard->setLicense(array());
        unset($wizard);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect time
     */
    public function testSetIncorrectTimeAsArray()
    {
        $wizard = new Wizard(sys_get_temp_dir());
        $wizard->setTime(array());
        unset($wizard);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect time
     */
    public function testSetIncorrectTimeAsString()
    {
        $wizard = new Wizard(sys_get_temp_dir());
        $wizard->setTime('time');
        unset($wizard);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect time
     */
    public function testSetIncorrectTimeAsFloat()
    {
        $wizard = new Wizard(sys_get_temp_dir());
        $wizard->setTime(12.5);
        unset($wizard);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect time
     */
    public function testSetIncorrectTimeAsNegativeInt()
    {
        $wizard = new Wizard(sys_get_temp_dir());
        $wizard->setTime(-12);
        unset($wizard);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect filename
     */
    public function testCompileIncorrectFile()
    {
        $wizard = new Wizard(sys_get_temp_dir());
        $wizard->compile(array());
        unset($wizard);
    }
}