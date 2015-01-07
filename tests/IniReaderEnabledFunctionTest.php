<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Ini;

class IniReaderEnabledFunctionTest extends BaseIniReaderTest
{
    /**
     * @expectedException \Piwik\Ini\IniReadingException
     * @expectedExceptionMessage Syntax error in INI configuration
     */
    public function test_readString_shouldThrowException_ifInvalidIni()
    {
        $this->reader->readString('[ test = foo');
    }
}
