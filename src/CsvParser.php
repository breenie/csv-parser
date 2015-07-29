<?php
/**
 * Converts csv data to an array.
 *
 * @author  chris
 * @created 20/06/2013 09:03
 */

namespace Kurl\ToolKit\Csv;

/**
 * Class CsvParser
 *
 * @package Kurl\ToolKit\Csv
 */
class CsvParser
{
    /**
     * The number of bytes to read per line.
     *
     * @var int
     */
    const LINE_BUFFER_SIZE = 1024;

    /**
     * The field delimiter for the file.
     *
     * @var string
     */
    const DELIMITER = ',';

    /**
     * The contents of the file.
     *
     * @var array
     */
    protected $_buffer = array();

    /**
     * The header of the CSV;
     *
     * @var array
     */
    protected $header;

    /**
     * Reads a file and converts the contents into an array.
     *
     * @return array the converted contents of the file
     */
    public function toArray()
    {
        return $this->_buffer;
    }

    /**
     * Gets a single column from the results
     *
     * @param int   $index                 the index of the column required
     * @param mixed $defaultEmptyCellValue the default value for a cell that is empty
     *
     * @return array
     */
    public function getColumn($index, $defaultEmptyCellValue = null)
    {
        $source = $this->toArray();
        $column = array();

        foreach ($source as $value) {
            $column[] = false === array_key_exists($index, $value) ? $defaultEmptyCellValue : $value[$index];
        }

        return $column;
    }

    /**
     * Gets the CSV output.
     *
     * @return string the CSV output
     */
    public function __toString()
    {
        $buffer = array();
        foreach ($this->toArray() as $row) {
            $buffer[] = $this->sputcsv($row);
        }

        return implode('', $buffer);
    }

    /**
     * Adds a new row to the buffer.
     *
     * @param array $row the row to add
     *
     * @return CsvParser the instance
     */
    public function addRow(array $row)
    {
        if (null !== $this->header) {
            $clone = array();

            foreach ($this->header as $index => $key) {
                $clone[$key] = true === array_key_exists($index, $row) ? utf8_encode($row[$index]) : null;
            }
            $row = $clone;
        }

        $this->_buffer[] = $row;

        return $this;
    }

    /**
     * The header row where header names are used for keys.
     *
     * @param array $header
     *
     * @return CsvParser
     */
    public function setHeader(array $header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Formats a row into a csv format, this calls onto the builtin fputcsv.
     *
     * @param array  $row       an array of values to convert into csv format
     * @param string $delimiter the cell delimiter
     * @param string $enclosure the wrappers around cells that need wrapping
     * @param string $eol       the line end delimiter
     *
     * @return string the CSV-ised row
     */
    public function sputcsv($row, $delimiter = ',', $enclosure = '"', $eol = "\n")
    {
        static $fp = false;
        if ($fp === false) {
            $fp = fopen('php://temp', 'r+');
        } else {
            rewind($fp);
        }

        foreach ($row as $index => $cell) {
            $row[$index] = trim(preg_replace('/\s+/', ' ', $cell));
        }

        // @codeCoverageIgnoreStart
        if (fputcsv($fp, $row, $delimiter, $enclosure) === false) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        rewind($fp);
        $csv = fgets($fp);

        // @codeCoverageIgnoreStart
        if ($eol != PHP_EOL) {
            $csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
        }
        // @codeCoverageIgnoreEnd

        return $csv;
    }

    /**
     * Creates a new CSV object from a file.
     *
     * @param string $file   the filename
     * @param bool   $header whether or not to get the header row
     *
     * @return CsvParser a new instance
     */
    public static function fromFile($file, $header = false)
    {
        ini_set('auto_detect_line_endings', true);

        if (true === extension_loaded('mbstring')) {
            mb_internal_encoding("UTF-8");
        }

        $instance = new self();
        if (($handle = fopen($file, "r")) !== false) {
            $hasHeader = $header;
            while (($data = fgetcsv($handle, self::LINE_BUFFER_SIZE, self::DELIMITER)) !== false) {
                // Skip the first row
                if (true === $hasHeader) {
                    $hasHeader = false;
                    $instance->setHeader($data);
                    continue;
                }
                $instance->addRow($data);
            }
            fclose($handle);
        }

        return $instance;
    }
}