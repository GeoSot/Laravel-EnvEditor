<?php

namespace GeoSot\EnvEditor\Helpers;

use Illuminate\Support\Arr;

class EntryObj implements \JsonSerializable
{
    public string $key;

    /**
     * @var int|string|null
     */
    protected mixed $value;

    public int $group = 0;

    public int $index = 0;

    protected bool $isSeparator = false;

    /**
     * @param int|string|null $value
     */
    public function __construct(string $key, mixed $value, int $group, int $index, bool $isSeparator = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->group = $group;
        $this->index = $index;
        $this->isSeparator = $isSeparator;
    }

    public static function parseEnvLine(string $line, int $group, int $index): self
    {
        $entry = explode('=', $line, 2);
        $isSeparator = 1 === count($entry);

        return new self(Arr::get($entry, 0), Arr::get($entry, 1), $group, $index, $isSeparator);
    }

    public static function makeKeysSeparator(int $groupIndex, int $index): self
    {
        return new self('', '', $groupIndex, $index, true);
    }

    public function getAsEnvLine(): string
    {
        return $this->isSeparator() ? '' : "$this->key=$this->value";
    }

    public function isSeparator(): bool
    {
        return $this->isSeparator;
    }

    /**
     * @return int|string|mixed|null
     */
    public function getValue(mixed $default = null): mixed
    {
        return $this->value ?: $default;
    }

    /**
     * @param int|string|mixed|null $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @return array{key:string, value: int|string|null, group:int, index:int , isSeparator:bool}
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return array{key:string, value: int|string|null, group:int, index:int , isSeparator:bool}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
