<?php

namespace GeoSot\EnvEditor\Facades;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @method static Collection getEnvFileContent(string $fileName = '')
 * @method static bool keyExists(string $key)
 * @method static mixed getKey(string $key, $default = null)
 * @method static bool addKey(string $key, $value, array $options = [])
 * @method static bool editKey(string $keyToChange, $newValue)
 * @method static bool deleteKey(string $key)
 * @method static Collection getAllBackUps()
 * @method static File upload(UploadedFile $uploadedFile, bool $replaceCurrentEnv)
 * @method static bool backUpCurrent()
 * @method static string getFilePath(string $fileName = '')
 * @method static bool deleteBackup(string $fileName)
 * @method static bool restoreBackUp(string $fileName)
 * @method static mixed config(string $key, $default = null)
 *
 * @see \GeoSot\EnvEditor\EnvEditor
 */
class EnvEditor extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \GeoSot\EnvEditor\EnvEditor::class;
    }
}
