<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Ini;

/**
 * Tests the IniReader when the `parse_ini_string()` function is disabled.
 */
class IniReaderDisableFunctionTest extends BaseIniReaderTest
{
    public function setUp()
    {
        parent::setUp();

        // Runs the same tests using the alternate implementation
        $property = new \ReflectionProperty($this->reader, 'useNativeFunction');
        $property->setAccessible(true);
        $property->setValue($this->reader, false);
    }
}
