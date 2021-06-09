<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Matomo\Ini\Tests;

class IniReaderEnabledFunctionTest extends BaseIniReaderTest
{
    /**
     * @expectedException \Matomo\Ini\IniReadingException
     * @expectedExceptionMessage Syntax error in INI configuration
     */
    public function test_readString_shouldThrowException_ifInvalidIni()
    {
        $this->reader->readString('[ test = foo');
    }
}
