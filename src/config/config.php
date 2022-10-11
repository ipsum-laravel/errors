<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   Laravel 4 Smart Errors
 */

return array(
    // The email the error reports will be sent to.
    'dev-email' => null,

    // send an email even if mail.pretend == true
    'force-email' => false,

    'minutesBetwwenSendMail' => 10,

    'dureeCache' => 60 * 60 * 24 * 30 ,

    // The PHP date() format that should be used. Leave as null for default
    // Default: Y-m-d H:i:s e
    'date-format' => null,
);
