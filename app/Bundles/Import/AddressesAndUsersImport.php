<?php


namespace App\Bundles\Import;

use Carbon\Carbon;
use App\Models\City;
use App\Models\Flat;
use App\Models\Role;
use App\Models\User;
use App\Models\House;
use App\Models\Region;
use App\Models\Street;
use App\Models\Contract;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class AddressesAndUsersImport extends MainImport
{
    /**
     * AddressesAndUsersImport constructor.
     *
     * @param $originalFile
     * @throws Exception
     */
    public function __construct($originalFile)
    {
        $this->importData($originalFile);
    }

    /**
     * Main function import data.
     *
     * @param $originalFile
     * @throws Exception
     */
    protected function importData($originalFile)
    {
        parent::import($originalFile);

        $data = null;

        try {
            $data = $this->reedImportFile();
        } catch (Exception $e) {

            // todo: сделать ответ на ошибку
        }

        $result = $this->addToDataBase($data);

        parent::setResult($result);

        parent::deleteFile();
    }

    /**
     * Add to data base company file
     *
     * @param $data
     * @return array
     */
    private function addToDataBase($data)
    {
        $countAllRecords  = 0;

        $countSuccessAddUsers = 0;
        $countSuccessAddAddresses = 0;
        $countSuccessAddContracts = 0;

        foreach ($data as $rowDataFromCSV) {
            $address = [
                'data' => [],
                'isNew' => false
            ];
            $contract = [
                'data' => [],
                'isNew' => false
            ];

            // 1. add address
            $address = $this->addingAddressesToDataBase($rowDataFromCSV);

            // 2. add user
            $user = $this->prepareUserData($rowDataFromCSV);

            $validatorOfUser = parent::validationData($user, [
                'name'  => 'required|string|min:2',
                'phone' => 'required|string|max:50|unique:users'
            ]);

            if ($validatorOfUser->fails()) {
                if (!empty($user['phone'])) {
                    $user = User::where('phone', $user['phone'])->first();
                }
            } else {
                $user = $this->addingUserToDataBase($user);
                $client = Role::where('slug', 'client')->first();

                $user->roles()->sync([$client->id]);

                $countSuccessAddUsers++;
            }

            // 3. add contract
            $contract = $this->addingContractToDataBase($user->id, $address, $rowDataFromCSV);


            if ($address['isNew'] == true) {
                $countSuccessAddAddresses++;
            }
            if ($contract['isNew'] == true) {
                $countSuccessAddContracts++;
            }

            $countAllRecords++;
        }

        return [
            ['name' => 'Всего записей в файле', 'count' => $countAllRecords],
            ['name' => 'Успешно добавлено пользователей', 'count' => $countSuccessAddUsers],
            ['name' => 'Успешно добавлено договоров', 'count' => $countSuccessAddContracts],
            ['name' => 'Успешно добавлено адресов', 'count' => $countSuccessAddAddresses],
        ];
    }

    /**
     * Read import file.
     *
     * @return array
     * @throws Exception
     */
    protected function reedImportFile()
    {
        $data = parent::getResultReedImportFile();

        $result = [];

        $titleTable = [];

        foreach ($data as $key => $row) {
            if ($key == 0) {
                foreach ($row as $columnName) {
                    $titleTable[] = Str::slug($columnName, '-');
                }
            }

            foreach ($row as $index => $column) {
                $result[$key][$titleTable[$index]] = htmlspecialchars_decode($column);
            }
        }

        unset($result[0]);

        return $result;
    }

    /**
     * @param $user
     * @return array
     */
    private function prepareUserData($user)
    {
        $user = parent::cleanFieldsToArray($user, [
            "nomer-dogovora",
            "indeks",
            "oblast",
            "naselennyi-punkt",
            "ulica",
            "dom",
            "kvartira",
            "god-postroiki",
            "data-zakl-dogovora",
            "telefon",
            "fio",
            "data-to"
        ]);

        // prepare phone number
        if (isset($user['telefon']) && $user['telefon'] != "") {
            $user['telefon'] = preg_replace('![^0-9]+!', '', $user['telefon']);   //Чистим телефон

            $tempPhone = str_split($user['telefon']);
            if (intval($tempPhone[0]) == 8) {
                $tempPhone[0] = 7;
            }
            if (intval($tempPhone[0]) != 7) {
                $tempPhone[0] = 7 . $tempPhone[0];
            }

            $user['telefon'] = implode("", $tempPhone);
        }

        // prepare user name
        if (isset($user['fio']) && $user['fio'] != "") {
            $user['fio'] = mb_convert_case($user['fio'], MB_CASE_TITLE, "UTF-8");
            $tempUserFio = explode(".", $user['fio']);
            if (count($tempUserFio) == 3) {
                $tempUserFio[1] = mb_convert_case($tempUserFio[1], MB_CASE_TITLE, "UTF-8");
                $user['fio'] = implode(".", $tempUserFio);
            }
        }

        return [
            'name' => $user['fio'],
            'phone' => $user['telefon'],
            'email' => '',
            'position' => '',
            'password' => bcrypt(str::random(8))
        ];
    }

    /**
     * Add to database, table users.
     *
     * @param $user
     * @return mixed
     */
    private function addingUserToDataBase($user)
    {
        return User::create($user);
    }

    /**
     * Add to database, table contracts.
     *
     * @param $user_id
     * @param $address
     * @param $importData
     */
    private function addingContractToDataBase($user_id, $address, $importData)
    {
        $result = [
            'data' => [],
            'isNew' => false
        ];

        $importData = parent::cleanFieldsToArray($importData, [
            "nomer-dogovora",
            "indeks",
            "oblast",
            "naselennyi-punkt",
            "ulica",
            "dom",
            "kvartira",
            "god-postroiki",
            "data-zakl-dogovora",
            "telefon",
            "fio",
            "data-to"
        ]);

        if (mb_strlen($importData['nomer-dogovora']) == 0) {
            return 0;
        }

        // Prepare date of contract
        // 01.02.2021
        if (mb_strlen($importData['data-zakl-dogovora']) == 10) {
            $date = Carbon::createFromFormat('d.m.Y', $importData['data-zakl-dogovora']);

            $importData['data-zakl-dogovora'] = $date->format('Y-m-d') . ' 00:00:00';
        } else {
            $importData['data-zakl-dogovora'] = NULL;
        }

        $isContractIsset = Contract::where(
            [
                ['contract_on_user_id', $user_id],
                ['contract_region_id', $address['data']['region_id']],
                ['contract_city_id', $address['data']['city_id']],
                ['contract_street_id', $address['data']['street_id']],
                ['contract_house_id', $address['data']['house_id']],
                ['contract_flat_id', $address['data']['id']],
                ['contract_number', trim($importData['nomer-dogovora'])],
            ]
        )->get();

        if (count($isContractIsset) > 0) {
            $contract = $isContractIsset[0];
        } else {
            $contract = Contract::create([
                'contract_on_user_id' => $user_id,
                'creator_user_id' => 1,
                'contract_region_id' => $address['data']['region_id'],
                'contract_city_id' => $address['data']['city_id'],
                'contract_street_id' => $address['data']['street_id'],
                'contract_house_id' => $address['data']['house_id'],
                'contract_flat_id' => $address['data']['id'],
                'contract_number' => trim($importData['nomer-dogovora']),
                'status' => 'В обработке',
                'contract_start_datetime' => $importData['data-zakl-dogovora']
            ]);

            $result['isNew'] = true;
        }

        $result['data'] = $contract->toArray();

        return $result;
    }

    /**
     * Add to database addreses group
     *
     * @param $importData
     */
    private function addingAddressesToDataBase($importData)
    {
        $result = [
            'data' => [],
            'isNew' => false
        ];

        $importData = parent::cleanFieldsToArray($importData, [
            "nomer-dogovora",
            "indeks",
            "oblast",
            "naselennyi-punkt",
            "ulica",
            "dom",
            "kvartira",
            "god-postroiki",
            "data-zakl-dogovora",
            "telefon",
            "fio",
            "data-to"
        ]);

        $region = Region::firstOrCreate(
            ['name' => $importData['oblast']]
        );

        $city = City::firstOrCreate(
            ['name' => $importData['naselennyi-punkt']],
            ['region_id' => $region->id]
        );

        // add one default street
        $defaultStreet = Street::firstOrCreate([
            'region_id' => $region->id,
            'city_id' => $city->id,
            'name' => '-'
        ]);

        // If no street, take default street value
        if (mb_strlen($importData['ulica']) <= 1) {
            $street = $defaultStreet;
        } else {
            $street = Street::firstOrCreate([
                'region_id' => $region->id,
                'city_id' => $city->id,
                'name' => $importData['ulica']
            ]);
        }

        // add one default house
        $defaultHouse = House::firstOrCreate([
            'region_id' => $region->id,
            'city_id' => $city->id,
            'street_id' => $street->id,
            'name' => '-'
        ]);

        // If no house, take default house value
        if (mb_strlen($importData['dom']) < 1) {
            $house = $defaultHouse;
        } else {
            $house = House::firstOrCreate([
                'region_id' => $region->id,
                'city_id' => $city->id,
                'street_id' => $street->id,
                'zip' => $importData['indeks'],
                'build_year' => intval($importData['god-postroiki']),
                'name' => $importData['dom']
            ]);
        }

        // add one default flat
        $defaultFlat = Flat::firstOrCreate([
            'region_id' => $region->id,
            'city_id' => $city->id,
            'street_id' => $street->id,
            'house_id' => $house->id,
            'name' => '-'
        ]);

        // If no flat, take default flat value
        if (mb_strlen($importData['kvartira']) < 1) {
            $flat = $defaultFlat;
        } else {
            $isFlatIsset = Flat::where(
                [
                    ['region_id', $region->id],
                    ['city_id', $city->id],
                    ['street_id', $street->id],
                    ['house_id', $house->id],
                    ['name', $importData['kvartira']]
                ]
            )->get();

            if (count($isFlatIsset) > 0) {
                $flat = $isFlatIsset[0];
            } else {
                $flat = Flat::create([
                    'region_id' => $region->id,
                    'city_id' => $city->id,
                    'street_id' => $street->id,
                    'house_id' => $house->id,
                    'name' => $importData['kvartira']
                ]);

                $result['isNew'] = true;
            }
        }

        $result['data'] = $flat->toArray();

        return $result;
    }
}
