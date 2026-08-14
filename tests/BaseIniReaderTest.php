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

    /**
     * A double-quoted value that contains newlines is a single multi-line value: the lines
     * after the opening quote are part of the value, not new sections or keys. The native
     * parser and the fallback implementation must read it back the same way.
     */
    public function test_readString_multiLineDoubleQuotedValue_isReadAsOneValue()
    {
        $ini = <<<INI
[data]
first = "
[section2]"
second = "
key2=value2"

INI;
        $expected = array(
            'data' => array(
                'first' => "\n[section2]",
                'second' => "\nkey2=value2",
            ),
        );

        $result = $this->reader->readString($ini);

        $this->assertSame($expected, $result);
        // the lines inside the value stay inside the value; no extra section/key appears
        $this->assertArrayNotHasKey('section2', $result);
        $this->assertArrayNotHasKey('key2', $result);
    }

    public function test_readString_multiLineQuotedValue_withEscapedQuote()
    {
        $ini = <<<INI
[section]
key = "va\\"lue
[newsection]
c=d
"

INI;
        $expected = array(
            'section' => array(
                'key' => "va\"lue\n[newsection]\nc=d\n",
            ),
        );

        $this->assertSame($expected, $this->reader->readString($ini));
    }

    /**
     * Single-quoted values can also span multiple lines; the fallback parser must read them
     * as one value, consistent with the native parser.
     */
    public function test_readString_singleQuotedMultiLineValue_isReadAsOneValue()
    {
        $ini = <<<INI
[data]
first = 'a
[section2]
key2=value2'

INI;
        $result = $this->reader->readString($ini);

        $this->assertSame(array('data' => array('first' => "a\n[section2]\nkey2=value2")), $result);
        $this->assertArrayNotHasKey('section2', $result);
    }

    /**
     * Files written by an older IniWriter, which escaped quotes but not backslashes, can
     * contain a value ending in a backslash directly before its closing quote. Such a value
     * must still end at that quote, so the following keys are not absorbed into it.
     */
    public function test_readString_valueEndingInBackslash_endsAtClosingQuote()
    {
        $ini = <<<'INI'
[sec]
winpath = "C:\Users\foo\"
endbs = "abc\"
keep = "1"

INI;
        $expected = array(
            'sec' => array(
                'winpath' => 'C:\Users\foo\\',
                'endbs'   => 'abc\\',
                'keep'    => 1,
            ),
        );

        $this->assertSame($expected, $this->reader->readString($ini));
    }

    /**
     * A quote that is followed by more content on the same line is an escaped quote inside
     * the value, so the value continues on the following lines.
     */
    public function test_readString_escapedQuoteFollowedByContent_doesNotEndValue()
    {
        // the escaped quote is followed by a space, so it is part of the value
        $ini = "[sec]\nk = \"abc\\\" \ndef\"\nnext = \"1\"\n";

        $expected = array(
            'sec' => array(
                'k'    => "abc\" \ndef",
                'next' => 1,
            ),
        );

        $this->assertSame($expected, $this->reader->readString($ini));
    }

    /**
     * A multi-line value whose last line ends in a backslash directly before the closing
     * quote still ends there, so the following keys are read normally.
     */
    public function test_readString_multiLineValueEndingInBackslash_endsAtClosingQuote()
    {
        $ini = <<<'INI'
[sec]
k = "line1
line2\"
next = "1"

INI;
        $expected = array(
            'sec' => array(
                'k'    => "line1\nline2\\",
                'next' => 1,
            ),
        );

        $this->assertSame($expected, $this->reader->readString($ini));
    }

    /**
     * Reading a large file that contains an unterminated quote must not take a
     * disproportionate amount of time: the value is scanned once, not from the start again
     * for every line that is appended to it.
     */
    public function test_readString_unterminatedQuoteInLargeFile_isNotSlow()
    {
        $ini = "[s]\nk = \"abc\n" . str_repeat("filler = value\n", 20000);

        $start = microtime(true);
        try {
            $this->reader->readString($ini);
        } catch (IniReadingException $e) {
            // expected, the value is never closed
        }

        $this->assertLessThan(2, microtime(true) - $start, 'parsing took disproportionately long');
    }

    /**
     * Anything after the closing quote of a value (such as an inline comment) is not part of
     * the value.
     */
    public function test_readString_contentAfterClosingQuote_isNotPartOfValue()
    {
        $ini = <<<INI
[s]
k = "abc
def" ; comment
z = 1

INI;
        $expected = array(
            's' => array(
                'k' => "abc\ndef",
                'z' => 1,
            ),
        );

        $this->assertSame($expected, $this->reader->readString($ini));
    }

    /**
     * A tab inside a quoted value is part of the value and must not be turned into a space.
     */
    public function test_readString_tabInsideQuotedValue_isPreserved()
    {
        $ini = "[s]\nk = \"a\tb\nc\td\"\n";

        $this->assertSame(array('s' => array('k' => "a\tb\nc\td")), $this->reader->readString($ini));
    }

    /**
     * A "\r\n" inside a quoted value is one line break, not two.
     */
    public function test_readString_crlfInsideQuotedValue_isKept()
    {
        $ini = "[s]\nk = \"a\r\nb\"\r\nz = 1\r\n";

        $this->assertSame(array('s' => array('k' => "a\r\nb", 'z' => 1)), $this->reader->readString($ini));
    }

    /**
     * The lines of a multi-line value are part of that value, so readComments() must not read
     * them as a section or a key of their own. The keys it reports have to be the same ones
     * readFile() returns.
     */
    public function test_readComments_multiLineValue_matchesReadFile()
    {
        $file = __DIR__ . '/resources/MultiLineComments.ini';

        $expected = array(
            'section' => array(
                'first'  => "description for first\n",
                'second' => "\ndescription for second\n",
            ),
        );

        $comments = $this->reader->readComments($file);

        $this->assertSame($expected, $comments);
        $this->assertArrayNotHasKey('NotASection', $comments);

        // the comments describe exactly the settings the file contains
        $config = $this->reader->readFile($file);
        $this->assertSame(array_keys($config), array_keys($comments));
        $this->assertSame(array_keys($config['section']), array_keys($comments['section']));
    }

    /**
     * Content after a section name makes the line invalid, consistent with the native parser.
     */
    public function test_readString_contentAfterSectionName_throws()
    {
        $this->expectException(IniReadingException::class);
        $this->reader->readString("[sec]\"\nk = 1\n");
    }

    public function test_readString_commentAfterSectionName_isAllowed()
    {
        $expected = array('sec' => array('k' => 1));

        $this->assertSame($expected, $this->reader->readString("[sec] ; a comment\nk = 1\n"));
    }

    /**
     * Plain text after a section name is ignored rather than rejected, the way the native
     * parser reads it.
     *
     * @dataProvider getSectionLinesWithTrailingText
     */
    public function test_readString_textAfterSectionName_isIgnored($sectionLine)
    {
        $expected = array('sec' => array('k' => 1));

        $this->assertSame($expected, $this->reader->readString($sectionLine . "\nk = 1\n"));
    }

    public function getSectionLinesWithTrailingText()
    {
        return array(
            'directly after'  => array('[sec]extra'),
            'separated'       => array('[sec] extra'),
            'with apostrophe' => array("[sec]'"),
        );
    }

    /**
     * An unquoted value containing a double quote that is never closed makes the line
     * invalid.
     */
    public function test_readString_unbalancedQuoteInUnquotedValue_throws()
    {
        $this->expectException(IniReadingException::class);
        $this->reader->readString("[sec]\nk = 1\"\n");
    }

    /**
     * An escaped quote does not close a value, so a following line cannot become a section
     * of its own. Either the lines stay part of the value or the file is rejected.
     *
     * @dataProvider getValuesWithQuotesThatDoNotClose
     */
    public function test_readString_quoteThatDoesNotClose_addsNoSection($ini)
    {
        try {
            $result = $this->reader->readString($ini);
        } catch (IniReadingException $e) {
            // the file is rejected, so nothing is read from it
            $result = array();
        }

        $this->assertArrayNotHasKey('other', $result);
    }

    public function getValuesWithQuotesThatDoNotClose()
    {
        return array(
            'escaped quote' => array("[s]\nk = a\"b\\\"c\"d\"\n[other]\nx = 1\n\"\nz = 2\n"),
            'trailing quote' => array("[s]\nk = 1\"\n[other]\nx = 1\n"),
        );
    }


    /**
     * An unterminated quoted value must be rejected, consistent with the native parser.
     */
    public function test_readString_unterminatedQuote_throws()
    {
        $this->expectException(IniReadingException::class);
        $this->reader->readString("[s]\nk = \"abc\n");
    }

    public function test_readString_unterminatedSingleQuote_throws()
    {
        $this->expectException(IniReadingException::class);
        $this->reader->readString("[s]\nk = 'abc\n");
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
            ['key = "\' test"', ['key' => '\' test']],
            ['key = "test \' "', ['key' => 'test \' ']],
        ];
    }
}
