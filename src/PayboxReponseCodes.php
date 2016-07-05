<?php
/**
 * Created by IntelliJ IDEA.
 * User: marem
 * Date: 09/11/15
 * Time: 19:01
 */

namespace Marem\PayumPaybox;


class PayboxResponseCodes {

    const SUCCESS = "00000";

    const CONTACT_CARD_OWNER = "01";

    const INVALID_TRANSACTION = "12";

    const INVALID_AMOUNT = "13";

    const INVALID_HOLDER_NUMBER = "14";

    const CUSTOM_CANCELATION = "17";

    const RETRY_LATER = "19";

    const EXPIRED_CARD = "33";

}