<?php

namespace GlobalPayments\Api\Utils;

use DateTime;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;

class Element
{
     /** 
     * @var DOMDocument
     * */ 
     private $doc;

     /**
      * @var DOMElement
      */
     private ?DOMElement $element;

     /**
     * @var array<string,string>
     */
     private array $namespaces;

     public function __construct($doc, ?DOMElement $element, array $namespaces) {
          $this->doc = $doc;
          $this->element = $element;
          $this->namespaces = $namespaces;
     }

     public function getDocument(): DOMDocument
     {
          return $this->doc;
     }

     public function getElement(): DOMElement {
          return $this->element;
     }

     public function text(string $text = ""): Element
     {
          $this->element->appendChild($this->doc->createTextNode($text));
          return $this;
     }
     
     public static function fromNode($doc, $node, array $namespaces = []): Element
     {
          return new Element($doc, $node, $namespaces);
     }

     public function get($tagName)
     {
          $node = $this->element->getElementsByTagName($tagName)->item(0);
          return Element::fromNode($this->doc, $node, $this->namespaces);
     }

/**
      * 
      * @param mixed $tagName 
      * @return Element|null 
      */
     public function getTransactionSummaryElement($tagName): Element|null
     {
          // Register the namespaces
          $xpath = new DOMXPath($this->doc);

          $xpath->registerNamespace('s', 'http://schemas.xmlsoap.org/soap/envelope/');
          $xpath->registerNamespace('a', 'http://schemas.datacontract.org/2004/07/BDMS.NewModel');

          $elementNode = $xpath->query('//s:Body//' . $tagName);

          if ($elementNode->length > 0) {
               return Element::fromNode(
                    $this->doc,
                    $elementNode->item(0),
                    $this->namespaces
               );
          } else {
               return null;
          }
     }

     public function getAll(string $tagName)
     {
          $elements = [];

          $xpath = new DOMXPath($this->doc);

          // Register the namespaces used in the XML
          foreach ($this->namespaces as $prefix => $uri) {
               $xpath->registerNamespace($prefix, $uri);
          }

          $parts = explode(':', $tagName);
          if (count($parts) === 2) {
               $prefix = $parts[0];
               $localName = $parts[1];
          } else {
               $localName = $tagName;
               $prefix = '';
          }

          // Build the XPath query
          $query = $prefix ? "//{$prefix}:{$localName}" : "//{$localName}";
          
          // Execute the query
          $nodes = $xpath->query($query);

          if ($nodes === false) {
               throw new Exception("Invalid XPath query: $query");
          }
          
          foreach($nodes as $node) {
               $elements[] = Element::fromNode(
                    $this->doc, 
                    $node,
                    $this->namespaces
               );
          }         

          return $elements;
     }

     public function getMerchantsElementArray($tagName): ?array
     {
          $xpath = new DOMXPath($this->doc);

          // Register the namespaces
          $xpath->registerNamespace('s', 'http://schemas.xmlsoap.org/soap/envelope/');
          $xpath->registerNamespace('a', 'http://schemas.datacontract.org/2004/07/BDMS.NewModel');
          $xpath->registerNamespace('b', 'http://schemas.microsoft.com/2003/10/Serialization/Arrays');

          $merchantNodes = $xpath->query('//' . $tagName . '/b:string');
          $merchants = null;

          foreach ($merchantNodes as $node) {
               $merchants[] = $node->nodeValue;
          }

          return $merchants;
     }

     public function getAccountHolderData($tagName)
     {
          $xpath = new DOMXPath($this->doc);

          // Register the namespaces
          $xpath->registerNamespace('s', 'http://schemas.xmlsoap.org/soap/envelope/');
          $xpath->registerNamespace('a', 'http://schemas.datacontract.org/2004/07/BDMS.NewModel');
          $xpath->registerNamespace('b', 'http://schemas.datacontract.org/2004/07/POSGateway.Wrapper');

          $accountHolderDataNode = $xpath->query('//'. $tagName)->item(0);

          $accountHolderData = [];

          if ($accountHolderDataNode) {
               foreach ($accountHolderDataNode->childNodes as $childNode) {
                   if ($childNode->nodeType === XML_ELEMENT_NODE) {
                       $localName = $childNode->localName;
                       $accountHolderData[$localName] = $childNode->textContent === null || $childNode->textContent === '' ? null : $childNode->textContent;
                   }
               }
          }

          return $accountHolderData;
     }

     public function getString(...$tagNames): ?string
     {
          $xpath = new DOMXPath($this->doc);

          // Register the namespaces used in the XML
          foreach ($this->namespaces as $prefix => $uri) {
               $xpath->registerNamespace($prefix, $uri);
          }

          foreach ($tagNames as $tagName) {
               foreach($xpath->query('//' . $tagName) as $element) {
                    return $element->nodeValue;
               }
          }

          return null;
     }

     public function getInt(...$tagNames): ?int
     {
          $xpath = new DOMXPath($this->doc);

          // Register the namespaces used in the XML
          foreach ($this->namespaces as $prefix => $uri) {
               $xpath->registerNamespace($prefix, $uri);
          }

          foreach ($tagNames as $tagName) {
               foreach($xpath->query('//' . $tagName) as $element) {
                    return (int)$element->nodeValue;
               }
          }

          return null;
     }

     public function getBool($tagName): bool
     {
          $xpath = new DOMXPath($this->doc);

          // Register the namespaces used in the XML
          foreach ($this->namespaces as $prefix => $uri) {
               $xpath->registerNamespace($prefix, $uri);
          }

          foreach($xpath->query('//' . $tagName) as $element) {
               $isSuccessfulBool = $element->nodeValue === 'true';
               return $isSuccessfulBool;
          }

          return false;
     }

     public function getFloat($tagName): ?float
     {
          $xpath = new DOMXPath($this->doc);

          // Register the namespaces used in the XML
          foreach ($this->namespaces as $prefix => $uri) {
               $xpath->registerNamespace($prefix, $uri);
          }

          foreach($xpath->query('//' . $tagName) as $element) {
               return (float) $element->nodeValue;
          }

          return null;
     }

     public function getDateTime($tagName): ?DateTime
     {
          $xpath = new DOMXPath($this->doc);

          // Register the namespaces used in the XML
          foreach ($this->namespaces as $prefix => $uri) {
               $xpath->registerNamespace($prefix, $uri);
          }

          foreach($xpath->query('//' . $tagName) as $element) {
               return new DateTime($element->nodeValue);
          }
          return null;
     }
}


