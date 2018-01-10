<?php

namespace Ddrv\Iptool\Wizard;

use Ddrv\Iptool\Wizard;

/**
 * Class RegisterAbstract
 *
 * @property array $source
 * @property int $firstRow
 */
abstract class CsvAbstract
{
    /**
     * @var array
     */
    protected $source;

    /**
     * @var int
     */
    protected $firstRow = 1;

    /**
     * Settings CSV source file.
     *
     * @param string $encoding
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return $this
     */
    public function setCsv($encoding='UTF-8', $delimiter=',', $enclosure='"', $escape='\\')
    {
        if (!is_string($encoding)) {
            throw new \InvalidArgumentException('encoding must be string');
        }
        if (!in_array($encoding, mb_list_encodings())) {
            throw new \InvalidArgumentException('encoding unsupported');
        }
        if (!is_string($delimiter) || mb_strlen($delimiter) != 1) {
            throw new \InvalidArgumentException('delimiter must be string with a length of 1 character');
        }
        if (!is_string($enclosure) || mb_strlen($enclosure) != 1) {
            throw new \InvalidArgumentException('enclosure must be string with a length of 1 character');
        }
        if (!is_string($escape) || mb_strlen($escape) != 1) {
            throw new \InvalidArgumentException('escape must be string with a length of 1 character');
        }
        $this->source['encoding'] = $encoding;
        $this->source['delimiter'] = $delimiter;
        $this->source['enclosure'] = $enclosure;
        $this->source['escape'] = $escape;
        return $this;
    }

    /**
     * Get source file and csv settings.
     *
     * @return array
     */
    public function getCsv()
    {
        return $this->source;
    }

    /**
     * Set first row.
     *
     * @param int $row
     * @return $this
     */
    public function setFirstRow($row)
    {
        if (!is_int($row) || $row < 1) {
            throw new \InvalidArgumentException('row must be positive integer');
        }
        $this->firstRow = $row;
        return $this;
    }

    /**
     * Get first row.
     *
     * @return int
     */
    public function getFirstRow()
    {
        return $this->firstRow;
    }

    /**
     * Check file.
     *
     * @param string $file
     * @throws \InvalidArgumentException
     */
    protected function checkFile($file)
    {
        if (!is_string($file)) {
            throw new \InvalidArgumentException('parameter file must be string');
        }
        $testHandler = @fopen( $file, 'rb');
        if ($testHandler === false) {
            throw new \InvalidArgumentException('can not read the file');
        }
        fclose($testHandler);
        $this->source = array(
            'file' => $file,
            'encoding' => '',
            'delimiter' => '',
            'enclosure' => '',
            'escape' => '',
        );
        $this->setCsv();
    }
}