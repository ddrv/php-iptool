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
     * Create object with incorrect tmpDir.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage incorrect tmpDir
     */
    public function testCreateWizardWithIncorrectTmpDir()
    {
        $wizard = new Wizard(array());
        unset($wizard);
    }

    /**
     * Create object with not directory tmpDir.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage tmpDir is not a directory
     */
    public function testCreateWizardWithNotDirectory()
    {
        $wizard = new Wizard('some symbols');
        unset($wizard);
    }

    /**
     * Test setAuthor with array.
     *
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
     * Test setLicense with array.
     *
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
     * Test setTime with array.
     *
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
     * Test setTime with string.
     *
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
     * Test setAuthor with float.
     *
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
     * Test setAuthor with negative.
     *
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
     * Test compile with array filename.
     *
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