<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Matomo\Ini\Tests;

/**
 * Tests the IniReader when the `parse_ini_string()` function is disabled.
 */
class IniReaderDisabledFunctionTest extends BaseIniReaderTest
{
    protected function setUp()
    {
        parent::setUp();

        // Runs the same tests using the alternate implementation
        $property = new \ReflectionProperty($this->reader, 'useNativeFunction');
        $property->setAccessible(true);
        $property->setValue($this->reader, false);
    }

    public function test_readComments()
    {
        $descriptions = $this->reader->readComments(__DIR__ . '/resources/test.ini.php');

        $expected = array (
            'database' =>
                array (
                    'host' => '',
                    'username' => '',
                    'password' => '',
                    'charset' => 'Phasellus tincidunt ultrices molestie.
Nunc ultricies augue justo, a faucibus lectus sollicitudin quis.
In nunc massa, congue faucibus tempor ac, vehicula eleifend urna.
',
                ),
            'tests' =>
                array (
                    'test_key' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Vivamus vitae risus sit amet ante placerat vulputate sit amet vel purus.
Vivamus ac cursus mauris. Phasellus congue tempor lacus.
',
                    'test_key2' => '',
                    'test_key3' => '',
                    'testkey4' => '
get ullamcorper nunc molestie. Null
',
                    'testkey5' => '',
                    'testkey6' => 'Vestibulum malesuada non nisl vitae maximus.
',
                    'testkey7' => 'Proin convallis augue sed sapien bibendum, et maximus purus rutrum.
',
                    'test_key8' => '',
                    'test_key9' => '',
                ),
            'log' =>
                array (
                    'log_writers' => 'Fusce maximus bibendum lectus, nec tristique enim malesuada hendrerit.
',
                    'log_level' => '
Quisque lorem justo, sollicitudin at pellentesque interdum, euismod quis nulla.
Sed malesuada dolor in tempus ornare. Etiam lobortis commodo congue.
',
                ),
            'Cache' =>
                array (
                    'backend' => 'available backends are \'file\', \'array\', \'null\', \'redis\', \'chained\'
\'array\' will cache data only during one request
\'null\' will not cache anything at all
\'file\' will cache on the filesystem
\'redis\' will cache on a Redis server. Further configuration in [RedisCache] is needed
\'chained\' will chain multiple cache backends. Further configuration in [ChainedCache] is needed
',
                ),
        );

        $this->assertEquals($expected, $descriptions);
    }
}
