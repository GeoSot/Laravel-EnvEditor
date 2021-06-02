<?php

namespace GeoSot\EnvEditor\Helpers;

use Carbon\Carbon;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class EnvFilesManager
{
    protected $envEditor;
    protected $package = 'env-editor';
    protected $filesystem;

    /**
     * Constructor.
     *
     * @param EnvEditor $envEditor
     */
    public function __construct(EnvEditor $envEditor)
    {
        $this->envEditor = $envEditor;
        $this->filesystem = new Filesystem();
        $this->makeBackupsDirectory();
    }

    /**
     * Get all Backup Files.
     *
     * @throws EnvException
     *
     * @return Collection
     */
    public function getAllBackUps(): Collection
    {
        $files = $this->filesystem->files($this->getBackupsDir());
        $collection = collect([]);
        foreach ($files as $file) {
            $data = [
                'real_name'             => $file->getFilename(),
                'name'                  => $file->getFilename(),
                'crated_at'             => $file->getCTime(),
                'modified_at'           => $file->getMTime(),
                'created_at_formatted'  => Carbon::createFromTimestamp($file->getCTime())->format($this->envEditor->config('timeFormat')),
                'modified_at_formatted' => Carbon::createFromTimestamp($file->getMTime())->format($this->envEditor->config('timeFormat')),
                'content'               => $file->getContents(),
                'path'                  => $file->getPath(),
                'parsed_data'           => $this->envEditor->getFileContentManager()->getParsedFileContent($file->getFilename()),
            ];

            $collection->push($data);
        }

        return $collection->sortByDesc('created_at');
    }

    /**
     * Used to create a backup of the current .env.
     * Will be assigned with the current timestamp.
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function backUpCurrentEnv(): bool
    {
        return $this->filesystem->copy(
            $this->getFilePath(),
            $this->getBackupsDir($this->makeBackUpFileName())
        );
    }

    /**
     * Restore  the given backup-file.
     *
     * @param string $fileName
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function restoreBackup(string $fileName): bool
    {
        if (empty($fileName)) {
            throw new EnvException(__($this->package.'::env-editor.exceptions.provideFileName'), 1);
        }
        $file = $this->getBackupsDir($fileName);

        return $this->filesystem->copy($file, $this->getFilePath());
    }

    /**
     * uploadBackup.
     *
     * @param UploadedFile $uploadedFile
     * @param bool         $replaceCurrentEnv
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function upload(UploadedFile $uploadedFile, bool $replaceCurrentEnv): bool
    {
        return $replaceCurrentEnv ?
            $uploadedFile->move($this->getEnvDir(), $this->getEnvFileName()) :
            $uploadedFile->move($this->getBackupsDir(), $this->makeBackUpFileName());
    }

    /**
     * Delete the given backup-file.
     *
     * @param string $fileName
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function deleteBackup(string $fileName): bool
    {
        if (empty($fileName)) {
            throw new EnvException(__($this->package.'::env-editor.exceptions.provideFileName'), 1);
        }
        $file = $this->getFilePath($fileName);

        return $this->filesystem->delete($file);
    }

    /**
     * Returns the full path of a backup file. If $fileName is empty return the path of the .env file.
     *
     * @param string $fileName
     *
     * @throws EnvException
     *
     * @return string
     */
    public function getFilePath(string $fileName = ''): string
    {
        $path = (empty($fileName))
            ? $this->getEnvDir($this->getEnvFileName())
            : $this->getBackupsDir($fileName);

        if ($this->filesystem->exists($path)) {
            return $path;
        }

        throw new EnvException(__($this->package.'::env-editor.exceptions.fileNotExists', ['name' => $path]), 0);
    }

    /**
     * Get the backup File Name.
     *
     * @return string
     */
    protected function makeBackUpFileName(): string
    {
        return 'env_'.date('Y-m-d_His');
    }

    /**
     * Get the .env File Name.
     *
     * @return string
     */
    protected function getEnvFileName(): string
    {
        return $this->envEditor->config('envFileName');
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getBackupsDir(string $path = ''): string
    {
        return storage_path($this->envEditor->config('paths.backupDirectory')).($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getEnvDir(string $path = ''): string
    {
        return $this->envEditor->config('paths.env').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     *Checks if Backups directory Exists and creates it.
     */
    public function makeBackupsDirectory(): void
    {
        $path = $this->getBackupsDir();
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->makeDirectory($path, 0755, true, true);
        }
    }
}
