<?php

namespace WebuddhaInc\IIF;

use WebuddhaInc\IIF\Transaction;

class Parser {

  public $columns = array();
  public $transactions = array();
  public $delimeter = "\t";
  public $eol = "\n";

  public function __construct(){
  }

  public function readFile( $file ){
    if (file_exists($file)) {
      if ($fh = fopen($file, 'r')) {
        while ($line = fgets($fh)) {
          $line      = preg_replace('/[\r\n]/', '', $line);
          $line_cols = explode("\t", $line);
          $line_key  = reset($line_cols);
          switch ($line_key) {

            /**
             * Header
             */
            case '!TRNS':
            case '!SPL':
              $this->columns[ str_replace('!', '', $line_key) ] = array_slice($line_cols, 1);
              break;
            case '!ENDTRNS':
              break;

            /**
             * Transaction
             */
            case 'TRNS':
              if (empty($this->columns['TRNS'])) {
                throw new Exception('No Transaction Headers Defined');
              }
              $trans = new Transaction();
              $trans->data = array_combine($this->columns['TRNS'], array_slice($line_cols, 1, count($this->columns['TRNS'])));
              break;
            case 'SPL':
              if (empty($this->columns['SPL'])) {
                throw new Exception('No Split Headers Defined');
              }
              $trans->splits[] = array_combine($this->columns['SPL'], array_slice($line_cols, 1, count($this->columns['SPL'])));
              break;
            case 'ENDTRNS':
              if (empty($trans)) {
                throw new Exception('No Transaction to End');
              }
              $this->transactions[] = $trans;
              unset($trans);
              break;

            /**
             * Who knows
             */
            default:
              throw new Exception('Unknown Line: ' . $line);
              break;

          }
        }
        fclose($fh);
      }
      else {
        throw new Exception("Cannot Open {$file}");
      }
    }
    else {
      throw new Exception("File Not Found {$file}");
    }
  }

  public function writeFile( $file ){
    if (is_writable($file) || $file == 'php://output') {
      if ($fh = fopen($file, 'w')) {

        /**
         * Header
         */
        if (empty($this->columns['TRNS'])) {
          throw new Exception('No Transaction Headers Defined');
        }
        fwrite($fh, '!TRNS' . $this->delimeter . implode($this->delimeter, $this->columns['TRNS']) . $this->eol);
        if (empty($this->columns['SPL'])) {
          throw new Exception('No Split Headers Defined');
        }
        fwrite($fh, '!SPL' . $this->delimeter . implode($this->delimeter, $this->columns['SPL']) . $this->eol);
        fwrite($fh, '!ENDTRNS' . $this->eol);

        /**
         * Transactions
         */
        foreach ($this->transactions AS $trans) {
          fwrite($fh, 'TRNS' . $this->delimeter . implode($this->delimeter, array_values($trans->data)) . $this->eol);
          foreach ($trans->splits AS $split) {
            fwrite($fh, 'SPL' . $this->delimeter . implode($this->delimeter, array_values($split)) . $this->eol);
          }
          fwrite($fh, 'ENDTRNS' . $this->eol);
        }

        fclose($fh);
      }
      else {
        throw new Exception("Cannot Open {$file}");
      }
    }
    else {
      throw new Exception("File Not Writable {$file}");
    }
  }

}