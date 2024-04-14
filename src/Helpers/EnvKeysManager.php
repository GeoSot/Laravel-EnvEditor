<?php

namespace GeoSot\EnvEditor\Helpers;

use GeoSot\EnvEditor\Dto\EntryObj;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use GeoSot\EnvEditor\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EnvKeysManager
{
    public function __construct(protected EnvEditor $envEditor)
    {
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

        return $result instanceof EntryObj ? $result->getValue($default) : $default;
    }

    /**
     * Add the  Key  on the Current Env.
     *
     * @param array{index?: int|string|null, group?: int|string|null} $options
     *
     * @throws EnvException
     */
    public function add(string $key, mixed $value, array $options = []): bool
    {
        if ($this->has($key)) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.keyAlreadyExists', ['name' => $key]), 0);
        }
        $envData = $this->getEnvData();
        $givenGroup = Arr::get($options, 'group', null);

        $groupIndex = $givenGroup ?? $envData->pluck('group')->unique()->sort()->last() + 1;

        if (!$givenGroup && !$envData->last()->isSeparator()) {
            $separator = EntryObj::makeKeysSeparator((int) $groupIndex, $envData->count() + 1);
            $envData->push($separator);
        }

        $lastSameGroupEntry = $envData->last(fn (EntryObj $entryObj): bool => explode('_', $entryObj->key, 2)[0] === strtoupper((string) $givenGroup) && $entryObj->isSeparator());

        $index = Arr::get(
            $options,
            'index',
            $lastSameGroupEntry ? $lastSameGroupEntry->index + 0.1 : $envData->count() + 2
        );

        $entryObj = new EntryObj($key, $value, $groupIndex, $index);

        $envData->push($entryObj);

        return $this->envEditor->getFileContentManager()->save($envData);
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
        $envData = $this->getEnvData();
        $newEnv = $envData->map(function (EntryObj $entryObj) use ($keyToChange, $newValue): EntryObj {
            if ($entryObj->key === $keyToChange) {
                $entryObj->setValue($newValue);
            }

            return $entryObj;
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
        $envData = $this->getEnvData();
        $newEnv = $envData->filter(fn (EntryObj $entryObj): bool => $entryObj->key !== $key);

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
            ->reject(fn (EntryObj $entryObj): bool => $entryObj->isSeparator())
            ->firstWhere('key', '==', $key);
    }
}
