<?php

namespace App\Bundles\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class SMSC
{

  /**
   * Login for SMSC
   *
   * @var
   */
  private $login;

  /**
   * Password for SMSC
   *
   * @var
   */
  private $password;

  /**
   * SMSC constructor
   */
  public function __construct()
  {
    $this->login = 'region750';
    $this->password = 'rgn75079153788117';
  }

  public function sendPassword($user_id, $password)
  {
    $user = User::find($user_id);
    $url = env("APP_URL");

    $message = 'Адрес: ' . $url . '
Ваш логин: ' . $user->phone . '
Ваш пароль: ' . $password;

    $link = 'https://smsc.ru/sys/send.php?login=' . $this->login . '&psw=' . $this->password . '&phones=' . $user->phone . '&mes=' . urlencode($message) . '&charset=utf-8';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 && substr($res, 0, 2) == 'OK') {
      curl_close($ch);

      Log::info('SMS sended: ' . $message);

      return true;
    }

    Log::error('Error sending SMS: ' . print_r(curl_getinfo($ch), true));

    curl_close($ch);

    return $res;
  }

  public function getPassword($user_id)
  {
    return "3456";
  }
}
