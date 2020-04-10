<?php

namespace GeoSot\EnvEditor;

use GeoSot\EnvEditor\Exceptions\EnvException;
use GeoSot\EnvEditor\Helpers\EnvFileContentManager;
use GeoSot\EnvEditor\Helpers\EnvFilesManager;
use GeoSot\EnvEditor\Helpers\EnvKeysManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/**
 * Class Settings.
 */
class EnvEditor
{
    protected $config;
    protected $keysManager;
    protected $filesManager;
    protected $fileContentManager;
    protected $package = 'env-editor';

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->keysManager = new EnvKeysManager($this);
        $this->filesManager = new EnvFilesManager($this);
        $this->fileContentManager = new EnvFileContentManager($this);
    }

    /**
     * Parse the .env Contents.
     *
     * @param string $fileName
     *
     * @throws EnvException
     *
     * @return Collection
     */
    public function getEnvFileContent(string $fileName = '')
    {
        return $this->getFileContentManager()->getParsedFileContent($fileName);
    }

    /**
     * Check if key Exist in Current env.
     *
     * @param string $key
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function keyExists(string $key)
    {
        return $this->getKeysManager()->keyExists($key);
    }

    /**
     * Add the  Key  on the Current Env.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @throws EnvException
     *
     * @return mixed
     */
    public function getKey(string $key, $default = null)
    {
        return $this->getKeysManager()->getKey($key, $default);
    }

    /**
     * Add the  Key  on the Current Env.
     *
     * @param string $key
     * @param mixed  $value
     * @param array  $options
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function addKey(string $key, $value, array $options = [])
    {
        return $this->getKeysManager()->addKey($key, $value, $options);
    }

    /**
     * Edits the Given Key  env.
     *
     * @param string $keyToChange
     * @param mixed  $newValue
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function editKey(string $keyToChange, $newValue)
    {
        return $this->getKeysManager()->editKey($keyToChange, $newValue);
    }

    /**
     * Deletes the Given Key form env.
     *
     * @param string $key
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function deleteKey(string $key)
    {
        return $this->getKeysManager()->deleteKey($key);
    }

    /**
     * Get all Backup Files.
     *
     * @throws EnvException
     *
     * @return Collection
     */
    public function getAllBackUps()
    {
        return $this->getFilesManager()->getAllBackUps();
    }

    /**
     * uploadBackup.
     *
     * @param UploadedFile $uploadedFile
     * @param bool         $replaceCurrentEnv
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function upload(UploadedFile $uploadedFile, bool $replaceCurrentEnv)
    {
        return $this->getFilesManager()->upload($uploadedFile, $replaceCurrentEnv);
    }

    /**
     * Used to create a backup of the current .env.
     * Will be assigned with the current timestamp.
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function backUpCurrent()
    {
        return $this->getFilesManager()->backUpCurrentEnv();
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
    public function getFilePath(string $fileName = '')
    {
        return $this->getFilesManager()->getFilePath($fileName);
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
    public function deleteBackup(string $fileName)
    {
        return $this->getFilesManager()->deleteBackup($fileName);
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
    public function restoreBackUp(string $fileName)
    {
        return $this->getFilesManager()->restoreBackup($fileName);
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function config(string $key, $default = null)
    {
        return config($this->package.'.'.$key, $default);
    }

    /**
     * @return EnvKeysManager
     */
    public function getKeysManager()
    {
        return $this->keysManager;
    }

    /**
     * @return EnvFilesManager
     */
    public function getFilesManager()
    {
        return $this->filesManager;
    }

    /**
     * @return EnvFileContentManager
     */
    public function getFileContentManager()
    {
        return $this->fileContentManager;
    }
}
