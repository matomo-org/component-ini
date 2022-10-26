<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Matomo\Ini\Tests;

use Matomo\Ini\IniReader;
use Matomo\Ini\IniReadingException;
use PHPUnit\Framework\TestCase;

abstract class BaseIniReaderTest extends TestCase
{
    /**
     * @var IniReader
     */
    protected $reader;

    protected function setUp(): void
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
empty = ""
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
                'empty' => '',
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
bar = null
array[] = null

INI;
        $expected = array(
            'bar' => null,
            'array' => array(
                null,
            ),
        );
        $this->assertSame($expected, $this->reader->readString($ini));
    }

    public function test_readString_shouldNotDecodeQuotedStrings()
    {
        $ini = <<<INI
test1 = ""
test2 = "null"
test3 = "on"
test4 = "off"
test5 = "true"
test6 = "false"
test7 = "yes"
test8 = "no"
array[] = "true"
array[] = "false"

INI;
        $expected = array(
            'test1' => '',
            'test2' => 'null',
            'test3' => 'on',
            'test4' => 'off',
            'test5' => 'true',
            'test6' => 'false',
            'test7' => 'yes',
            'test8' => 'no',
            'array' => array(
                'true',
                'false',
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

    public function test_readString_shouldCastToIntOnlyIfNoDataIsLost()
    {
        $ini = <<<INI
int = 10
float = 10.3
too_many_dots = 10.3.3
contains_e = 52e666
look_like_hexa = 0xf4c3b00c
look_like_binary = 0b10100111001
with_plus = +10
with_minus = -10
starts_with_zero = 0123
starts_with_zero_2 = +0123

INI;
        $expected = array(
            'int' => 10,
            'float' => 10.3,
            'too_many_dots' => '10.3.3',
            'contains_e' => '52e666',
            'look_like_hexa' => '0xf4c3b00c',
            'look_like_binary' => '0b10100111001',
            'with_plus' => '+10',
            'with_minus' => -10,
            'starts_with_zero' => '0123',
            'starts_with_zero_2' => '+0123',
        );
        $this->assertSame($expected, $this->reader->readString($ini));
    }

    public function test_readFile_shouldThrow_withInvalidFile()
    {
        $this->expectExceptionMessage("The file /foobar doesn't exist or is not readable");
        $this->expectException(IniReadingException::class);
        $this->reader->readFile('/foobar');
    }

    public function test_readBoolKeysError()
    {
        $this->expectException(IniReadingException::class);
        $this->expectExceptionMessage("unexpected BOOL_TRUE");
        $this->reader->setUseNativeFunction(true);
        $this->reader->readFile(__DIR__ . '/resources/BoolKey.ini');
    }

    public function test_readBoolKeys()
    {
        $expected = array(
            'form-edit'         => array(
                'submit' => 'Submit',
                'cancel' => 'Cancel',
            ),
            'form-confirmation' => array(
                'yes' => 'Yes',
                'no'  => 'No',
            ),
        );
        $this->reader->setUseNativeFunction(false);
        $result   = $this->reader->readFile(__DIR__ . '/resources/BoolKey.ini');

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider getSpecialCharsAndEscapingTests
     */
    public function test_readSpecialCharsAndEscaping($in, $out)
    {
        self::assertSame($out, $this->reader->readString($in));
    }

    public function getSpecialCharsAndEscapingTests()
    {
        return [
            ['key = "test \" test"', ['key' => 'test " test']],
            ['key = "test \" \' test"', ['key' => 'test " \' test']],
            ['key = "test \" \\\' test"', ['key' => 'test " \\\' test']],
        ];
    }
}
