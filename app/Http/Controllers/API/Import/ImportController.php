<?php

namespace App\Http\Controllers\API\Import;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use App\Bundles\Import\AddressesAndUsersImport;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        $originalFile = $request->file('file');

        $import = null;

        try {
            $import = new AddressesAndUsersImport($originalFile);
        } catch (Exception $e) {
        }

        return $import->getResult();
    }
}
