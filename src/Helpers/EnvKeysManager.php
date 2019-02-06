<?php

namespace GeoSot\EnvEditor\Helpers;


use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Support\Collection;

class EnvKeysManager
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
    }

    /**
     * Check if key Exist in Current env
     *
     * @param string $key
     *
     * @return  bool
     * @throws EnvException
     */
    public function keyExists(string $key)
    {
        $env = $this->getEnvData();
        return !is_null($env->firstWhere('key', '==', $key));
    }


    /**
     * Add the  Key  on the Current Env
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     * @throws EnvException
     */
    public function getKey(string $key, $default = null)
    {
        return $this->getEnvData()->get($key, $default);
    }

    /**
     * Add the  Key  on the Current Env
     *
     * @param string $key
     * @param mixed  $value
     * @param array  $options
     *
     * @return  bool
     * @throws EnvException
     */
    public function addKey(string $key, $value, array $options = [])
    {
        if ($this->keyExists($key)) {
            throw new EnvException(__($this->package . '::exceptions.keyAlreadyExists', ['name' => $key]), 0);
        }
        $env = $this->getEnvData();
        $givenGroup = array_get($options, 'group', null);

        $groupIndex = $givenGroup ?? $env->pluck('group')->unique()->sort()->last() + 1;

        if (!$givenGroup and !$env->last()['separator']) {
            $separator = $this->getKeysSeparator($groupIndex, $env->count() + 1);
            $env->push($separator);
        }

        $lastSameGroupIndex = $env->last(function ($value, $key) use ($givenGroup) {
            return explode('_', $value['key'], 2)[0] == strtoupper($givenGroup) and !is_null($value['key']);
        });


        $keyArray = [
            'key'       => $key,
            'value'     => $value,
            'group'     => $groupIndex,
            'index'     => array_get($options, 'index', $env->search($lastSameGroupIndex) ? $env->search($lastSameGroupIndex) + 0.1 : $env->count() + 2),
            'separator' => false
        ];


        $env->push($keyArray);

        return $this->envEditor->getFileContentManager()->save($env);
    }

    /**
     * Deletes the Given Key form env
     *
     * @param string $keyToChange
     * @param mixed  $newValue
     *
     * @return  bool
     * @throws EnvException
     */
    public function editKey(string $keyToChange, $newValue)
    {
        if (!$this->keyExists($keyToChange)) {
            throw  new EnvException(__($this->package . '::exceptions.keyNotExists', ['name' => $keyToChange]), 11);
        }
        $env = $this->getEnvData();
        $newEnv = $env->map(function ($item) use ($keyToChange, $newValue) {
            if ($item['key'] == $keyToChange) {
                $item['value'] = $newValue;
            }
            return $item;
        });
        return $this->envEditor->getFileContentManager()->save($newEnv);
    }

    /**
     * Deletes the Given Key form env
     *
     * @param string $key
     *
     * @return  bool
     * @throws EnvException
     */
    public function deleteKey(string $key)
    {
        if (!$this->keyExists($key)) {
            throw  new EnvException(__($this->package . '::exceptions.keyNotExists', ['name' => $key]), 10);
        }
        $env = $this->getEnvData();
        $newEnv = $env->filter(function ($item) use ($key) {
            return $item['key'] !== $key;
        });

        return $this->envEditor->getFileContentManager()->save($newEnv);
    }


    /**
     * @param $groupIndex
     * @param $index
     *
     * @return array
     */
    public function getKeysSeparator($groupIndex, $index)
    {
        $groupArray = [
            'key'       => '',
            'value'     => '',
            'group'     => $groupIndex,
            'index'     => $index,
            'separator' => true
        ];
        return $groupArray;
    }

    /**
     * @return Collection
     * @throws EnvException
     */
    protected function getEnvData()
    {
        return $this->envEditor->getFileContentManager()->getParsedFileContent();
    }

}
