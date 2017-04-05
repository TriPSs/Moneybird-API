<?php

namespace Moneybird;

use Moneybird\Exception\IncompatiblePlatformException;

class CompatibilityChecker {

    /**
     * @var string
     */
    public static $MIN_PHP_VERSION = '5.2.0';

    /**
     * Used cURL functions
     *
     * @var array
     */
    public static $REQUIRED_CURL_FUNCTIONS = [
        'curl_init',
        'curl_setopt',
        'curl_exec',
        'curl_error',
        'curl_errno',
        'curl_close',
        'curl_version',
    ];

    /**
     * @throws IncompatiblePlatformException
     * @return void
     */
    public function checkCompatibility() {
        if (!$this->satisfiesPhpVersion()) {
            throw new IncompatiblePlatformException(
                "The client requires PHP version >= " . self::$MIN_PHP_VERSION . ", you have " . PHP_VERSION . ".",
                IncompatiblePlatformException::INCOMPATIBLE_PHP_VERSION
            );
        }

        if (!$this->satisfiesJsonExtension()) {
            throw new IncompatiblePlatformException(
                "PHP extension json is not enabled. Please make sure to enable 'json' in your PHP configuration.",
                IncompatiblePlatformException::INCOMPATIBLE_JSON_EXTENSION
            );
        }

        if (!$this->satisfiesCurlExtension()) {
            throw new IncompatiblePlatformException(
                "PHP extension cURL is not enabled. Please make sure to enable 'curl' in your PHP configuration.",
                IncompatiblePlatformException::INCOMPATIBLE_CURL_EXTENSION
            );
        }

        if (!$this->satisfiesCurlFunctions()) {
            throw new IncompatiblePlatformException(
                "This client requires the following cURL functions to be available: " . implode(', ', self::$REQUIRED_CURL_FUNCTIONS) . ". " .
                "Please check that none of these functions are disabled in your PHP configuration.",
                IncompatiblePlatformException::INCOMPATIBLE_CURL_FUNCTION
            );
        }
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function satisfiesPhpVersion() {
        return (bool)version_compare(PHP_VERSION, self::$MIN_PHP_VERSION, ">=");
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function satisfiesJsonExtension() {
        // Check by extension_loaded
        if (function_exists('extension_loaded') && extension_loaded('json')) {
            return TRUE;
        } elseif (function_exists('json_encode')) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function satisfiesCurlExtension() {
        // Check by extension_loaded
        if (function_exists('extension_loaded') && extension_loaded('curl')) {
            return TRUE;
        } // Check by calling curl_version()
        elseif (function_exists('curl_version') && curl_version()) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function satisfiesCurlFunctions() {
        foreach (self::$REQUIRED_CURL_FUNCTIONS as $curl_function) {
            if (!function_exists($curl_function)) {
                return FALSE;
            }
        }

        return TRUE;
    }

}