<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Ini;

use Piwik\Ini\IniReader;

abstract class BaseIniReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IniReader
     */
    protected $reader;

    public function setUp()
    {
        parent::setUp();

        $this->reader = new IniReader();
    }

    public function test_readString()
    {
        $ini = <<<INI
[Section 1]
foo = "bar"
number_1 = 1
number_0 = 0
int = 10
float = 10.3
array[] = "string"
array[] = 10.3
array[] = 1
array[] = 0

[Section 2]
foo = "bar"

INI;
        $expected = array(
            'Section 1' => array(
                'foo' => 'bar',
                'number_1' => 1,
                'number_0' => 0,
                'int' => 10,
                'float' => 10.3,
                'array' => array(
                    'string',
                    10.3,
                    1,
                    0,
                ),
            ),
            'Section 2' => array(
                'foo' => 'bar',
            ),
        );
        $this->assertSame($expected, $this->reader->readString($ini));
    }

    public function test_readString_shouldReadBooleans()
    {
        $ini = <<<INI
bool_true_1 = on
bool_false_1 = off
bool_true_2 = true
bool_false_2 = false
bool_true_3 = yes
bool_false_3 = no
array[] = true
array[] = false

INI;
        $expected = array(
                'bool_true_1' => true,
                'bool_false_1' => false,
                'bool_true_2' => true,
                'bool_false_2' => false,
                'bool_true_3' => true,
                'bool_false_3' => false,
                'array' => array(
                    true,
                    false,
                ),
        );
        $this->assertSame($expected, $this->reader->readString($ini));
    }

    public function test_readString_shouldReadNulls()
    {
        $ini = <<<INI
foo =
bar = null
array[] =
array[] = null

INI;
        $expected = array(
            'foo' => null,
            'bar' => null,
            'array' => array(
                null,
                null,
            ),
        );
        $this->assertSame($expected, $this->reader->readString($ini));
    }

    /**
     * parse_ini_string() on PHP 5.3.3 fails with strings missing an empty line at the end (at least on Travis).
     *
     * Tests that IniReader handles them.
     *
     * @see http://3v4l.org/jD1Lh
     */
    public function test_readString_withoutEmptyEndLine()
    {
        $ini = <<<INI
[Section 1]
foo = "bar"
INI;
        $expected = array(
            'Section 1' => array(
                'foo' => 'bar',
            ),
        );
        $this->assertSame($expected, $this->reader->readString($ini));
    }

    public function test_readString_withEmptyString()
    {
        $this->assertSame(array(), $this->reader->readString(''));
    }

    public function test_readString_shouldIgnoreComments()
    {
        $expected = array(
            'Section 1' => array(
                'foo' => 'bar',
            ),
        );
        $ini = <<<INI
; <?php exit; ?> DO NOT REMOVE THIS LINE
[Section 1]
foo = "bar"

INI;
        $this->assertSame($expected, $this->reader->readString($ini));
    }

    public function test_readString_shouldReadIniWithoutSections()
    {
        $expected = array(
            'foo' => 'bar',
        );
        $ini = <<<INI
foo = "bar"

INI;
        $this->assertSame($expected, $this->reader->readString($ini));
    }

    /**
     * Test a case that fails with basic parse_ini_string($ini, true, INI_SCANNER_RAW)
     * under PHP <= 5.3.14 or <= 5.4.4
     *
     * @see http://3v4l.org/m24cT
     */
    public function test_readString_shouldReadSpecialCharacters()
    {
        $expected = array(
            'foo' => "&amp;6^ geagea'''&quot;;;&amp;",
        );
        $ini = <<<INI
foo = "&amp;6^ geagea'''&quot;;;&amp;"

INI;
        $this->assertSame($expected, $this->reader->readString($ini));
    }
}
