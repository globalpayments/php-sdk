<?php
namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ProPayAccountStatus extends Enum
{

    const READY_TO_PROCESS = 'ReadyToProcess';

    const FRAUD_ACCOUNT = 'FraudAccount';

    const RISK_WISE_DECLINED = 'RiskwiseDeclined';

    const HOLD = 'Hold';

    const CANCELED = 'Canceled';

    const FRAUD_VICTIM = 'FraudVictim';

    const CLOSED_EULA = 'ClosedEULA';

    const CLOSED_EXCESSIVE_CHARGEBACK = 'ClosedExcessiveChargeback';
}
