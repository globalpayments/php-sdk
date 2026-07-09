<?php

namespace GlobalPayments\Api\Utils;

/**
 * Utility for retrieving the SDK release version from the project metadata.
 * When running under test conditions (environment variable {@code SDK_TESTING}=true),
 * an empty string is returned to avoid file-system lookups.
 */
class ReleaseVersionUtils
{
    /**
     * Returns the SDK release version extracted from {@code metadata.xml}.
     * If the {@code SDK_TESTING} environment variable is set to {@code true}, an empty string is returned.
     *
     * @return string the release version, or an empty string when testing or if the version cannot be read
     */
    public static function getReleaseVersion(): string
    {
        if (strtolower((string) getenv('SDK_TESTING')) === 'true') {
            return '';
        }

        $filename = dirname(__DIR__, 2) . '/metadata.xml';
        if (!file_exists($filename)) {
            return '';
        }

        $xml = simplexml_load_string(file_get_contents($filename));
        if ($xml === false) {
            return '';
        }

        return !empty($xml->releaseNumber) ? (string) $xml->releaseNumber : '';
    }
}
