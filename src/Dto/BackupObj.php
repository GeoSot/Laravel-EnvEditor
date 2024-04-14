<?php

namespace GeoSot\EnvEditor\Dto;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Arrayable<string, scalar>
 */
class BackupObj implements \JsonSerializable, Arrayable
{
    /**
     * @param Collection<int, EntryObj> $entries
     */
    public function __construct(
        public readonly string $name,
        public readonly Carbon $createdAt,
        public readonly Carbon $modifiedAt,
        public readonly string $path,
        public readonly string $rawContent,
        public readonly Collection $entries,
    ) {
    }

    /**
     * @return array{real_name:string, name:string, created_at:string, modified_at:string, raw_content:string, path:string, entries:array<int,EntryObj>}
     */
    public function toArray(): array
    {
        return [
            'real_name' => $this->name,
            'name' => $this->name,
            'created_at' => Carbon::createFromTimestamp($this->createdAt)->format(config('env-editor.timeFormat')),
            'modified_at' => Carbon::createFromTimestamp($this->modifiedAt)->format(config('env-editor.timeFormat')),
            'raw_content' => $this->rawContent,
            'path' => $this->path,
            'entries' => $this->entries->toArray(),
        ];
    }

    /**
     * @return array{real_name:string, name:string, created_at:string, modified_at:string, raw_content:string, path:string, entries:array<int,EntryObj>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
