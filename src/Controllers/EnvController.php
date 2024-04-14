<?php

namespace GeoSot\EnvEditor\Controllers;

use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EnvController extends BaseController
{
    public function __construct(
        protected EnvEditor $envEditor
    ) {
    }

    /**
     * Display main view with the Collection of current .env values.
     *
     * @return JsonResponse|View
     */
    public function index(Request $request)
    {
        $envFileContent = $this->envEditor->getEnvFileContent();
        if ($request->wantsJson()) {
            return $this->returnGenericResponse(true, ['items' => $envFileContent]);
        }

        return view(ServiceProvider::PACKAGE.'::index', ['envValues' => $envFileContent]);
    }

    /**
     * Add a new key on current .env file.
     */
    public function addKey(Request $request): JsonResponse
    {
        $result = $this->envEditor->addKey(
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
        $result = $this->envEditor->editKey($request->input('key'), $request->input('value'));

        return $this->returnGenericResponse($result, [], 'keyWasEdited', $request->input('key'));
    }

    /**
     * Delete a key from current .env file.
     */
    public function deleteKey(Request $request): JsonResponse
    {
        $result = $this->envEditor->deleteKey($request->input('key'));

        return $this->returnGenericResponse($result, [], 'keyWasDeleted', $request->input('key'));
    }

    /**
     * Display a listing of the resource.
     */
    public function getBackupFiles(Request $request): View|JsonResponse
    {
        $allBackUps = $this->envEditor->getAllBackUps()->toArray();
        if ($request->wantsJson()) {
            return $this->returnGenericResponse(true, ['items' => $allBackUps]);
        }

        return view(ServiceProvider::PACKAGE.'::index', ['backUpFiles' => $allBackUps]);
    }

    /**
     * Create BackUp of .env File.
     */
    public function createBackup(): JsonResponse
    {
        $result = $this->envEditor->backUpCurrent();

        return $this->returnGenericResponse($result, [], 'backupWasCreated');
    }

    /**
     * Restore Backup file.
     */
    public function restoreBackup(string $filename): JsonResponse
    {
        $result = $this->envEditor->restoreBackUp($filename);

        return $this->returnGenericResponse($result, [], 'fileWasRestored', $filename);
    }

    /**
     * Delete Backup file.
     */
    public function destroyBackup(string $filename): JsonResponse
    {
        $result = $this->envEditor->deleteBackup($filename);

        return $this->returnGenericResponse($result, [], 'fileWasDeleted', $filename);
    }

    /**
     * Get Files As Download.
     */
    public function download(string $filename = ''): BinaryFileResponse
    {
        $path = $this->envEditor->getFilePath($filename);

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

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->file('file');
        $file = $this->envEditor->upload($uploadedFile, $replaceCurrentEnv);
        $successMsg = ($replaceCurrentEnv) ? 'currentEnvWasReplacedByTheUploadedFile' : 'uploadedFileSavedAsBackup';

        return $this->returnGenericResponse(true, [], $successMsg, $file->getFilename());
    }

    /**
     * Clears Config cache to get new values.
     */
    public function clearConfigCache(): JsonResponse
    {
        Artisan::call('config:clear');

        return $this->returnGenericResponse(true, ['message' => Artisan::output()]);
    }

    /**
     * Generic ajax response.
     *
     * @param array<string, mixed> $data
     */
    protected function returnGenericResponse(
        bool $success,
        array $data = [],
        ?string $translationWord = null,
        string $keyName = ''
    ): JsonResponse {
        if ($translationWord && $success) {
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
