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
    protected EnvEditor $envEditor;

    protected Filesystem $filesystem;

    public function __construct(EnvEditor $envEditor, Filesystem $filesystem)
    {
        $this->envEditor = $envEditor;
        $this->filesystem = $filesystem;
        $this->makeBackupsDirectory();
    }

    /**
     * Get all Backup Files.
     *
     * @return Collection<int, array{real_name:string, name:string, created_at:int, modified_at:int, created_at_formatted:string, modified_at_formatted:string, content:string, path:string,parsed_data:Collection<int, EntryObj>}>
     */
    public function getAllBackUps(): Collection
    {
        $files = $this->filesystem->files($this->getBackupsDir());
        /** @var Collection<int, array{real_name:string, name:string, created_at:int, modified_at:int, created_at_formatted:string, modified_at_formatted:string, content:string, path:string,parsed_data:Collection<int, EntryObj>}> $collection */
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

    public function getBackupsDir(string $path = ''): string
    {
        return $this->envEditor->config('paths.backupDirectory').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

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
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->makeDirectory($path, 0755, true, true);
        }
    }
}
