<?php

namespace GeoSot\EnvEditor\Helpers;

use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use GeoSot\EnvEditor\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EnvKeysManager
{
    protected EnvEditor $envEditor;

    public function __construct(EnvEditor $envEditor)
    {
        $this->envEditor = $envEditor;
    }

    /**
     * Check if key Exist in Current env.
     */
    public function has(string $key): bool
    {
        return $this->getFirst($key) instanceof EntryObj;
    }

    /**
     * Add the  Key  on the Current Env.
     */
    public function get(string $key, mixed $default = null): float|bool|int|string|null
    {
        $result = $this->getFirst($key);

        return $result ? $result->getValue($default) : $default;
    }

    /**
     * Add the  Key  on the Current Env.
     *
     * @param array<string, int|string> $options
     *
     * @throws EnvException
     */
    public function add(string $key, mixed $value, array $options = []): bool
    {
        if ($this->has($key)) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.keyAlreadyExists', ['name' => $key]), 0);
        }
        $env = $this->getEnvData();
        $givenGroup = Arr::get($options, 'group', null);

        $groupIndex = $givenGroup ?? $env->pluck('group')->unique()->sort()->last() + 1;

        if (!$givenGroup && !$env->last()->isSeparator()) {
            $separator = EntryObj::makeKeysSeparator((int) $groupIndex, $env->count() + 1);
            $env->push($separator);
        }

        $lastSameGroupIndex = $env->last(function (EntryObj $entry, $key) use ($givenGroup) {
            return explode('_', $entry->key, 2)[0] == strtoupper($givenGroup) && null !== $entry->key;
        });

        $index = Arr::get(
            $options,
            'index',
            $env->search($lastSameGroupIndex) ? $env->search($lastSameGroupIndex) + 0.1 : $env->count() + 2
        );

        $entryObj = new EntryObj($key, $value, $groupIndex, $index);

        $env->push($entryObj);

        return $this->envEditor->getFileContentManager()->save($env);
    }

    /**
     * Deletes the Given Key form env.
     *
     * @throws EnvException
     */
    public function edit(string $keyToChange, mixed $newValue = null): bool
    {
        if (!$this->has($keyToChange)) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.keyNotExists', ['name' => $keyToChange]), 11);
        }
        $env = $this->getEnvData();
        $newEnv = $env->map(function (EntryObj $entry) use ($keyToChange, $newValue) {
            if ($entry->key == $keyToChange) {
                $entry->setValue($newValue);
            }

            return $entry;
        });

        return $this->envEditor->getFileContentManager()->save($newEnv);
    }

    /**
     * Deletes the Given Key form env.
     *
     * @throws EnvException
     */
    public function delete(string $key): bool
    {
        if (!$this->has($key)) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.keyNotExists', ['name' => $key]), 10);
        }
        $env = $this->getEnvData();
        $newEnv = $env->filter(fn (EntryObj $entry) => $entry->key !== $key);

        return $this->envEditor->getFileContentManager()->save($newEnv);
    }

    /**
     * @return Collection<int, EntryObj>
     */
    protected function getEnvData(): Collection
    {
        return $this->envEditor->getFileContentManager()->getParsedFileContent();
    }

    protected function getFirst(string $key): ?EntryObj
    {
        return $this->getEnvData()
            ->reject(fn (EntryObj $entry) => $entry->isSeparator())
            ->firstWhere('key', '==', $key);
    }
}
