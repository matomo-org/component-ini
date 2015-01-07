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
bool_true_1 = 1
bool_false_1 = 0
bool_true_2 = true
bool_false_2 = false
bool_true_3 = yes
bool_false_3 = no
bool_true_4 = on
bool_false_4 = off
empty =
explicit_null = null
int = 10
float = 10.3
array[] = "string"
array[] = 10.3
array[] = 1
array[] = 0
array[] = true
array[] = false

[Section 2]
foo = "bar"

INI;
        $expected = array(
            'Section 1' => array(
                'foo' => 'bar',
                'bool_true_1' => 1,
                'bool_false_1' => 0,
                'bool_true_2' => true,
                'bool_false_2' => false,
                'bool_true_3' => true,
                'bool_false_3' => false,
                'bool_true_4' => true,
                'bool_false_4' => false,
                'empty' => null,
                'explicit_null' => null,
                'int' => 10,
                'float' => 10.3,
                'array' => array(
                    'string',
                    10.3,
                    1,
                    0,
                    true,
                    false,
                ),
            ),
            'Section 2' => array(
                'foo' => 'bar',
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
}
