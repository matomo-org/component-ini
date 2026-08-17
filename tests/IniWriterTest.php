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
use Matomo\Ini\IniWriter;
use Matomo\Ini\IniWritingException;
use PHPUnit\Framework\TestCase;

class IniWriterTest extends TestCase
{
    public function test_writeToString()
    {
        $config = array(
            'Section 1' => array(
                'foo' => 'bar',
                'bool_true' => true,
                'bool_false' => false,
                'int' => 10,
                'float' => 10.3,
                'array' => array(
                    'string with !"§$%&&§%/\'(%/(=',
                    10.3,
                    true,
                    false,
                ),
            ),
            'Section 2' => array(
                'foo' => 'bar',
            ),
        );
        $expected = <<<INI
[Section 1]
foo = "bar"
bool_true = true
bool_false = false
int = 10
float = 10.3
array[] = "string with !\"§$%&&§%/'(%/(="
array[] = 10.3
array[] = true
array[] = false

[Section 2]
foo = "bar"


INI;
        $writer = new IniWriter();
        $this->assertEquals($expected, $writer->writeToString($config));
    }

    public function test_writeToString_multiArray()
    {
        $config = include('resources/Array.php');
        $expected = file_get_contents('tests/resources/Array.ini');

        $writer = new IniWriter();
        $this->assertEquals($expected, $writer->writeToString($config));
    }

    public function test_writeToString_doesNotAllowInjection()
    {
        $config = include('resources/Injection.php');
        $expected = file_get_contents('tests/resources/Injection.ini');

        $writer = new IniWriter();
        $actual = $writer->writeToString($config);
        $this->assertEquals($expected, $actual);

        // also test reading the output so we're sure there's no injection here
        $reader = new IniReader();
        $result = $reader->readString($actual);

        $configClean = include('resources/Injection.clean.php');
        $this->assertEquals($configClean, $result);
    }

    /**
     * A value that contains newlines must round-trip to exactly the same value on both the
     * native and the fallback reader: the embedded lines stay part of the value instead of
     * becoming separate sections or keys.
     */
    public function test_writeToString_newlineInValue_roundTrips()
    {
        $config = array(
            'data' => array(
                'first' => "\n[section2]",
                'second' => "\nkey2=value2",
            ),
        );

        $writer = new IniWriter();
        $ini = $writer->writeToString($config);

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);
            $result = $reader->readString($ini);

            $this->assertSame($config, $result, 'useNativeFunction=' . var_export($useNativeFunction, true));
            $this->assertArrayNotHasKey('section2', $result);
            $this->assertArrayNotHasKey('key2', $result);
        }
    }

    /**
     * @dataProvider getValuesWithQuotesBeforeLineBreak
     */
    public function test_writeToString_quotesBeforeLineBreak_roundTripExactly($value)
    {
        $config = array('section' => array('key' => $value, 'other' => 'keep'));

        $writer = new IniWriter();
        $ini = $writer->writeToString($config);

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);
            $result = $reader->readString($ini);

            $message = 'useNativeFunction=' . var_export($useNativeFunction, true);
            $this->assertSame(array('section'), array_keys($result), $message);
            $this->assertSame(array('key', 'other'), array_keys($result['section']), $message);
        }
    }

    public function getValuesWithQuotesBeforeLineBreak()
    {
        return array(
            'two quotes'          => array("\"\"\nkey2=value2"),
            'two quotes, section' => array("\"\"\n[section2]"),
            'three quotes'        => array("\"\"\"\nkey2=value2"),
            'text and quotes'     => array("x\"\"\"\"\n[section2]"),
            'one quote'           => array("a\"\nkey2=value2"),
        );
    }

    /**
     * A value ending in a backslash, followed by a multi-line value, must still round-trip
     * exactly on both parsers: the trailing backslash must not run into the next value.
     */
    public function test_writeToString_valueEndingInBackslash_roundTrips()
    {
        $config = array(
            'data' => array(
                'first' => 'abc\\', // value ending in a single backslash
                'second' => "\n[section2]\nkey2=value2",
            ),
        );

        $writer = new IniWriter();
        $ini = $writer->writeToString($config);

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);
            $result = $reader->readString($ini);

            $this->assertSame($config, $result, 'useNativeFunction=' . var_export($useNativeFunction, true));
            $this->assertArrayNotHasKey('section2', $result);
        }
    }

    /**
     * Backslashes (e.g. Windows paths) must round-trip identically through both parsers.
     */
    public function test_writeToString_roundTripsBackslashes()
    {
        $config = array(
            'paths' => array(
                'windows'  => 'C:\\Users\\foo',
                'trailing' => 'ends\\',
                'double'   => 'a\\\\b',
            ),
        );

        $writer = new IniWriter();
        $ini = $writer->writeToString($config);

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);

            $this->assertSame($config, $reader->readString($ini), 'useNativeFunction=' . var_export($useNativeFunction, true));
        }
    }

    /**
     * Values with characters that are significant to INI syntax must survive a write/read
     * round-trip unchanged and identically on both the native and the fallback parser,
     * without producing spurious sections or keys.
     *
     * @dataProvider getTrickyRoundTripValues
     */
    public function test_writeToString_roundTripsTrickyValues($value)
    {
        $config = array('section' => array('key' => $value));

        $writer = new IniWriter();
        $ini = $writer->writeToString($config);

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);
            $result = $reader->readString($ini);

            $this->assertSame($config, $result, 'useNativeFunction=' . var_export($useNativeFunction, true));
        }
    }

    public function getTrickyRoundTripValues()
    {
        return array(
            'double quote in the middle' => array('p@ss"w0rd'),
            'semicolon in value'         => array('a;b'),
            'lone double quote'          => array('"'),
            'lone backslash'             => array('\\'),
            'multiple quotes'            => array('a"b"c'),
            'embedded newline'           => array("line1\nline2"),
            'brackets look like section' => array('[notasection]'),
            'equals sign in value'       => array('a=b'),
            'newline then key=value'     => array("x\ny=z"),
            'quote and backslash'        => array('a\\"b'),
            'tab in value'               => array("a\tb"),
            'tab in multi-line value'    => array("a\tb\nc\td"),
            'windows line break'         => array("a\r\nb"),
            'empty'                      => array(''),
            'spaces only'                => array('   '),
            'surrounding spaces'         => array('  a  '),
            'two quotes'                 => array('a""b'),
            'three quotes'               => array('a"""b'),
            'only quotes'                => array('""'),
            'two backslashes'            => array('a\\\\b'),
            'three backslashes'          => array('a\\\\\\'),
            'ends with two backslashes'  => array('a\\\\'),
            'only backslashes'           => array('\\\\'),
            'newline then quote'         => array("a\n\"b"),
            'looks like a comment'       => array('; not a comment'),
            'starts with a semicolon'    => array(';x'),
            'looks like a section'       => array('[General]'),
            'section on its own line'    => array("a\n[General]\nb"),
            'several line breaks'        => array("a\n\n\nb"),
            'equals sign and quotes'     => array('a="b"'),
            'looks like a boolean'       => array('true'),
            'looks like null'            => array('null'),
            'percent signs'              => array('%s%d'),
            'multibyte'                  => array('héllo→世界'),
            'long'                       => array(str_repeat('x', 5000)),
        );
    }

    /**
     * The value written for a quote directly before a line break loses that quote, but both
     * implementations still read the same value.
     */
    public function test_writeToString_quoteBeforeLineBreak_isLostButReadsTheSame()
    {
        $writer = new IniWriter();
        $ini = $writer->writeToString(array('s' => array('k' => "a\"\nb")));

        $expected = array('s' => array('k' => "a\nb"));

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);

            $this->assertSame($expected, $reader->readString($ini), 'useNativeFunction=' . var_export($useNativeFunction, true));
        }
    }

    /**
     * Characters that are valid in an option name are kept when writing.
     *
     * @dataProvider getValidOptionNames
     */
    public function test_writeToString_keepsValidOptionNames($option)
    {
        $config = array('s' => array($option => 'v'));

        $writer = new IniWriter();
        $ini = $writer->writeToString($config);

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);

            $this->assertSame($config, $reader->readString($ini), 'useNativeFunction=' . var_export($useNativeFunction, true));
        }
    }

    public function getValidOptionNames()
    {
        return array(
            'dot'   => array('db.host'),
            'colon' => array('my:key'),
            'space' => array('a b'),
        );
    }

    public function test_writeToString_shouldThrowException_whenOptionNameCannotBeWritten()
    {
        $this->expectException(IniWritingException::class);
        $this->expectExceptionMessage('cannot be written');

        $writer = new IniWriter();
        $writer->writeToString(array('s' => array('===' => 'v')));
    }

    /**
     * Key names are encoded as well, so a key containing line breaks cannot turn into a
     * section or an additional key when the file is read back.
     */
    public function test_writeToString_encodesKeyNames()
    {
        $config = array(
            'sec' => array(
                "a\nkey2" => 'v',
                'keep' => '1',
            ),
        );

        $writer = new IniWriter();
        $ini = $writer->writeToString($config);

        $expected = array('sec' => array('akey2' => 'v', 'keep' => 1));

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);

            $this->assertSame($expected, $reader->readString($ini), 'useNativeFunction=' . var_export($useNativeFunction, true));
        }
    }

    /**
     * A key name cannot start a section of its own, whichever characters it contains.
     *
     * @dataProvider getKeyNamesThatLookLikeSections
     */
    public function test_writeToString_keyNameDoesNotStartASection($key)
    {
        $writer = new IniWriter();
        $ini = $writer->writeToString(array('sec' => array($key => 'v', 'keep' => '1')));

        foreach (array(true, false) as $useNativeFunction) {
            $reader = new IniReader();
            $reader->setUseNativeFunction($useNativeFunction);

            try {
                $result = $reader->readString($ini);
            } catch (IniReadingException $e) {
                // the file is rejected, so no section is read from it
                $result = array();
            }

            $this->assertSame(array_diff(array_keys($result), array('sec')), array(), 'useNativeFunction=' . var_export($useNativeFunction, true));
        }
    }

    public function getKeyNamesThatLookLikeSections()
    {
        return array(
            'line break and section' => array("a\n[section2]\nkey2"),
            'section name'           => array('[section2]'),
            'closing bracket first'  => array(']x'),
        );
    }

    /**
     * A key ending in "[]" denotes an array and has to keep its brackets.
     */
    public function test_writeToString_keepsArrayBracketsInKeyName()
    {
        $writer = new IniWriter();
        $ini = $writer->writeToString(array('sec' => array('d[]' => 'e')));

        $this->assertSame("[sec]\nd[] = \"e\"\n\n", $ini);

        $reader = new IniReader();
        $reader->setUseNativeFunction(true);
        $this->assertSame(array('sec' => array('d' => array('e'))), $reader->readString($ini));
    }

    public function test_writeToString_withEmptyConfig()
    {
        $writer = new IniWriter();
        $this->assertEquals('', $writer->writeToString(array()));
    }

    public function test_writeToString_shouldThrowException_withInvalidConfig()
    {
        $this->expectException(IniWritingException::class);
        $this->expectExceptionMessage("Section \"Section 1\" doesn't contain an array of values");
        $writer = new IniWriter();
        $writer->writeToString(array('Section 1' => 123));
    }

    public function test_writeToString_shouldAddHeader()
    {
        $header = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n";
        $config = array(
            'Section 1' => array(
                'foo' => 'bar',
            ),
        );
        $expected = <<<INI
; <?php exit; ?> DO NOT REMOVE THIS LINE
[Section 1]
foo = "bar"


INI;
        $writer = new IniWriter();
        $this->assertEquals($expected, $writer->writeToString($config, $header));
    }
}
