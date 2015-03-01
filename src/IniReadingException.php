<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Ini;

/**
 * Exception when reading a INI configuration.
 */
class IniReadingException extends \Exception
{
    /**
     * @var string
     */
    private $iniFile;

    /**
     * Constructor
     *
     * @param string $iniFile
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($iniFile, $message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->iniFile = $iniFile;
    }

    /**
     * Returns the INI file that was being read, or `"<string>"` if reading from an INI string.
     *
     * @return string
     */
    public function getIniFile()
    {
        return $this->iniFile;
    }
}
