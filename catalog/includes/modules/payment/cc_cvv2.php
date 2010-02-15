<?php
/*
  Credit Card with CVV2 compatible with Dynamo Checkout
  Based on cc.php by hpdl Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  class cc_cvv2 {
    var $code, $title, $description, $enabled;

    function cc_cvv2() {
      global $order;

      $this->code = 'cc_cvv2';
      $this->title = MODULE_PAYMENT_CC_CVV2_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_CC_CVV2_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_CC_CVV2_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_CC_CVV2_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_CC_CVV2_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_CC_CVV2_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_CC_CVV2_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_CC_CVV2_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_CC_CVV2_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
			$js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
            '    var cc_cvv2_owner = document.checkout_payment.cc_cvv2_owner.value;' . "\n" .
            '    var cc_cvv2_number = document.checkout_payment.cc_cvv2_number.value;' . "\n" .
            '    if (cc_cvv2_owner == "" || cc_cvv2_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_CC_CVV2_TEXT_JS_CC_CVV2_OWNER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '    if (cc_cvv2_number == "" || cc_cvv2_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_CC_CVV2_TEXT_JS_CC_CVV2_NUMBER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n";
      if (MODULE_PAYMENT_CC_CVV2_REQUIRE_CVV2 == 'True') {
        $js .= '    var cc_cvv2_cvv2 = document.checkout_payment.cc_cvv2_cvv2.value;' . "\n" .
               '    if (cc_cvv2_cvv2 == "" || cc_cvv2_cvv2.length < 3) {' . "\n" .
               '      error_message = error_message + "' . MODULE_PAYMENT_CC_CVV2_TEXT_JS_CC_CVV2_CVV2 . '";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }     
      $js .= '  }' . "\n";
      return $js;
    }

    function selection() {
      global $order;

      for ($i=1; $i<13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
      }

      $today = getdate(); 
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }
      
      return array('id' => $this->code,
                   'module' => $this->public_title,
                   'fields' => array(array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_OWNER,
                                           'field' => tep_draw_input_field('cc_cvv2_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                     array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_NUMBER,
                                           'field' => tep_draw_input_field('cc_cvv2_number')),
                                     array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_EXPIRES,
                                           'field' => tep_draw_pull_down_menu('cc_cvv2_expires_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('cc_cvv2_expires_year', $expires_year)),
                                     array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_CVV2,
                                           'field' => tep_draw_input_field('cc_cvv2_cvv2', '', 'size=4'))));
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {

      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['cc_cvv2_owner']),
                                              array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => str_repeat('X', (strlen($_POST['cc_cvv2_number']) - 4)) . substr($_POST['cc_cvv2_number'], -4)),
                                              array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => $_POST['cc_cvv2_expires_month'] . '/' . $_POST['cc_cvv2_expires_year']),
                                              array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_CREDIT_CARD_CVV2,
                                                    'field' => str_repeat('X', strlen($_POST['cc_cvv2_cvv2'])))));
                                                    
      return $confirmation;
    }

    function process_button() {
      $process_button_string = tep_draw_hidden_field('cc_cvv2_owner', $_POST['cc_cvv2_owner']) . 
                               tep_draw_hidden_field('cc_cvv2_number', $_POST['cc_cvv2_number']) . 
                               tep_draw_hidden_field('cc_cvv2_expires_month', $_POST['cc_cvv2_expires_month']) . 
                               tep_draw_hidden_field('cc_cvv2_expires_year', $_POST['cc_cvv2_expires_year']) . 
                               tep_draw_hidden_field('cc_cvv2_cvv2', $_POST['cc_cvv2_cvv2']);
    
      return $process_button_string;
    }

    function before_process() {
      global $order;

      include(DIR_WS_CLASSES . 'cc_validation.php');

      $cc_validation = new cc_validation();
      $result = $cc_validation->validate($_POST['cc_cvv2_number'], $_POST['cc_cvv2_expires_month'], $_POST['cc_cvv2_expires_year']);

      $error = '';
      switch ($result) {
        case -1:
          $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
          break;
        case -2:
        case -3:
        case -4:
          $error = TEXT_CCVAL_ERROR_INVALID_DATE;
          break;
        case false:
          $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
          break;
      }
      
      if (MODULE_PAYMENT_CC_CVV2_REQUIRE_CVV2 == 'True' && strlen($_POST['cc_cvv2_cvv2']) < 3) {
        if ($error != '') $error .= '<br />';
        $error .= MODULE_PAYMENT_CC_CVV2_TEXT_JS_CC_CVV2_CVV2;
      }


      if ( ($result == false) || ($result < 1) ) {
        $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&cc_cvv2_owner=' . urlencode($_POST['cc_cvv2_owner']) . '&cc_cvv2_expires_month=' . $_POST['cc_cvv2_expires_month'] . '&cc_cvv2_expires_year=' . $_POST['cc_cvv2_expires_year'];

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
      }

      $order->info['cc_owner'] = $_POST['cc_cvv2_owner'];
      $order->info['cc_type'] = $cc_validation->cc_type;
      $order->info['cc_number'] = $_POST['cc_cvv2_number'];
      $order->info['cc_expires'] = $_POST['cc_cvv2_expires_month'] . $_POST['cc_cvv2_expires_year'];
      $order->info['cc_cvv2'] = $_POST['cc_cvv2_cvv2'];
      
      if ($this->order_status > 0) {
        $order->info['order_status'] = $this->order_status;
      }
      
      if ( (defined('MODULE_PAYMENT_CC_CVV2_EMAIL')) && (tep_validate_email(MODULE_PAYMENT_CC_CVV2_EMAIL)) ) {
        $len = strlen($_POST['cc_cvv2_number']);

        $this->cc_middle = substr($_POST['cc_cvv2_number'], 4, ($len-8));
        $order->info['cc_number'] = substr($_POST['cc_cvv2_number'], 0, 4) . str_repeat('X', (strlen($_POST['cc_cvv2_number']) - 8)) . substr($_POST['cc_cvv2_number'], -4);
      }
    }

    function after_process() {
      global $insert_id;

      if ( (defined('MODULE_PAYMENT_CC_CVV2_EMAIL')) && (tep_validate_email(MODULE_PAYMENT_CC_CVV2_EMAIL)) ) {
        $message = 'Order #' . $insert_id . "\n\n" . 'Middle: ' . $this->cc_middle . "\n\n";
        
        tep_mail('', MODULE_PAYMENT_CC_CVV2_EMAIL, 'Extra Order Info: #' . $insert_id, $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }
    }

    function get_error() {
      $error = array('title' => MODULE_PAYMENT_CC_CVV2_TEXT_ERROR,
                     'error' => stripslashes(urldecode($_GET['error'])));

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CC_CVV2_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Credit Card Module', 'MODULE_PAYMENT_CC_CVV2_STATUS', 'True', 'Do you want to accept credit card payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Split Credit Card E-Mail Address', 'MODULE_PAYMENT_CC_CVV2_EMAIL', '', 'If an e-mail address is entered, the middle digits of the credit card number will be sent to the e-mail address (the outside digits are stored in the database with the middle digits censored)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_CC_CVV2_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_CC_CVV2_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_CC_CVV2_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Require CCV2', 'MODULE_PAYMENT_CC_CVV2_REQUIRE_CVV2', 'True', 'Require cvv2 numbers?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      
      $col_query = tep_db_query("SHOW COLUMNS FROM " . TABLE_ORDERS);
      $found = false;
      
      while ($col = tep_db_fetch_array($col_query)) {
        if ($col['Field'] == 'cc_cvv2') $found = true;
      }

      if (!$found) {
        tep_db_query("ALTER TABLE `" . TABLE_ORDERS . "` ADD `cc_cvv2` VARCHAR(4) AFTER `cc_expires`");
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_CC_CVV2_STATUS', 'MODULE_PAYMENT_CC_CVV2_EMAIL', 'MODULE_PAYMENT_CC_CVV2_ZONE', 'MODULE_PAYMENT_CC_CVV2_ORDER_STATUS_ID', 'MODULE_PAYMENT_CC_CVV2_SORT_ORDER', 'MODULE_PAYMENT_CC_CVV2_REQUIRE_CVV2' );
    }
  }
?>