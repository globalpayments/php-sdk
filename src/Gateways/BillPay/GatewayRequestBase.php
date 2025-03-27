<?php

namespace GlobalPayments\Api\Gateways\BillPay;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Gateways\XmlGateway;
use GlobalPayments\Api\Utils\{Element, ElementTree};

abstract class GatewayRequestBase extends XmlGateway
{
    public string $publicEndpoint = 'BillingDataManagement/v3/BillingDataManagementService.svc/BillingDataManagementService';

    protected Credentials $credentials;

    /**
     * Creates a SOAP envelope with the necessary namespaces
     * @param ElementTree $et
     * @param string soapAction The method name that is the target of the invocation
     * 
     * @return Element The Element that represents the envelope node
     */
    protected function createSOAPEnvelope(ElementTree $et, string $soapAction): Element
    {
        $this->setSOAPAction($soapAction);
        $this->addXMLNS($et);

        return $et->element("soapenv:Envelope");
    }

    /**
     * Creates and sets the SOAPAction header using the supplied method name
     * 
     * @param string $soapAction The method name that is the target of the invocation
     */
    protected function setSOAPAction(string $soapAction)
    {
        $this->headers["SOAPAction"] = "https://test.heartlandpaymentservices.net/BillingDataManagement/v3/BillingDataManagementService/IBillingDataManagementService/" . $soapAction;
    }

    /**
     * Adds the XML Namespaces necessary to make BillPay SOAP requests
     * 
     * @param ElementTree $et The element tree for the SOAP request
     */
    protected function addXMLNS(ElementTree $et)
    {
        $et->addNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $et->addNamespace('bil', 'https://test.heartlandpaymentservices.net/BillingDataManagement/v3/BillingDataManagementService');
        $et->addNamespace('bdms', 'http://schemas.datacontract.org/2004/07/BDMS.NewModel');
        $et->addNamespace('hps', 'http://schemas.datacontract.org/2004/07/HPS.BillerDirect.ACHCard.Wrapper');
        $et->addNamespace('pos', 'http://schemas.datacontract.org/2004/07/POSGateway.Wrapper');
        $et->addNamespace('bdm', 'https://test.heartlandpaymentservices.net/BillingDataManagement/v3/BDMServiceAdmin');
    }
}