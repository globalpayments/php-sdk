<?php

namespace GlobalPayments\Api\Utils;

class EmvData
{
    /** @var array<TlvData> */
    private array $tlvData = [];
    /** @var array<TlvData> */
    private array $removedTags = [];
    /** @var boolean */
    private ?bool $standInStatus = null;
    /** @var string */
    private ?string $standInStatusReason = null;

    public function getTag(?string $tagName): ?TlvData
    {
        if (isset($this->tlvData[$tagName])) {
            return $this->tlvData[$tagName];
        }

        return null;
    }

    public function getAcceptedTagData(): ?string
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

    public function getAcceptedTags(): array
    {
        return $this->tlvData;
    }

    public function getRemovedTags(): array
    {
        return $this->removedTags;
    }

    public function getStandInStatus(): ?bool
    {
        return $this->standInStatus;
    }

    public function getStandInStatusReason(): ?string
    {
        return $this->standInStatusReason;
    }

    public function setStandInStatus(?bool $value, ?string $reason): void
    {
        $this->standInStatus = $value;
        $this->standInStatusReason = $reason;
    }

    public function getCardSequenceNumber(): ?TlvData
    {
        if (isset($this->tlvData["5F34"])) {
            return $this->tlvData["5F34"];
        }

        return null;
    }

    public function getSendBuffer(): ?string
    {
        return StringUtils::bytesFromHex($this->getAcceptedTagData());
    }

    /**
     * @param string $tag
     * @param string $length
     * @param string $value
     * @param string $description
     */
    public function addTag(?string $tag, ?string $length, ?string $value, ?string $description = null): void
    {
        $this->addTagData(new TlvData($tag, $length, $value, $description));
    }

    /**
     * @param TlvData $tagData
     */
    public function addTagData(TlvData $tagData): void
    {
        $this->tlvData[$tagData->getTag()] = $tagData;
    }

    public function addRemovedTag(?string $tag, ?string $length, ?string $value, ?string $description = null): void
    {
        $this->addRemovedTagData(new TlvData($tag, $length, $value, $description));
    }

    /**
     * @param TlvData $tagData
     */
    public function addRemovedTagData(TlvData $tagData): void
    {
        $this->removedTags[$tagData->getTag()] = $tagData;
    }

    public function isContactlessMsd(): bool
    {
        $entryMode = $this->getEntryMode();

        return !is_null($entryMode) ? $entryMode == "91" : false;
    }

    public function getEntryMode(): ?string
    {
        $posEntryMode = $this->getTag("9F39");
        if (!is_null($posEntryMode)) {
            return $posEntryMode->getValue();
        }

        return null;
    }
}