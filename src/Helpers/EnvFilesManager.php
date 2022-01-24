<?php

namespace GeoSot\EnvEditor\Helpers;

use Carbon\Carbon;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use GeoSot\EnvEditor\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\File;

class EnvFilesManager
{
    /**
     * @var EnvEditor
     */
    protected $envEditor;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor.
     *
     * @param  EnvEditor  $envEditor
     */
    public function __construct(EnvEditor $envEditor, Filesystem $filesystem)
    {
        $this->envEditor = $envEditor;
        $this->filesystem = $filesystem;
        $this->makeBackupsDirectory();
    }

    /**
     * Get all Backup Files.
     *
     * @return Collection
     */
    public function getAllBackUps(): Collection
    {
        $files = $this->filesystem->files($this->getBackupsDir());
        $collection = collect([]);
        foreach ($files as $file) {
            $data = [
                'real_name' => $file->getFilename(),
                'name' => $file->getFilename(),
                'created_at' => $file->getCTime(),
                'modified_at' => $file->getMTime(),
                'created_at_formatted' => Carbon::createFromTimestamp($file->getCTime())->format($this->envEditor->config('timeFormat')),
                'modified_at_formatted' => Carbon::createFromTimestamp($file->getMTime())->format($this->envEditor->config('timeFormat')),
                'content' => $file->getContents(),
                'path' => $file->getPath(),
                'parsed_data' => $this->envEditor->getFileContentManager()->getParsedFileContent($file->getFilename()),
            ];

            $collection->push($data);
        }

        return $collection->sortByDesc('created_at');
    }

    /**
     * Used to create a backup of the current .env.
     * Will be assigned with the current timestamp.
     *
     * @return bool
     * @throws EnvException
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
     * @param  string  $fileName
     *
     * @return bool
     * @throws EnvException
     */
    public function restoreBackup(string $fileName): bool
    {
        if (empty($fileName)) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.provideFileName'), 1);
        }
        $file = $this->getBackupsDir($fileName);

        return $this->filesystem->copy($file, $this->getFilePath());
    }

    /**
     * uploadBackup.
     *
     * @param  UploadedFile  $uploadedFile
     * @param  bool  $replaceCurrentEnv
     *
     * @return File
     */
    public function upload(UploadedFile $uploadedFile, bool $replaceCurrentEnv): File
    {
        return $replaceCurrentEnv ?
            $uploadedFile->move($this->getEnvDir(), $this->getEnvFileName()) :
            $uploadedFile->move($this->getBackupsDir(), $this->makeBackUpFileName());
    }

    /**
     * Delete the given backup-file.
     *
     * @param  string  $fileName
     *
     * @return bool
     * @throws EnvException
     */
    public function deleteBackup(string $fileName): bool
    {
        if (empty($fileName)) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.provideFileName'), 1);
        }
        $file = $this->getFilePath($fileName);

        return $this->filesystem->delete($file);
    }

    /**
     * Returns the full path of a backup file. If $fileName is empty return the path of the .env file.
     *
     * @param  string  $fileName
     *
     * @return string
     * @throws EnvException
     */
    public function getFilePath(string $fileName = ''): string
    {
        $path = (empty($fileName))
            ? $this->getEnvDir($this->getEnvFileName())
            : $this->getBackupsDir($fileName);

        if ($this->filesystem->exists($path)) {
            return $path;
        }

        throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.fileNotExists', ['name' => $path]), 0);
    }

    /**
     * Get the backup File Name.
     */
    protected function makeBackUpFileName(): string
    {
        return 'env_'.date('Y-m-d_His');
    }

    /**
     * Get the .env File Name.
     */
    protected function getEnvFileName(): string
    {
        return $this->envEditor->config('envFileName');
    }

    /**
     * @param  string  $path
     *
     * @return string
     */
    public function getBackupsDir(string $path = ''): string
    {
        return storage_path($this->envEditor->config('paths.backupDirectory')).($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * @param  string  $path
     *
     * @return string
     */
    public function getEnvDir(string $path = ''): string
    {
        return $this->envEditor->config('paths.env').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Checks if Backups directory Exists and creates it.
     */
    public function makeBackupsDirectory(): void
    {
        $path = $this->getBackupsDir();
        if (! $this->filesystem->exists($path)) {
            $this->filesystem->makeDirectory($path, 0755, true, true);
        }
    }
}
