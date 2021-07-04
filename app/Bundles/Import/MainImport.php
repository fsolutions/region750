<?php


namespace App\Bundles\Import;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class MainImport
{
    /**
     * Full path to upload import file.
     *
     * @var
     */
    private $fullPathToUploadFile;

    /**
     * Result to reed import file.
     *
     * @var
     */
    private $resultReedImportFile;

    /**
     * This result to import file.
     *
     * @var
     */
    private $result;

    /**
     * Path to import file.
     *
     * @var
     */
    private $pathToFile;

    /**
     * Get property result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get full path to upload file.
     *
     * @return mixed
     */
    protected function getFullPathToUploadFile()
    {
        return $this->fullPathToUploadFile;
    }

    /**
     * Set end result to import.
     *
     * @param $value
     */
    protected function setResult($value)
    {
        $this->result = $value;
    }

    /**
     * Result to reed import file.
     *
     * @return mixed
     */
    protected function getResultReedImportFile()
    {
        return $this->resultReedImportFile;
    }

    /**
     * Main logic function.
     *
     * @param $originalFile
     * @throws Exception
     */
    protected function import($originalFile)
    {
        $this->uploadImportFile($originalFile);

        $this->generateFullPathToUploadFile();

        $this->reedImportFile();
    }

    /**
     * Reed upload import file.
     *
     * @throws Exception
     */
    private function reedImportFile()
    {
        $reader = IOFactory::createReader('Xlsx');

        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($this->getFullPathToUploadFile());

        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true,  true);

        $this->resultReedImportFile = $sheetData;
    }

    /**
     * Validation import data.
     *
     * @param $company
     * @param $validate
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validationData($company, $validate)
    {
        return Validator::make($company, $validate);
    }

    /**
     * Upload import file.
     *
     * @param $originalFile
     */
    protected function uploadImportFile($originalFile)
    {
        $this->pathToFile = Storage::putFileAs('imports', $originalFile, $originalFile->getClientOriginalName());
    }

    /**
     * Get full path  to upload file.
     *
     * @return mixed
     */
    protected function generateFullPathToUploadFile()
    {
        // $this->fullPathToUploadFile = str_replace("/", "\\", storage_path('app/' . $this->pathToFile));
        $this->fullPathToUploadFile = storage_path('app/' . $this->pathToFile);
    }

    /**
     * Clean array.
     *
     * @param $array
     * @param $fields
     * @return array
     */
    protected function cleanFieldsToArray($array, $fields)
    {
        return array_filter(
            $array,
            function ($key) use ($array, $fields) {
                return in_array($key, $fields);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Delete upload file.
     */
    protected function deleteFile()
    {
        Storage::delete($this->pathToFile);
    }
}
