<?php

namespace GeoSot\EnvEditor\Controllers;

use App\Http\Controllers\Controller;
use GeoSot\EnvEditor\Facades\EnvEditor;
use Illuminate\Http\Request;

class EnvController extends Controller
{
    protected $package = 'env-editor';


    /**
     * Display main view with the Collection of current .env values
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $envValues = EnvEditor::getEnvFileContent();
        if (request()->wantsJson()) {
            return $this->returnGenericResponse(true, ['items' => $envValues]);
        }
        return view($this->package . '::index', compact('envValues'));
    }

    /**
     * Add a new key on current .env file
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function addKey(Request $request)
    {
        $result = EnvEditor::addKey($request->input('key'), $request->input('value'), $request->except(['key', 'value']));
        return $this->returnGenericResponse($result, [], 'keyWasAdded', $request->input('key'));
    }

    /**
     * Edit a key of current .env file
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function editKey(Request $request)
    {
        $result = EnvEditor::editKey($request->input('key'), $request->input('value'));
        return $this->returnGenericResponse($result, [], 'keyWasEdited', $request->input('key'));
    }

    /**
     * Delete a key from current .env file
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteKey(Request $request)
    {
        $result = EnvEditor::deleteKey($request->input('key'));
        return $this->returnGenericResponse($result, [], 'keyWasDeleted', $request->input('key'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBackupFiles()
    {
        $backUpFiles = EnvEditor::getAllBackUps();
        if (request()->wantsJson()) {
            return $this->returnGenericResponse(true, ['items' => $backUpFiles]);
        }
        return view($this->package . '::index', compact('backUpFiles'));
    }

    /**
     * Create BackUp of .env File
     *
     * @return \Illuminate\Http\Response
     */
    public function createBackup()
    {
        $result = EnvEditor::backUpCurrent();
        return $this->returnGenericResponse($result, [], 'backupWasCreated');
    }


    /**
     * Restore Backup file
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function restoreBackup(string $filename)
    {
        $result = EnvEditor::restoreBackUp($filename);
        return $this->returnGenericResponse($result, [], 'fileWasRestored', $filename);
    }


    /**
     * Delete Backup file
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function destroyBackup(string $filename)
    {
        $result = EnvEditor::deleteBackup($filename);

        return $this->returnGenericResponse($result, [], 'fileWasDeleted', $filename);
    }

    /**
     * Get Files As Download
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function download(string $filename = '')
    {
        $path = EnvEditor::getFilePath($filename);
        return response()->download($path);
    }


    /**
     * Upload File As BackUp or replace Current .Env
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/octet-stream,text/plain|mimes:txt,text,'
        ]);
        $replaceCurrentEnv = filter_var($request->input('replace_current'), FILTER_VALIDATE_BOOLEAN);

        $file = EnvEditor::upload($request->file('file'), $replaceCurrentEnv);
        $successMsg = ($replaceCurrentEnv) ? 'currentEnvWasReplacedByTheUploadedFile' : 'uploadedFileSavedAsBackup';
        return $this->returnGenericResponse(true, [], $successMsg, $file->getFilename());
    }

    /**
     * Generic ajax response
     *
     * @param  bool   $success
     * @param  array  $data
     * @param  string $translationWord
     * @param  string $keyName
     *
     * @return \Illuminate\Http\Response
     */
    protected function returnGenericResponse(bool $success, array $data = [], string $translationWord = '', string $keyName = '')
    {
        if (!empty($translationWord) and $success) {
            $data = array_merge($data, [
                'message' => __($this->package . "::env-editor.controllerMessages.$translationWord", ['name' => $keyName])
            ]);
        }
        return response()
            ->json(array_merge($data, [
                'success' => $success,
            ]));
    }

}
