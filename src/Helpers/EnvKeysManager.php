<?php

namespace GeoSot\EnvEditor\Helpers;

use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use GeoSot\EnvEditor\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EnvKeysManager
{
    /**
     * @var EnvEditor
     */
    protected $envEditor;

    /**
     * Constructor.
     *
     * @param  EnvEditor  $envEditor
     */
    public function __construct(EnvEditor $envEditor)
    {
        $this->envEditor = $envEditor;
    }

    /**
     * Check if key Exist in Current env.
     *
     * @param  string  $key
     *
     * @return bool
     * @throws EnvException
     */
    public function keyExists(string $key): bool
    {
        $env = $this->getEnvData();

        return $env->firstWhere('key', '==', $key) !== null;
    }

    /**
     * Add the  Key  on the Current Env.
     *
     * @param  string  $key
     * @param  mixed  $default
     *
     * @return string|float|int|bool|null
     * @throws EnvException
     */
    public function getKey(string $key, $default = null)
    {
        $result = $this->getEnvData()->firstWhere('key', '==', $key);

        return $result ? $result['value'] ?: $default : $default;
    }

    /**
     * Add the  Key  on the Current Env.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  array<string, int|string>  $options
     *
     * @return bool
     * @throws EnvException
     */
    public function addKey(string $key, $value, array $options = []): bool
    {
        if ($this->keyExists($key)) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.keyAlreadyExists', ['name' => $key]), 0);
        }
        $env = $this->getEnvData();
        $givenGroup = Arr::get($options, 'group', null);

        $groupIndex = $givenGroup ?? $env->pluck('group')->unique()->sort()->last() + 1;

        if (! $givenGroup && ! $env->last()['separator']) {
            $separator = $this->getKeysSeparator((int) $groupIndex, $env->count() + 1);
            $env->push($separator);
        }

        $lastSameGroupIndex = $env->last(function ($value, $key) use ($givenGroup) {
            return explode('_', $value['key'], 2)[0] == strtoupper($givenGroup) && $value['key'] !== null;
        });

        $keyArray = [
            'key' => $key,
            'value' => $value,
            'group' => $groupIndex,
            'index' => Arr::get(
                $options,
                'index',
                $env->search($lastSameGroupIndex) ? $env->search($lastSameGroupIndex) + 0.1 : $env->count() + 2
            ),
            'separator' => false,
        ];

        $env->push($keyArray);

        return $this->envEditor->getFileContentManager()->save($env);
    }

    /**
     * Deletes the Given Key form env.
     *
     * @param  string  $keyToChange
     * @param  mixed  $newValue
     *
     * @return bool
     * @throws EnvException
     */
    public function editKey(string $keyToChange, $newValue): bool
    {
        if (! $this->keyExists($keyToChange)) {
            throw  new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.keyNotExists', ['name' => $keyToChange]), 11);
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
     * Deletes the Given Key form env.
     *
     * @param  string  $key
     *
     * @return bool
     * @throws EnvException
     */
    public function deleteKey(string $key): bool
    {
        if (! $this->keyExists($key)) {
            throw  new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.keyNotExists', ['name' => $key]), 10);
        }
        $env = $this->getEnvData();
        $newEnv = $env->filter(function ($item) use ($key) {
            return $item['key'] !== $key;
        });

        return $this->envEditor->getFileContentManager()->save($newEnv);
    }

    /**
     * @param  int  $groupIndex
     * @param  int  $index
     *
     * @return array<string, mixed>
     */
    public function getKeysSeparator(int $groupIndex, int $index): array
    {
        return [
            'key' => '',
            'value' => '',
            'group' => $groupIndex,
            'index' => $index,
            'separator' => true,
        ];
    }

    /**
     * @return Collection<int, array{key:string, value: int|string, group:int, index:int , separator:bool}>
     * @throws EnvException
     */
    protected function getEnvData(): Collection
    {
        return $this->envEditor->getFileContentManager()->getParsedFileContent();
    }
}
