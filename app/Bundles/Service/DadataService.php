<?php


namespace App\Bundles\Service;

use Exception;
use Illuminate\Http\Request;

use App\Bundles\Keyboard\Switcher;
use Fomvasss\Dadata\Facades\DadataClean;
use Fomvasss\Dadata\Facades\DadataSuggest;

class DadataService
{
    /**
     * Count result response.
     *
     * @var int
     */
    public static $count = 10;


    /**
     * Get response to Dadata.
     *
     * @param $type
     * @param $query
     * @return array
     */
    public static function getResponseToDadata($type, $query)
    {
        try {

            $result = DadataSuggest::suggest($type, ["query" => $query, "count" => self::$count]);
            if (count($result) == 3 && isset($result["value"])) {
                $result = array($result);
            }
        } catch (Exception $e) {

            $result = [];
        }

        return $result;
    }

    /**
     * Main function to get data.
     *
     * @param $type
     * @param $query
     * @return |null
     */
    public static function getData($type, $query)
    {
        $result = self::getResponseToDadata($type, $query);

        if (!count($result)) {
            $query = Switcher::toCyrillic($query);
            $result = self::getResponseToDadata($type, $query);
        }

        return $result;
    }

    /**
     * Get info company to user.
     *
     * @param Request $request
     * @return mixed
     */
    public static function getCompany(Request $request)
    {
        return self::getData('party', $request->company);
    }

    /**
     * Get info address to user.
     *
     * @param Request $request
     * @return mixed
     */
    public static function getAddress(Request $request)
    {
        return self::getData('address', $request->address);
    }

    /**
     * Get fio to user.
     *
     * @param Request $request
     * @return mixed
     */
    public static function getName(Request $request)
    {
        return self::getData('fio', $request->name);
    }

    /**
     * Get email to user.
     *
     * @param Request $request
     * @return mixed
     */
    public static function getEmail(Request $request)
    {
        return self::getData('email', $request->email);
    }

    /**
     * Validate phone.
     *
     * @param Request $request
     * @return mixed
     */
    public static function cleanPhone(Request $request)
    {
        return DadataClean::cleanPhone($request->phone);
    }
}
