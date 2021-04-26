<?php

namespace App\Bundles\Notifications;

use App\Models\User;
use Illuminate\Http\Request;
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

  /**
   * Send SMS Notification about new password
   *
   * @param int $user_id
   * @param string $password
   * @return void
   */
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

      Log::channel('sms')->info('SMS to abonent ' . $user->phone . ' sended successfull : ' . $message);

      return true;
    }

    Log::channel('sms')->error('Error sending SMS to abonent ' . $user->phone . ': ' . print_r(curl_getinfo($ch), true));

    curl_close($ch);

    return $res;
  }


  /**
   * Send any notification via SMS
   *
   * @param string $phone
   * @param string $message
   * @return void
   */
  public function sendSMSNotify($phone, $message)
  {
    $link = 'https://smsc.ru/sys/send.php?login=' . $this->login . '&psw=' . $this->password . '&phones=' . $phone . '&mes=' . urlencode($message) . '&charset=utf-8';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 && substr($res, 0, 2) == 'OK') {
      curl_close($ch);

      Log::channel('sms')->info('SMS to abonent ' . $phone . ' sended successfull : ' . $message);

      return true;
    }

    Log::channel('sms')->error('Error sending SMS to abonent ' . $phone . ': ' . print_r(curl_getinfo($ch), true));

    curl_close($ch);

    return $res;
  }

  public function sendSMSNotifyAction(Request $request)
  {
    $this->formData = $request->all();
    if (isset($this->formData['phone']) && isset($this->formData['message'])) {
      $result = $this->sendSMSNotify($this->formData['phone'], $this->formData['message']);
      return response()->json($result, 200);
    } else {
      return abort('404');
    }
  }
}
