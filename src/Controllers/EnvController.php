<?php

namespace GeoSot\EnvEditor\Controllers;

use GeoSot\EnvEditor\Facades\EnvEditor;
use GeoSot\EnvEditor\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EnvController extends BaseController
{
    /**
     * Display main view with the Collection of current .env values.
     *
     * @return JsonResponse|View
     */
    public function index()
    {
        $envValues = EnvEditor::getEnvFileContent();
        if (request()->wantsJson()) {
            return $this->returnGenericResponse(true, ['items' => $envValues]);
        }

        return view(ServiceProvider::PACKAGE.'::index', compact('envValues'));
    }

    /**
     * Add a new key on current .env file.
     */
    public function addKey(Request $request): JsonResponse
    {
        $result = EnvEditor::addKey(
            $request->input('key'),
            $request->input('value'),
            $request->except(['key', 'value'])
        );

        return $this->returnGenericResponse($result, [], 'keyWasAdded', $request->input('key'));
    }

    /**
     * Edit a key of current .env file.
     */
    public function editKey(Request $request): JsonResponse
    {
        $result = EnvEditor::editKey($request->input('key'), $request->input('value'));

        return $this->returnGenericResponse($result, [], 'keyWasEdited', $request->input('key'));
    }

    /**
     * Delete a key from current .env file.
     */
    public function deleteKey(Request $request): JsonResponse
    {
        $result = EnvEditor::deleteKey($request->input('key'));

        return $this->returnGenericResponse($result, [], 'keyWasDeleted', $request->input('key'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|JsonResponse
     */
    public function getBackupFiles()
    {
        $backUpFiles = EnvEditor::getAllBackUps();
        if (request()->wantsJson()) {
            return $this->returnGenericResponse(true, ['items' => $backUpFiles]);
        }

        return view(ServiceProvider::PACKAGE.'::index', compact('backUpFiles'));
    }

    /**
     * Create BackUp of .env File.
     */
    public function createBackup(): JsonResponse
    {
        $result = EnvEditor::backUpCurrent();

        return $this->returnGenericResponse($result, [], 'backupWasCreated');
    }

    /**
     * Restore Backup file.
     */
    public function restoreBackup(string $filename): JsonResponse
    {
        $result = EnvEditor::restoreBackUp($filename);

        return $this->returnGenericResponse($result, [], 'fileWasRestored', $filename);
    }

    /**
     * Delete Backup file.
     */
    public function destroyBackup(string $filename): JsonResponse
    {
        $result = EnvEditor::deleteBackup($filename);

        return $this->returnGenericResponse($result, [], 'fileWasDeleted', $filename);
    }

    /**
     * Get Files As Download.
     */
    public function download(string $filename = ''): BinaryFileResponse
    {
        $path = EnvEditor::getFilePath($filename);

        return response()->download($path);
    }

    /**
     * Upload File As BackUp or replace Current .Env.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/octet-stream,text/plain|mimes:txt,text,',
        ]);
        $replaceCurrentEnv = filter_var($request->input('replace_current'), FILTER_VALIDATE_BOOLEAN);

        $file = EnvEditor::upload($request->file('file'), $replaceCurrentEnv);
        $successMsg = ($replaceCurrentEnv) ? 'currentEnvWasReplacedByTheUploadedFile' : 'uploadedFileSavedAsBackup';

        return $this->returnGenericResponse(true, [], $successMsg, $file->getFilename());
    }

    /**
     * Clears Config cache to get new values.
     */
    public function clearConfigCache(): JsonResponse
    {
        Artisan::call('config:clear');

        return $this->returnGenericResponse(true, ['message'=> Artisan::output()]);
    }

    /**
     * Generic ajax response.
     *
     * @param  bool  $success
     * @param  array<string, mixed>  $data
     * @param  string  $translationWord
     * @param  string  $keyName
     *
     * @return JsonResponse
     */
    protected function returnGenericResponse(
        bool $success,
        array $data = [],
        string $translationWord = '',
        string $keyName = ''
    ): JsonResponse {
        if (! empty($translationWord) && $success) {
            $data = array_merge($data, [
                'message' => __(
                    ServiceProvider::TRANSLATE_PREFIX."controllerMessages.$translationWord",
                    ['name' => $keyName]
                ),
            ]);
        }

        return response()->json(array_merge($data, [
            'success' => $success,
        ]), $success ? 200 : 400);
    }
}
