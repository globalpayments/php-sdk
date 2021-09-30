<?php

namespace GlobalPayments\Api\Utils;

class EmvUtils
{
    public static $blackList = [
        "57" => "Track 2 Equivalent Data",
        "5A" => "Application Primary Account Number (PAN)",
        "99" => "Transaction PIN Data",
        "5F20" => "Cardholder Name",
        "5F24" => "Application Expiration Date",
        "9F0B" => "Cardholder Name Extended",
        "9F1F" => "Track 1 Discretionary Data",
        "9F20" => "Track 2 Discretionary Data",
    ];

    public static $knownTags = [
        "4F" => "Application Dedicated File (ADF) Name",
        "50" => "Application Label",
        "6F" => "File Control Information (FCI) Template",
        "71" => "Issuer Script Template 1",
        "72" => "Issuer Script Template 2",
        "82" => "Application Interchange Profile",
        "84" => "Dedicated File (DF) Name",
        "86" => "Issuer Script Command",
        "87" => "Application Priority Indicator",
        "88" => "Short File Identifier (SFI)",
        "8A" => "Authorization Response Code (ARC)",
        "8C" => "Card Rick Management Data Object List 1 (CDOL1)",
        "8D" => "Card Rick Management Data Object List 2 (CDOL2)",
        "8E" => "Cardholder Verification Method (CVM) List",
        "8F" => "Certification Authority Public Key Index",
        "90" => "Issuer Public Key Certificate",
        "91" => "Issuer Authentication Data",
        "92" => "Issuer Public Key Remainder",
        "93" => "Signed Static Application Data",
        "94" => "Application File Locator (AFL)",
        "95" => "Terminal Verification Results (TVR)",
        "97" => "Transaction Certification Data Object List (TDOL)",
        "9A" => "Transaction Date",
        "9B" => "Transaction Status Indicator",
        "9C" => "Transaction Type",
        "9D" => "Directory Definition File (DDF) Name",
        "5F25" => "Application Effective Date",
        "5F28" => "Issuer Country Code",
        "5F2A" => "Transaction Currency Code",
        "5F2D" => "Language Preference",
        "5F30" => "Service Code",
        "5F34" => "Application Primary Account Number (PAN) Sequence Number",
        "5F36" => "Transaction Currency Exponent",
        "9F01" => "Unknown",
        "9F02" => "Amount, Authorized",
        "9F03" => "Amount, Other",
        "9F05" => "Application Discretionary Data",
        "9F06" => "Application Identifier (AID)",
        "9F07" => "Application Usage Control",
        "9F08" => "Application Version Number",
        "9F09" => "Application Version Number",
        "9F0D" => "Issuer Action Code (IAC) - Default",
        "9F0E" => "Issuer Action Code (IAC) - Denial",
        "9F0F" => "Issuer Action Code (IAC) - Online",
        "9F10" => "Issuer Application Data",
        "9F11" => "Issuer Code Table Index",
        "9F12" => "Application Preferred Name",
        "9F13" => "Last Online Application Transaction Counter (ATC) Register",
        "9F14" => "Lower Consecutive Offline Limit",
        "9F16" => "Unknown",
        "9F17" => "Personal Identification Number (PIN) Try Counter",
        "9F1A" => "Terminal Country Code",
        "9F1B" => "Terminal Floor Limit",
        "9F1C" => "Unknown",
        "9F1D" => "Terminal Risk Management Data",
        "9F1E" => "Interface Device (IFD) Serial Number",
        "9F21" => "Transaction Time",
        "9F22" => "Certification Authority Public Key Modulus",
        "9F23" => "Upper Consecutive Offline Limit",
        "9F26" => "Application Cryptogram",
        "9F27" => "Cryptogram Information Data",
        "9F2D" => "Integrated Circuit Card (ICC) PIN Encipherment Public Key Certificate",
        "9F2E" => "Integrated Circuit Card (ICC) PIN Encipherment Public Key Exponent",
        "9F2F" => "Integrated Circuit Card (ICC) PIN Encipherment Public Key Remainder",
        "9F32" => "Issuer Public Key Exponent",
        "9F33" => "Terminal Capabilities",
        "9F34" => "Cardholder Verification Method (CVM) Results",
        "9F35" => "Terminal Type",
        "9F36" => "Application Transaction Counter (ATC)",
        "9F37" => "Unpredictable Number",
        "9F38" => "Processing Options Data Object List (PDOL)",
        "9F39" => "Point-Of-Service (POS) Entry Mode",
        "9F3B" => "Application Reference Currency",
        "9F3C" => "Transaction Reference Currency Code",
        "9F3D" => "Transaction Reference Currency Conversion",
        "9F40" => "Additional Terminal Capabilities",
        "9F41" => "Transaction Sequence Counter",
        "9F42" => "Application Currency Code",
        "9F43" => "Application Reference Currency Exponent",
        "9F44" => "Application Currency Exponent",
        "9F46" => "Integrated Circuit Card (ICC) Public Key Certificate",
        "9F47" => "Integrated Circuit Card (ICC) Public Key Exponent",
        "9F48" => "Integrated Circuit Card (ICC) Public Key Remainder",
        "9F49" => "Dynamic Data Authentication Data Object List (DDOL)",
        "9F4A" => "Signed Data Authentication Tag List",
        "9F4B" => "Signed Dynamic Application Data",
        "9F4C" => "ICC Dynamic Number",
        "9F4E" => "Unknown",
        "9F5B" => "Issuer Script Results",
        "9F6E" => "Form Factor Indicator/Third Party Data",
        "9F7C" => "Customer Exclusive Data",
        "FFC6" => "Terminal Action Code (TAC) Default",
        "FFC7" => "Terminal Action Code (TAC) Denial",
        "FFC8" => "Terminal Action Code (TAC) Online",
            // WEX EMV
        "42" => "Issuer Identification Number (IIN or BIN)",
        "61" => "Directory entry Template",
        "70" => "Record Template",
        "73" => "Directory Discretionary Template",
        "9F4D" => "Log Entry",
        "9F4F" => "Transaction Log Format",
        "9F52" => "Card Verification Results (CVR)",
        "9F7E" => "Issuer Life Cycle Data",
        "A5" => "FCI Proprietary Template",
        "BF0C" => "FCI Issuer Discretionary Data",
        "BF20" => "PRO 00",
        "BF27" => "PRO 07",
        "BF2E" => "PRO 14",
        "C1" => "Application Control",
        "C4" => "Default Contact Profile31",
        "CA" => "Previous Transaction History",
        "CB" => "CRM Country Code",
        "CD" => "CRM Currency Code",
        "D3" => "PDOL Related data Length",
        "D8" => "CAFL",
        "DF01" => "Proprietary Data Element n°1",
        "DF02" => "Proprietary Data Element n°2",
        "DF03" => "Proprietary Data Element n°3",
        "DF04" => "Proprietary Data Element n°4",
        "DF05" => "Proprietary Data Element n°5",
        "DF06" => "Proprietary Data Element n°6",
        "DF07" => "Proprietary Data Element n°7",
        "DF08" => "Proprietary Data Element n°8",
        "DF10" => "Profile Selection Table",
        "DF11" => "Currency Conversion Code 1",
        "DF12" => "Currency Conversion Code 2",
        "DF13" => "COTN counter",
        "DF14" => "COTA accumulator",
        "DF15" => "CIAC – Denial",
        "DF16" => "CIAC – Default",
        "DF17" => "CIAC – Online",
        "DF18" => "LCOTA limit ",
        "DF19" => "UCOTA limit",
        "DF1A" => "MTAL limit ",
        "DF1B" => "LCOL limit",
        "DF1C" => "Upper Consecutive Offline Limit (UCOL)",
        "DF1D" => "IADOL",
        "DF1E" => "Derivation key Index",
        "DF30" => "Fuel Card usage bitmap [Prompting], ATC Limit",
        "DF31" => "Encrypted PIN cryptography failure limit",
        "DF32" => "Purchase Restrictions (WEX refers to this as Chip Offline Purchase Restriction), Failed MAC limit",
        "DF33" => "Lifetime MAC Limit",
        "DF34" => "Chip Offline Purchase Restrictions Amount for Fuel*, Session MAC Limit",
        "DF35" => "Chip Offline Purchase Restrictions Amount for non-Fuel*",
        "DF36" => "Relationship Codes*",
        "DF37" => "3rd Party Reference Data Generation 2*",
        "DF38" => "Loyalty ID*",
        "DF39" => "Purchase Device Sequence Number (with the suffix)* ",
        "DF40" => "DDOL Related Data Length",
        "DF41" => "CCDOL2 Related Data Length",
        "DF4D" => "Transaction Log Setting parameter31"
    ];

    public static $dataTypes = [
        "82" => "b",
        "8E" => "b",
        "95" => "b",
        "9B" => "b",
        "9F07" => "b",
        "9F33" => "b",
        "9F40" => "b",
        "9F5B" => "b"
    ];

    /**
     * @param $tagData
     * @param false $verbose
     * @return EmvData|null
     */
    public static function parseTagData($tagData, $verbose = false)
    {
        if (empty($tagData)) {
            return null;
        }
        $tagData = strtoupper($tagData);
        $rvalue = new EmvData();

        for ($i = 0; $i < strlen($tagData);) {
            try {
                $tagName = substr($tagData, $i, 2);
                $i += 2;
                if ((base_convert($tagName, 16, 10) & 0x1F) == 0x1F) {
                    $tagName .= substr($tagData, $i, 2);
                    $i += 2;
                }
                $lengthStr = substr($tagData,$i, 2);
                $i += 2;
                $length = base_convert($lengthStr,16,10);
                if ($length > 127) {
                    $bytesLength = $length - 128;
                    $lengthStr = substr($tagData, $bytesLength * 2);
                    $i += $bytesLength * 2;
                    $length = base_convert($lengthStr, 16,10);
                }
                $length *= 2;
                $value = substr($tagData, $i, $length);
                $i += $length;
                if (!array_key_exists($tagName, self::$blackList)) {
                    $approvedTag = new TlvData($tagName, $lengthStr, $value, self::$knownTags[$tagName]);
                    if ($tagName == "5F28" && $value !== "840") {
                        $rvalue->setStandInStatus(false, "Card is not domestically issued");
                    } elseif ($tagName == "95") {
                        $valueBuffer = StringUtils::bytesFromHex($value);
                        $maskBuffer = StringUtils::bytesFromHex("FC50FC2000");
                        for ($idx = 0; $idx < strlen($valueBuffer); $idx++) {
                            if (($valueBuffer[$idx] & $maskBuffer[$idx]) != 0x00) {
                                $rvalue->setStandInStatus(false, sprintf("Invalid TVR status in byte %s of tag 95", $idx + 1));
                            }
                        }
                    } elseif ($tagName == "9B") {
                        $valueBuffer = StringUtils::bytesFromHex($value);
                        $maskBuffer = StringUtils::bytesFromHex("E800");
                        for ($idx = 0; $idx < strlen($valueBuffer); $idx++) {
                            if (($valueBuffer[$idx] & $maskBuffer[$idx]) != $maskBuffer[$idx]) {
                                $rvalue->setStandInStatus(false, sprintf("Invalid TSI status in byte %s of tag 9B", $idx + 1));
                            }
                        }
                    }

                    $rvalue->addTagData($approvedTag);
                } else {
                    $rvalue->addRemovedTag($tagName, $lengthStr, $value, self::$blackList[$tagName]);
                }
            } catch (\Exception $exc) {}
        }

        if ($verbose) {
            echo "Accepted Tags:" . PHP_EOL;
            foreach (array_keys($rvalue->getAcceptedTags()) as $tagName) {
                /** @var TlvData $tag */
                $tag = $rvalue->getTag($tagName);
                $appendBinary = array_key_exists($tagName, self::$dataTypes);
                echo sprintf("TAG: %s - %s", $tagName, $tag->getDescription());
                echo sprintf("%s: %s%s\r\n",$tag->getLength(), $tag->getValue(), $appendBinary ? sprintf(" [%s]", $tag->getBinaryValue()) : "");
            }
            echo "Removed Tags:" . PHP_EOL;
            $removedTags = $rvalue->getRemovedTags();
            foreach (array_keys($removedTags) as $tagName) {
                /** @var TlvData $tag */
                $tag = $removedTags[$tagName];
                echo sprintf("TAG: %s - %s", $tagName, $tag->getDescription());
                echo sprintf('%s: %s\r\n',$tag->getLength(), $tag->getValue());
            }
        }

        return $rvalue;
    }
}