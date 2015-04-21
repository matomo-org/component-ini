<?php

namespace Piwik\Tests\Ini\PerformanceTest;

use Athletic\AthleticEvent;
use Piwik\Ini\IniReader;

class IniReadingEvent extends AthleticEvent
{
    /**
     * @var IniReader
     */
    private $reader;

    private $ini;

    protected function classSetUp()
    {
        if (extension_loaded('xdebug')) {
            throw new \Exception('Xdebug should be disabled to run the performance tests');
        }
    }

    protected function setUp()
    {
        $this->reader = new IniReader();
        $this->createIniString();
    }

    /**
     * @iterations 10000
     */
    public function native()
    {
        parse_ini_string($this->ini, true);
    }

    /**
     * @iterations 10000
     */
    public function native_raw()
    {
        parse_ini_string($this->ini, true, INI_SCANNER_RAW);
    }

    /**
     * @iterations 10000
     */
    public function library()
    {
        $this->reader->readString($this->ini);
    }

    private function createIniString()
    {
        $this->ini = <<<INI
[Array]
array[] = "string1"
array[] = "string2"
array[] = "string3"
array[] = "string4"
array[] = "string5"
array[] = "string6"
array[] = "string7"
array[] = "string8"
array[] = "string9"
array[] = "string10"
array[] = 10.3
array[] = 1
array[] = 0
array[] = null

[Types]
empty =
null_value = null
string = "bar"
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

[Strings]
string1 = "string1"
string2 = "string2"
string3 = "string3"
string4 = "string4"
string5 = "string5"
string6 = "string6"
string7 = "string7"
string8 = "string8"
string9 = "string9"
string10 = "string10"
string11 = "string11"
string12 = "string12"
string13 = "string13"
string14 = "string14"
string15 = "string15"
string16 = "string16"
string17 = "string17"
string18 = "string18"
string19 = "string19"
string20 = "string20"

INI;
    }
}
