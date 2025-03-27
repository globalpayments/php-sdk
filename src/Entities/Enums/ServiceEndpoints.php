<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ServiceEndpoints extends Enum
{
    const GLOBAL_ECOM_PRODUCTION = "https://api.realexpayments.com/epage-remote.cgi";
    const GLOBAL_ECOM_TEST = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
    const PORTICO_PRODUCTION = "https://api2.heartlandportico.com";
    const PORTICO_TEST = "https://cert.api2.heartlandportico.com";
    const THREE_DS_AUTH_PRODUCTION = "https://api.globalpay-ecommerce.com/3ds2/";
    const THREE_DS_AUTH_TEST = "https://api.sandbox.globalpay-ecommerce.com/3ds2/";
    const PAYROLL_PRODUCTION = "https://taapi.heartlandpayrollonlinetest.com/PosWebUI";
    const PAYROLL_TEST = "https://taapi.heartlandpayrollonlinetest.com/PosWebUI/Test/Test";
    const TABLE_SERVICE_PRODUCTION = "https://www.freshtxt.com/api31/";
    const TABLE_SERVICE_TEST = "https://www.freshtxt.com/api31/";
    const MERCHANTWARE_TEST = "https://ps1.merchantware.net/Merchantware/ws/";
    const MERCHANTWARE_PRODUCTION = "https://ps1.merchantware.net/Merchantware/ws/";
    const TRANSIT_TEST = "https://stagegw.transnox.com/servlets/TransNox_API_Server";
    const TRANSIT_PRODUCTION = "https://gateway.transit-pass.com/servlets/TransNox_API_Server/";
    const PROPAY_TEST = "https://xmltest.propay.com/API/PropayAPI.aspx";
    const PROPAY_TEST_CANADIAN = "https://xmltestcanada.propay.com/API/PropayAPI.aspx";
    const PROPAY_PRODUCTION = "https://epay.propay.com/API/PropayAPI.aspx";
    const PROPAY_PRODUCTION_CANADIAN = "https://www.propaycanada.ca/API/PropayAPI.aspx";
    const GP_API_TEST = "https://apis.sandbox.globalpay.com/ucp";
    const GP_API_PRODUCTION = "https://apis.globalpay.com/ucp";
    const OPEN_BANKING_TEST = 'https://api.sandbox.globalpay-ecommerce.com/openbanking';
    const OPEN_BANKING_PRODUCTION = 'https://api.globalpay-ecommerce.com/openbanking';
    const TRANSACTION_API_TEST = "https://api.pit.paygateway.com/transactions/";
    const TRANSACTION_API_PROD = "https://api.paygateway.com/transactions";
    const DIAMOND_CLOUD_TEST = "https://qr-cert.simpletabcloud.com/tomcat/command";
    const DIAMOND_CLOUD_PROD = "https://qr.simpletabcloud.com/tomcat/command";
    const DIAMOND_CLOUD_PROD_EU = "https://qreu.simpletabcloud.com/tomcat/command";
    const MEET_IN_THE_CLOUD_PROD = "https://api.paygateway.com";
    const MEET_IN_THE_CLOUD_TEST = "https://api.pit.paygateway.com";
    const BILLPAY_TEST = "https://testing.heartlandpaymentservices.net";
    const BILLPAY_CERTIFICATION = "https://staging.heartlandpaymentservices.net";
    const BILLPAY_PRODUCTION = "https://heartlandpaymentservices.net";
}
