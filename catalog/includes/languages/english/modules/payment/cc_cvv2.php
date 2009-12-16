<?php
/*
  Credit Card with CVV2 compatible with Dynamo Checkout
  Based on cc.php by hpdl Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_CC_CVV2_TEXT_TITLE', 'Credit Card with CVV2');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_PUBLIC_TITLE', 'Credit Card');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_DESCRIPTION', 'Credit Card Test Info:<br><br>CC#: 4111111111111111<br>Expiry: Any');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_CVV2', 'Credit Card CVV2:');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_JS_CC_CVV2_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_JS_CC_CVV2_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_JS_CC_CVV2_CVV2', '* The CVV2 security code must be at least 3 characters.');
  define('MODULE_PAYMENT_CC_CVV2_TEXT_ERROR', 'Credit Card Error!');
?>