<?php


namespace App\Bundles\Documents;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Madnest\Madzipper\Madzipper;

class DocumentsDownload
{
    /**
     * Multi download documents.
     *
     * @param $model
     * @param $model_id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|void
     * @throws \Exception
     */
    public function downloadDocuments($model, $model_id) {

        $field = null;
        switch ($model) {
            case 'tickets':
                $field = 'ticket_id';
                break;
            case 'companies':
                $field = 'company_id';
                break;
            case 'orders':
                $field = 'order_id';
                break;
            case 'users':
                $field = 'creator_user_id';
                break;
        }

        if($field != null) {
            $documents = Document::where($field, $model_id)
                ->where('folder', 1)
                ->get()
                ->toArray();
        }

        if(!empty($documents)) {
            $zipper = new Madzipper();
            $zipper->make("tmp\\".time().".zip")->folder($model);
            foreach ($documents as $document) {
                $extensionFile = strrchr($document['path'], ".");
                $nameFile = $document['name'] . '-' . $document['id'] . $extensionFile;
                $pathToFIle = Storage::path($document['path']);
                $zipper->add($pathToFIle, $nameFile);
            }
            $filPath = $zipper->getFilePath();
            $zipper->close();
            return response()->download(public_path($filPath))->deleteFileAfterSend(true);
        }

        return abort('404');
    }

    /**
     * Download documents.
     *
     * @param $model
     * @param $model_id
     * @param $name
     * @return mixed
     */
    public function downloadDocument($model, $model_id, $name) {
        $path = "uploads/files/$model/$model_id/$name";
        $document = Document::where('path', '=', $path)->firstOrFail();
        $extension = strrchr($path, ".");
        $name = $document->name . $extension;
        return Storage::download($path, $name);
    }

    /**
     * Download subservice document.
     *
     * @param $model
     * @param $model_id
     * @param $subservice
     * @param $name
     * @return mixed
     */
    public function downloadSubserviceDocument($model, $model_id, $subservice, $name) {
        $path = "uploads/files/$model/$model_id/$subservice/$name";
        $document = Document::where('path', '=', $path)->firstOrFail();
        $extension = strrchr($path, ".");
        $name = $document->name . $extension;
        return Storage::download($path, $name);
    }
}
