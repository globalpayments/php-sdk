<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpApiConnector\FileProcessing;

use GlobalPayments\Api\Entities\Exceptions\ApiException;

class FileProcessingClient
{
    private string $uploadUrl;

    public function __construct($url)
    {
        $this->uploadUrl = $url;
    }

    public function uploadFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found!');
        } elseif ((int)(filesize($filePath)) / 1024 / 1024 > 100) {
            throw new \Exception('Max file size 100MB exceeded!');
        }

        $header = [
            "Content-Type: text/csv"
        ];
        $verb = 'PUT';

        return $this->sendRequest($verb, $filePath, $header);
    }

    private function sendRequest($verb, $file_name_with_full_path, $headers = [])
    {
        try {
            $request = curl_init();
            curl_setopt_array($request, [
                CURLOPT_URL => $this->uploadUrl,
                CURLOPT_PUT => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_INFILE => fopen($file_name_with_full_path, 'w'),
                CURLOPT_INFILESIZE => filesize($file_name_with_full_path),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $verb,
                CURLOPT_TIMEOUT => 30,
            ]);
            curl_exec($request);
            $curlInfo = curl_getinfo($request);
            $message = (curl_errno($request) === CURLE_OK);
            curl_close($request);

            if ($curlInfo['http_code'] != 200) {
                throw new ApiException(sprintf('Upload request failed with response code: %s', $curlInfo['http_code']));
            }
        } catch (\Exception $exc) {
            throw new ApiException($exc);
        }

        return $message;
    }

}