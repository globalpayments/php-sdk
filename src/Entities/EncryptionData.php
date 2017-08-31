<?php

namespace GlobalPayments\Api\Entities;

/**
 * Details how encrypted track data was encrypted by the device
 * in order for the gateway to decrypt the data.
 */
class EncryptionData
{
    /**
     * The encryption version.
     *
     * @var string
     */
    public $version;

    /**
     * The track number that is encrypted and supplied in
     * the request.
     *
     * @var string
     */
    public $trackNumber;

    /**
     * The key serial number (KSN) used at the point of sale;
     * where applicable.
     *
     * @var string
     */
    public $ksn;

    /**
     * The key transmission block (KTB) used at the point of sale;
     * where applicable.
     *
     * @var string
     */
    public $ktb;

    /**
     * Convenience method for creating version `01` encryption data.
     *
     * @return EncryptionData
     */
    public static function version1()
    {
        $data = new EncryptionData();
        $data->version = '01';
        return $data;
    }

    /**
     * Convenience method for creating version `02` encryption data.
     *
     * @param string $ktb
     * @param string $trackNumber
     *
     * @return EncryptionData
     */
    public static function version2($ktb, $trackNumber = null)
    {
        $data = new EncryptionData();
        $data->version = '02';
        $data->trackNumber = $trackNumber;
        $data->ktb = $ktb;
        return $data;
    }
}
