<?php

/**
 * This file uses the IIF parser to perform some custom IIF modifications
 * to the IIF file exported from PayPal.  This alse serves as an example.
 */

require 'autoload.php';
use WebuddhaInc\IIF\Parser;

if( !function_exists('inspect') ){
  function inspect(){
    echo '<pre>' . print_r(func_get_args(), true) . '</pre>';
  }
}

if (isset($_FILES['iif']) && isset($_FILES['iif']['tmp_name'])) {

  /**
   * Parse File
   */
  $iif = new Parser();
  $iif->readFile( $_FILES['iif']['tmp_name'] );

  /**
   * Modify Transactions
   */
  if ($iif->transactions) {
    foreach ($iif->transactions AS &$trans) {
      if ($trans->data['CLASS'] == '"General Withdrawal"'){
        $trans->splits[0]['ACCNT'] = '"Ask My Accountant"';
      }
      if ($trans->data['CLASS'] == '"Payment Refund"'){
        if ($trans->data['AMOUNT'] > 0) {
          $trans->splits[0]['ACCNT'] = '"Computer and Internet Expenses"';
        }
        else {
          $trans->splits[0]['ACCNT'] = '"Sales - Support and Maintenance"';
        }
      }
      if (in_array($trans->data['CLASS'], array('"Reversal of General Account Hold"', '"General Currency Conversion"'))){
        $trans->splits[0]['ACCNT'] = '"Computer and Internet Expenses"';
      }
      foreach ($trans->splits AS &$split) {
        if ($split['ACCNT'] == '"Other income"'){
          $split['ACCNT'] = '"Sales - Support and Maintenance"';
        }
        if ($split['ACCNT'] == '"Other expenses"'){
          $split['ACCNT'] = '"Computer and Internet Expenses"';
        }
        if ($split['NAME'] == 'Fee'){
          $split['ACCNT'] = '"Merchant Account Fees"';
        }
      }
    }
  }

  /**
   * Download New File
   */
  header("Content-Type: text/plain");
  header("Content-Disposition: attachment; filename=" . preg_replace('/^(.*)\.([A-Za-z0-9]+)$/', '$1-Modified.$2', $_FILES['iif']['name']));
  $iif->writeFile('php://output');

}
else {

  ?>
  <p>Select a PayPal IIF file to process.</p>
  <form method="post" enctype="multipart/form-data">
    <input type=file name="iif">
    <input type="submit">
  </form>
  <?php

}