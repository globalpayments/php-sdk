<?php

namespace GlobalPayments\Api\Utils;

class EmvData
{
    /** @var array<TlvData> */
    private $tlvData = [];
    /** @var array<TlvData> */
    private $removedTags = [];
    /** @var boolean */
    private $standInStatus;
    /** @var string */
    private $standInStatusReason;

    public function getTag($tagName)
    {
        if (isset($this->tlvData[$tagName])) {
            return $this->tlvData[$tagName];
        }

        return null;
    }

    public function getAcceptedTagData()
    {
        if (empty($this->tlvData)) {
            return null;
        }
        $rvalue = "";
        /** @var TlvData $tag */
        foreach ($this->tlvData as $tag) {
            $rvalue .= $tag->getFullValue();
        }

        return $rvalue;
    }

    public function getAcceptedTags()
    {
        return $this->tlvData;
    }

    public function getRemovedTags()
    {
        return $this->removedTags;
    }

    public function getStandInStatus()
    {
        return $this->standInStatus;
    }

    public function getStandInStatusReason()
    {
        return $this->standInStatusReason;
    }

    public function setStandInStatus($value, $reason)
    {
        $this->standInStatus = $value;
        $this->standInStatusReason = $reason;
    }

    public function getCardSequenceNumber()
    {
        if (isset($this->tlvData["5F34"])) {
            return $this->tlvData["5F34"];
        }

        return null;
    }

    public function getSendBuffer()
    {
        return StringUtils::bytesFromHex($this->getAcceptedTagData());
    }

    /**
     * @param string $tag
     * @param string $length
     * @param string $value
     * @param string $description
     */
    public function addTag($tag, $length, $value, $description = null)
    {
        $this->addTagData(new TlvData($tag, $length, $value, $description));
    }

    /**
     * @param TlvData $tagData
     */
    public function addTagData($tagData)
    {
        $this->tlvData[$tagData->getTag()] = $tagData;
    }

    public function addRemovedTag($tag, $length, $value, $description = null)
    {
        $this->addRemovedTagData(new TlvData($tag, $length, $value, $description));
    }

    /**
     * @param TlvData $tagData
     */
    public function addRemovedTagData($tagData)
    {
        $this->removedTags[$tagData->getTag()] = $tagData;
    }

    public function isContactlessMsd()
    {
        $entryMode = $this->getEntryMode();

        return !is_null($entryMode) ? $entryMode == "91" : false;
    }

    public function getEntryMode()
    {
        $posEntryMode = $this->getTag("9F39");
        if (!is_null($posEntryMode)) {
            return $posEntryMode->getValue();
        }

        return null;
    }
}