<?php
namespace Civi\Cxn\Rpc;

use Civi\Cxn\Rpc\Exception\CxnException;

class Cxn {

  /**
   * @return string
   */
  public static function createId() {
    return 'cxn:' . BinHex::bin2hex(crypt_random_string(Constants::CXN_ID_CHARS));
  }

  public static function validate($cxn) {
    $errors = self::getValidationMessages($cxn);
    if (!empty($errors)) {
      throw new CxnException("Invalid Cxn: " . implode(', ', array_keys($errors)));
    }
  }

  /**
   * @param array $cxn
   * @return array
   *   List of errors. Empty error if OK.
   */
  public static function getValidationMessages($cxn) {
    $errors = array();

    if (!is_array($cxn)) {
      $errors['appMeta'] = 'Not an array';
    }

    // cxnId is completely arbitrary.
    // Secret is base64-encoded AES-256 key (32 bytes, ~45 base64 char).
    foreach (array('cxnId', 'secret', 'appId') as $key) {
      if (empty($cxn[$key])) {
        $errors[$key] = 'Required field';
      }
    }

    foreach (array('appUrl', 'siteUrl') as $key) {
      if (empty($cxn[$key])) {
        $errors[$key] = 'Required field';
      }
      elseif (!filter_var($cxn[$key], FILTER_VALIDATE_URL)) {
        $errors[$key] = 'Malformed URL';
      }
    }

    if (!isset($cxn['perm']) || !is_array($cxn['perm'])) {
      $errors['perm'] = 'Missing permisisons';
    }

    return $errors;
  }

}
