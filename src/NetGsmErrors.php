<?php

namespace Siberfx\NetGsm;

/**
 * Translation keys for NetGsm API result codes.
 */
class NetGsmErrors
{
    public const string MESSAGE_TOO_LONG = 'netgsm::errors.message_too_long';

    public const string START_DATE_INCORRECT = 'netgsm::errors.start_date_incorrect';

    public const string END_DATE_INCORRECT = 'netgsm::errors.end_date_incorrect';

    public const string SENDER_INCORRECT = 'netgsm::errors.sender_incorrect';

    public const string CREDENTIALS_INCORRECT = 'netgsm::errors.credentials_incorrect';

    public const string PARAMETERS_INCORRECT = 'netgsm::errors.parameters_incorrect';

    public const string RECEIVER_INCORRECT = 'netgsm::errors.receiver_incorrect';

    public const string OTP_ACCOUNT_NOT_DEFINED = 'netgsm::errors.otp_account_not_defined';

    public const string QUERY_LIMIT_EXCEED = 'netgsm::errors.query_limit_exceed';

    public const string DUPLICATE_LIMIT_EXCEED = 'netgsm::errors.duplicate_limit_exceed';

    public const string IYS_CONTROLLED = 'netgsm::errors.iys_controlled';

    public const string IYS_BRAND_NOT_FOUND = 'netgsm::errors.iys_brand_not_found';

    public const string SYSTEM_ERROR = 'netgsm::errors.system_error';

    public const string NETGSM_GENERAL_ERROR = 'netgsm::errors.netgsm_general_error';

    public const string NO_RECORD = 'netgsm::errors.no_record';

    public const string JOB_ID_NOT_FOUND = 'netgsm::errors.job_id_not_found';
}
