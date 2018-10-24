<?php


namespace GeoSot\EnvEditor\Helpers;

use Carbon\Carbon;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class EnvFilesManager
{

    protected $envEditor;
    protected $package = 'env-editor';


    /**
     * Constructor
     *
     * @param  EnvEditor $envEditor
     */
    public function __construct(EnvEditor $envEditor)
    {
        $this->envEditor = $envEditor;
        $this->makeBackupsDirectory();
    }

    /**
     * Get all Backup Files
     *
     * @return  Collection
     * @throws EnvException
     */
    public function getAllBackUps()
    {

        $files = File::files($this->getBackupsDir() . '\\.');
        $collection = collect([]);
        foreach ($files as $file) {

            $data = [
                'real_name' => $file->getFilename(),
                'name' => $file->getFilename(),
                'crated_at' => $file->getCTime(),
                'modified_at' => $file->getMTime(),
                'crated_at_formatted' => Carbon::createFromTimestamp($file->getCTime())->format($this->envEditor->config('timeFormat')),
                'modified_at_formatted' => Carbon::createFromTimestamp($file->getMTime())->format($this->envEditor->config('timeFormat')),
                'content' => $file->getContents(),
                'path' => $file->getPath(),
                'parsed_data' => $this->envEditor->getFileContentManager()->getParsedFileContent( $file->getFilename())
            ];

            $collection->push($data);
        }

        $filtered = $collection->sortByDesc('created_at');

        return $filtered;
    }

    /**
     * Used to create a backup of the current .env.
     * Will be assigned with the current timestamp.
     *
     * @return bool
     * @throws EnvException
     */
    public function backUpCurrentEnv()
    {
        return File::copy(
            $this->getFilePath(),
            $this->getBackupsDir(true) . $this->makeBackUpFileName()
        );
    }


    /**
     * Restore  the given backup-file
     * @param  string $fileName
     *
     * @return  bool
     * @throws EnvException
     */
    public function restoreBackup(string $fileName)
    {
        if (empty($fileName)){
            throw new EnvException(__($this->package . '::exceptions.provideFileName'), 1);
        }
        $file = $this->getFilePath($fileName);
        return File::copy($file, $this->getFilePath());
    }

    /**
     * uploadBackup
     *
     * @param  UploadedFile $uploadedFile
     * @param bool          $replaceCurrentEnv
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function upload(UploadedFile $uploadedFile, bool $replaceCurrentEnv)
    {
        return $replaceCurrentEnv ?
            $uploadedFile->move($this->getEnvDir(), $this->getEnvFileName()) :
            $uploadedFile->move($this->getBackupsDir(), $this->makeBackUpFileName());
    }

    /**
     * Delete the given backup-file
     * @param  string $fileName
     *
     * @return  bool
     * @throws EnvException
     */
    public function deleteBackup(string $fileName )
    {
        if (empty($fileName)){
            throw new EnvException(__($this->package . '::exceptions.provideFileName'), 1);
        }
        $file = $this->getFilePath($fileName);

        return File::delete($file);

    }

    /**
     * Returns the full path of a backup file. If $fileName is empty return the path of the .env file
     * @param  string $fileName
     *
     * @return  bool
     * @throws EnvException
     */
    public function getFilePath(string $fileName = '')
    {
        $path = (empty($fileName)) ?
            $this->getEnvDir(true) . $this->getEnvFileName() :
            $this->getBackupsDir(true) . $fileName;

        if (File::exists($path)) {
            return $path;
        } else {
            throw new EnvException(__($this->package . '::exceptions.fileNotExists', ['name' => $path]), 0);
        }

    }


    /**
     * Get the backup File Name
     * @return string
     */
    protected function makeBackUpFileName()
    {
        return 'env_' . date('Y-m-d_His');
    }

    /**
     * Get the .env File Name
     * @return string
     */
    protected function getEnvFileName()
    {
        return $this->envEditor->config('envFileName');
    }

    public function getBackupsDir(bool $appendSlash = false)
    {
        return storage_path($this->envEditor->config('paths.backupDirectory')) . ($appendSlash ? '\\' : '');
    }

    public function getEnvDir(bool $appendSlash = false)
    {
        return $this->envEditor->config('paths.env') . ($appendSlash ? '\\' : '');
    }

    /**
     *Checks if Backups directory Exists and creates it
     * @return string
     */
    public function makeBackupsDirectory()
    {
        $path = $this->getBackupsDir();
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
    }


}