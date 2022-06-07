<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpEcom;

abstract class GpEcomRequestBuilder
{
    protected function buildSupplementaryData($builder, $xml, $request)
    {
        $supplementaryData = $xml->createElement("supplementarydata");
        foreach ($builder->supplementaryData as $key => $items) {
            $item = $xml->createElement("item");
            $item->setAttribute('type', $key);
            if (!is_array($items)) {
                $items = explode(' ', $items);
            }
            for ($i = 1; $i <= count($items); $i++) {
                $item->appendChild($xml->createElement('field' .  sprintf("%02d", $i), $items[$i - 1]));
            }
            $supplementaryData->appendChild($item);
        }
        $request->appendChild($supplementaryData);

    }
}