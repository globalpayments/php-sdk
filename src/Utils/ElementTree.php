<?php

namespace GlobalPayments\Api\Utils;

use BadMethodCallException;
use DOMDocument;
use DOMElement;
use Exception;
use GlobalPayments\Api\Entities\Enum;
use GlobalPayments\Api\Entities\Exceptions\ApiException;

class ElementTree
{
    /** 
     * @var DOMDocument;
     * */ 
    private $doc;

    /**
     * @var array<string,string>
     */
    private array $namespaces;

    public function __construct()
    {
        $this->doc = new DOMDocument(version: "1.0", encoding: "UTF-8");
        $this->doc->xmlStandalone = false;
        $this->namespaces = array();
    }

    public function addNamespace(String $prefix, String $uri) 
    {
        $this->namespaces[$prefix] = $uri;
    }

    public function getNameSpace(String $prefix): string
    {
        return $this->namespaces[$prefix];
    }

    public function element(string $tagName): Element
    {
        /** @var DOMElement */
        $element = null;

        if (strpos($tagName, ":") !== false) {
            $data = explode(":", $tagName);
            $namespaceURI = $this->namespaces[$data[0]];
            $element = $this->doc->createElementNS($namespaceURI, $tagName);
        } else {
            $element = $this->doc->createElement($tagName);
        }

        return new Element($this->doc, $element, $this->namespaces);
    }

    public function __call($name, $arguments)
    {
        if ($name == 'subElement') {
            if (count($arguments) == 2) {
                return $this->subElement($arguments[0], $arguments[1]);
            } elseif (count($arguments) == 3) {
                if ($arguments[2] === null) {
                    return null;
                } else {
                    if (count($arguments) == 3 && is_int($arguments[2])) {
                        return $this->subElementInt($arguments[0], $arguments[1], $arguments[2]);
                    } else if (count($arguments) == 3 && is_string($arguments[2])) {
                        return $this->subElementString($arguments[0], $arguments[1], $arguments[2]);
                    } else if (count($arguments) == 3 && is_float($arguments[2])) {
                        return $this->subElementFloat($arguments[0], $arguments[1], $arguments[2]);
                    } else if (count($arguments) == 3 && $arguments[2] instanceof Enum) {
                        return $this->subElementEnum($arguments[0], $arguments[1], $arguments[2]);
                    }
                }
            } 
        }

        throw new BadMethodCallException("Method $name not found.");
    }

    private function subElement(Element $parent, string $tagName): Element
    {
        /** @var DOMElement */
        $child = null;

        if (strpos($tagName, ":") !== false) {
            $data = explode(":", $tagName);
            $namespaceURI = $this->namespaces[$data[0]];
            $child = $this->doc->createElementNS($namespaceURI, $tagName);
        } else {
            $child = $this->doc->createElement($tagName);
        }

        $parent->getElement()->appendChild($child);
        return new Element($this->doc, $child, $this->namespaces);
    }

    private function subElementInt(Element $parent, string $tagName, int $value = null): ?Element
    {
        if ($value == null || $value == 0) {
            return null;
        }

        return $this->subElement($parent, $tagName)->text($value . "");
    }

    private function subElementString(Element $parent, string $tagName, string $value = null): ?Element
    {
        if ($value == null || $value === "") {
            return null;
        }
        return $this->subElement($parent, $tagName)->text($value);
    }

    private function subElementEnum(Element $parent, string $tagName, Enum $value = null): ?Element
    {
        if ($value == null) {
            return null;
        }

        return $this->subElement($parent, $tagName, (string)Enum::getKey($value));
    }

    private function subElementFloat(Element $parent, string $tagName, float $value = null): ?Element
    {
        if ($value === null) {
            return null;
        }

        return $this->subElement($parent, $tagName)->text($value . "");
    }

    public function toString(Element $root): string
    {
        $this->doc->appendChild($root->getElement());

        try {
            return $this->doc->saveXML();
        } catch (Exception $e) {
            return $e->getMessage();
        } finally {
            $this->doc->removeChild($root->getElement());
        }
    }

    public function setDocument(DOMDocument $doc)
    {
        $this->doc = $doc;
    }

    public function getDocument(): DOMDocument
    {
        return $this->doc;
    }

    public static function parse(string $xml, array $namespaces): ElementTree
    {
        try {
            $dbf = new DOMDocument();
            $dbf->loadXML($xml);

            $rvalue = new ElementTree();
            $rvalue->namespaces = $namespaces;
            $rvalue->setDocument($dbf);

            return $rvalue;
        } catch (Exception $e) {
            // Throw an ApiException with the error message.
            throw new ApiException($e->getMessage());
        }
    }

    public function get($tagName)
    {
        $node = null;

        if (strpos($tagName, ":") !== false) {
            $data = explode(":", $tagName);
            $namespaceURI = $this->namespaces[$data[0]];
            $node = $this->doc->getElementsByTagNameNS($namespaceURI, $tagName)->item(0);
        } else {
            $node = $this->doc->getElementsByTagName($tagName)->item(0);
        }

        if ($node != null) {
            return Element::fromNode($this->doc, $node, $this->namespaces);
        }

        return null;
    }

}