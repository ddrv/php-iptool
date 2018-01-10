<?php

namespace Ddrv\Iptool;

use Ddrv\Iptool\Wizard\Network;

/**
 * Class Wizard
 *
 * @const int FORMAT_VERSION
 * @property string $tmpDir
 * @property string $author
 * @property string $license
 * @property int    $time
 * @property Network[]  $networks
 */
class Wizard
{
    /**
     * @const int
     */
    const FORMAT_VERSION = 1;

    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $license;

    /**
     * @var integer
     */
    protected $time;

    /**
     * @var array
     */
    protected $networks;

    /**
     * Wizard constructor.
     *
     * @param string $tmpDir
     * @throws \InvalidArgumentException
     */
    public function __construct($tmpDir)
    {
        if (!is_string($tmpDir)) {
            throw new \InvalidArgumentException('incorrect tmpDir');
        }
        if (!is_dir($tmpDir)) {
            throw new \InvalidArgumentException('tmpDir is not a directory');
        }
        if (!is_writable($tmpDir)) {
            throw new \InvalidArgumentException('tmpDir is not a writable');
        }
        return $this;
    }

    /**
     * Set author.
     *
     * @param string $author
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAuthor($author)
    {
        if (!is_string($author)) {
            throw new \InvalidArgumentException('incorrect author');
        }
        if (mb_strlen($author) > 64) $author = mb_substr($author,0,64);
        $this->author = $author;
        return $this;
    }

    /**
     * Set license.
     *
     * @param string $license
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setLicense($license)
    {
        if (!is_string($license)) {
            throw new \InvalidArgumentException('incorrect license');
        }
        $this->license = $license;
        return $this;
    }

    /**
     * Set time.
     *
     * @param integer $time
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTime($time)
    {
        if (!is_int($time) || $time <0) {
            throw new \InvalidArgumentException('incorrect time');
        }
        $this->time = $time;
        return $this;
    }

    /**
     * Add network.
     *
     * @param Network $network
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addNetwork($network)
    {
        if (!($network instanceof Network)) {
            throw new \InvalidArgumentException('incorrect network');
        }
        $this->networks[] = $network;
        return $this;
    }

    /**
     * Compile database
     *
     * @param string $filename
     * @return $this
     */
    public function compile($filename)
    {
        if (!is_string($filename)) {
            throw new \InvalidArgumentException('incorrect filename');
        }
        if (file_exists($filename) && !is_writable($filename)) {
            throw new \InvalidArgumentException('file not writable');
        }
        if (!file_exists($filename) && !is_writable(dirname($filename))) {
            throw new \InvalidArgumentException('directory not writable');
        }
        if (empty($this->time)) $this->time = time();
        return $this;
    }
}