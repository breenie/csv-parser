<?php
/**
 * - CsvParserTest.php
 *
 * @author  chris
 * @created 31/08/2014 11:48
 */
namespace Kurl\Tests\ToolKit\Csv;

use Kurl\ToolKit\Csv\CsvParser;

class CsvParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests to string.
     *
     * @param bool $header
     * @param string $expected
     *
     * @dataProvider toStringProvider
     */
    public function testToString($header, $expected)
    {
        $parser = CsvParser::fromFile(__DIR__ . '/Fixture/test.csv', $header);

        $this->assertEquals($expected, trim($parser->__toString()));
    }

    /**
     * Tests getting columns.
     *
     * @dataProvider getColumnProvider
     */
    public function testGetColumn($header, $column, $expected)
    {
        $parser = CsvParser::fromFile(__DIR__ . '/Fixture/test.csv', $header);

        $this->assertEquals($expected, $parser->getColumn($column));
    }

    /**
     * Tests CSV parsing.
     *
     * @param bool   $header
     * @param string $expected
     *
     * @dataProvider fromFileProvider
     */
    public function testFromFile($header, $expected)
    {
        $this->assertEquals($expected, CsvParser::fromFile(__DIR__ . '/Fixture/test.csv', $header)->toArray());
    }

    /**
     * @return array
     */
    public function fromFileProvider()
    {
        return array(
            array(
                false,
                array(array('column_1', 'column_2', 'column_3'), array('Candy Man', 'Candy Man', 'Candy Man'))
            ),
            array(true, array(array('column_1' => 'Candy Man', 'column_2' => 'Candy Man', 'column_3' => 'Candy Man'))),
        );
    }

    /**
     * @return array
     */
    public function getColumnProvider()
    {
        return array(
            array(false, 1, array('column_2', 'Candy Man')),
            array(true, 'column_2', array('Candy Man')),
        );
    }

    public function toStringProvider()
    {
        return array(
            array(true, '"Candy Man","Candy Man","Candy Man"'),
            array(false,
                <<<EOT
column_1,column_2,column_3
"Candy Man","Candy Man","Candy Man"
EOT
            )
        );
    }
}
