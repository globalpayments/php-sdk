<?php

namespace GlobalPayments\Api\Entities;

class MerchantDataCollection
{
    /**
     * @var array<MerchantKVP>
     */
    private $collection;

    /**
     * @return string
     */
    public function get($key)
    {
        foreach ($this->collection as $kvp) {
            if ($kvp->getKey() == $key && $kvp->isVisible()) {
                return $kvp->getValue();
            }
        }
        return null;
    }
    
    /**
     * @return array<string>
     */
    public function getKeys()
    {
        $keys = [];
        foreach ($this->collection as $kvp) {
            if ($kvp->isVisible()) {
                array_push($keys, $kvp->getKey());
            }
        }
        return $keys;
    }

    /**
     * @return int
     */
    public function count()
    {
        $count = 0;
        foreach ($this->collection as $kvp) {
            if ($kvp->isVisible()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @return int
     */
    private function indexOf($key)
    {
        for ($i=0; $i<count($this->collection); $i++) {
            if ($this->collection[$i]->getKey() == $key) {
                return $i;
            }
        }
        return -1;
    }

    /**
     * @return array<MerchantKVP>
     */
    public function getHiddenValues()
    {
        $list = [];
        foreach ($this->collection as $kvp) {
            if (!$kvp->isVisible()) {
                array_push($list, $kvp);
            }
        }
        return $list;
    }

    public function __construct()
    {
        $this->collection = [];
    }

    /**
     * @return void
     */
    public function add($key, $value, $visible = true)
    {
        if ($this->hasKey($key)) {
            if ($visible) {
                throw new ApiException(sprintf('Key %s already exists in the collection.', $key));
            } else {
                unset($this->collection[$this->indexOf($key)]);
                $this->collection = array_values($this->collection);
            }
        }

        $kvp = new MerchantKVP();
        $kvp->setKey($key);
        $kvp->setValue($value);
        $kvp->setVisible($visible);

        array_push($this->collection, $kvp);
    }

    /**
     * @return string
     */
    public function getValue($key, $converter = null)
    {
        foreach ($this->collection as $kvp) {
            if ($kvp->getKey() == $key) {
                if ($converter != null) {
                    return $converter($kvp->getValue());
                } else {
                    return $kvp->getValue();
                }
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasKey($key)
    {
        return $this->getValue($key) != null;
    }

    /**
     * @return void
     */
    public function mergeHidden($oldCollection)
    {
        foreach ($oldCollection->getHiddenValues() as $kvp) {
            if (!$this->hasKey($kvp->getKey())) {
                array_push($this->collection, $kvp);
            }
        }
    }

    /**
     * @return MerchantDataCollection
     */
    public static function parse($kvpString, $decoder = null)
    {
        $collection = new MerchantDataCollection();

        $decryptedKvp = (string)base64_decode($kvpString);
        if ($decoder != null) {
            $decryptedKvp = $decoder($decryptedKvp);
        }

        $merchantData = explode('|', $decryptedKvp);
        foreach ($merchantData as $kvp) {
            $data = explode(':', $kvp);
            $collection->add($data[0], $data[1], (bool)$data[2]);
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function toString($encoder = null)
    {
        $sb = '';

        foreach ($this->collection as $kvp) {
            $sb .= sprintf('%s:%s:%s|', $kvp->getKey(), $kvp->getValue(), $kvp->isVisible());
        }

        $pos = strrpos($sb, '|');
        $sb = substr($sb, 0, $pos) . substr($sb, $pos+1);

        try {
            $formatted = (string)$sb;
            if ($encoder != null) {
                $formatted = $encoder($formatted);
            }

            return (string)base64_encode($formatted);
        } catch (UnsupportedEncodingException $e) {
            return null;
        }
    }
}
