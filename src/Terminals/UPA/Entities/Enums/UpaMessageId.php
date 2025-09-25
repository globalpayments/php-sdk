<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class UpaMessageId extends Enum
{
    const SALE = "Sale";
    const VOID = "Void";
    const REFUND = "Refund";
    const EOD = "EODProcessing";
    const SEND_SAF = "SendSAF";
    const TIPADJUST = "TipAdjust";
    const CARD_VERIFY = "CardVerify";
    const GET_SAF_REPORT = "GetSAFReport";
    const CANCEL   = "CancelTransaction";
    const REBOOT   = "Reboot";
    const LINEITEM = "LineItemDisplay";
    const REVERSAL = "Reversal";
    const GET_BATCH_REPORT = "GetBatchReport";
    const GET_BATCH_DETAILS = "GetBatchDetails";
    const AVAILABLE_BATCHES = 'AvailableBatches';
    const BALANCE_INQUIRY = "BalanceInquiry";
    const PRE_AUTH = "PreAuth";
    const DELETE_PRE_AUTH = "DeletePreAuth";
    const CAPTURE = "AuthCompletion";
    const TOKENIZE = "Tokenize";
    const GET_OPEN_TAB_DETAILS = "GetOpenTabDetails";
    const PING = "Ping";
    const RESTART = "Restart";
    const GET_APP_INFO = 'GetAppInfo';
    const CLEAR_DATA_LAKE = 'ClearDataLake';
    const SET_TIME_ZONE = 'SetTimeZone';
    const GET_PARAM = 'GetParam';
    const GET_SIGNATURE = 'GetSignature';
    const REGISTER_POS = 'RegisterPOS';
    const BROADCAST_CONFIGURATION = 'BroadcastConfiguration';
    const SET_DEBUG_LEVEL = 'SetDebugLevel';
    const GET_DEBUG_LEVEL = 'GetDebugLevel';
    const GET_DEBUG_INFO = 'GetDebugInfo';
    const RETURN_TO_IDLE = 'ReturnToIdle';
    const LOAD_UD_SCREEN = 'LoadUDDataFile';
    const REMOVE_UD_SCREEN = 'RemoveUDDataFile';
    const EXECUTE_UD_SCREEN = 'ExecuteUDDataFile';
    const INJECT_UD_SCREEN = 'InjectUDDataFile';
    const SCAN = 'Scan';
    const PRINT = 'PrintData';
    const GET_CONFIG_CONTENTS = 'GetConfigContents';
    const MAIL_ORDER = 'MailOrder';
    const REMOVE_CARD = 'RemoveCard';
    const ENTER_PIN = 'EnterPIN';
    const PROMPT_WITH_OPTIONS = 'PromptWithOptions';
    const PROMPT_MENU = 'PromptMenu';
    const UPDATE_TAX_INFO = 'UpdateTaxInfo';
    const UPDATE_LODGING_DETAILS = 'UpdateLodgingDetails';
    const GENERAL_ENTRY = 'GeneralEntry';
    const DISPLAY_MESSAGE = 'DisplayMessage';
    const RETURN_DEFAULT_SCREEN = 'ReturnDefaultScreen';
    const GET_ENCRYPTION_TYPE = 'GetEncryptionType';
    const COMMUNICATION_CHECK = 'CommunicationCheck';
    const LOGON = 'Logon';
    const GET_LAST_EOD = "GetLastEOD";
    const FORCE_SALE = "ForceSale";
    const START_CARD_TRANSACTION = "StartCardTransaction";
    const CONTINUE_EMV_TRANSACTION = "ContinueEMVTransaction";
    const COMPLETE_EMV_TRANSACTION = "CompleteEMVTransaction";
    const PROCESS_CARD_TRANSACTION = "ProcessCardTransaction";
    const CONTINUE_CARD_TRANSACTION = "ContinueCardTransaction";
}
