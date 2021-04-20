<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Matomo\Tests\Ini;

use Matomo\Ini\IniReader;
use Matomo\Ini\IniWriter;
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
                    'string',
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
bool_true = 1
bool_false = 0
int = 10
float = 10.3
array[] = "string"
array[] = 10.3
array[] = 1
array[] = 0

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

    public function test_writeToString_withEmptyConfig()
    {
        $writer = new IniWriter();
        $this->assertEquals('', $writer->writeToString(array()));
    }

    /**
     * @expectedException \Matomo\Ini\IniWritingException
     * @expectedExceptionMessage Section "Section 1" doesn't contain an array of values
     */
    public function test_writeToString_shouldThrowException_withInvalidConfig()
    {
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
