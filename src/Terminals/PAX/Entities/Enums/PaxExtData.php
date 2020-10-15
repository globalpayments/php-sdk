<?php

namespace GlobalPayments\Api\Terminals\PAX\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaxExtData extends Enum
{

    const TABLE_NUMBER = "TABLE";
    const GUEST_NUMBER = "GUEST";
    const SIGNATURE_CAPTURE = "SIGN";
    const TICKET_NUMBER = "TICKET";
    const HOST_REFERENCE_NUMBER = "HREF";
    const TIP_REQUEST = "TIPREQ";
    const SIGNATURE_UPLOAD = "SIGNUPLOAD";
    const REPORT_STATUS = "REPORTSTATUS";
    const TOKEN_REQUEST = "TOKENREQUEST";
    const TOKEN = "TOKEN";
    const CARD_TYPE = "CARDTYPE";
    const CARD_TYPE_BITMAP = "CARDTYPEBITMAP";
    const PASS_THROUGH_DATA = "PASSTHRUDATA";
    const RETURN_REASON = "RETURNREASON";
    const ORIGINAL_TRANSACTION_DATE = "ORIGTRANSDATE";
    const ORIGINAL_PAN = "ORIGPAN";
    const ORIGINAL_EXPIRATION_DATE = "ORIGEXPIRYDATE";
    const ODOMETER_READING = "ODOMETER";
    const VEHICLE_NUMBER = "VEHICLENO";
    const JOB_NUMBER = "JOBNO";
    const DRIVER_ID = "DRIVERID";
    const EMPLOYEE_NUMBER = "EMPLOYEENO";
    const LICENSE_NUMBER = "LICENSENO";
    const JOB_ID = "JOBID";
    const DEPARTMENT_NUMBER = "DEPARTMENTNO";
    const CUSTOMER_DATA = "CUSTOMERDATA";
    const USER_ID = "USERID";
    const VEHICLE_ID = "VEHICLEID";
    const APPLICATION_PREFERRED_NAME = "APPPN";
    const APPLICATION_LABEL = "APPLAB";
    const APPLICATION_ID = "AID";
    const CUSTOMER_VERIFICATION_METHOD = "CVM";
    const TRANSACTION_CERTIFICATE = "TC";
    const ECR_REFERENCE_NUMBER = "ECRRefNum";
    const CARD_BIN = "CARDBIN";
    const SIGNATURE_STATUS = "SIGNSTATUS";
    const TERMINAL_VERIFICATION_RESULTS = "TVR";
    const FPS_SIGN = "FPSSIGN";
    const FPS = "FPS";
    const MERCHANT_ID = "MM_ID";
    const MERCHANT_NAME = "MM_NAME";
}
