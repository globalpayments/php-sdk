<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpEcom;

use GlobalPayments\Api\Builders\TransactionBuilder;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;

abstract class GpEcomRequestBuilder
{
    protected function buildSupplementaryData(TransactionBuilder $builder, \DOMDocument $xml, \DOMElement $request)
    {
        $supplementaryData = $xml->createElement("supplementarydata");
        foreach ($builder->supplementaryData as $key => $items) {
            $item = $xml->createElement("item");
            if (strtolower($key) === 'visadirect') {
                $item->setAttribute('type', 'visaDirect');
                $this->appendVisaDirectAftFields($xml, $item, $items);
            } else {
                $item->setAttribute('type', $key);
                if (!is_array($items)) {
                    $items = explode(' ', $items);
                }
                for ($i = 1; $i <= count($items); $i++) {
                    $item->appendChild($xml->createElement('field' . sprintf("%02d", $i), $items[$i - 1]));
                }
            }
            $supplementaryData->appendChild($item);
        }
        $request->appendChild($supplementaryData);
    }

    /**
     * @param \DOMDocument $xml
     * @param \DOMElement $item
     * @param mixed $items
     * @return void
     * @throws BuilderException
     */
    private function appendVisaDirectAftFields(\DOMDocument $xml, \DOMElement $item, $items): void
    {
        if (!is_array($items) || count($items) !== 8) {
            throw new BuilderException(
                'VisaDirect supplementary data must contain 8 fields in order: Sender Reference Number, Sender Account Number, Sender Name, Sender Address, Sender City, Sender Country, Account Number Type, Recipient Account Number.'
            );
        }

        $fieldMap = [
            'field01',
            'field02',
            'field03',
            'field04',
            'field05',
            'field08',
            'field22',
            'field23',
        ];

        foreach (array_values($items) as $index => $value) {
            $item->appendChild($xml->createElement($fieldMap[$index], (string)$value));
        }
    }
}
