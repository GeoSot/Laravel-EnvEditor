<?php

namespace GeoSot\EnvEditor\Helpers;

use GeoSot\EnvEditor\Dto\EntryObj;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class EnvFileContentManager
{
    public function __construct(protected EnvEditor $envEditor, protected Filesystem $filesystem)
    {
    }

    /**
     * Parse the .env Contents.
     *
     * @return Collection<int, EntryObj>
     *
     * @throws EnvException
     */
    public function getParsedFileContent(string $fileName = ''): Collection
    {
        /** @var list<string> $content */
        $content = preg_split('/(\r\n|\r|\n)/', $this->getFileContents($fileName));

        $groupIndex = 1;
        /** @var Collection<int, EntryObj> $collection */
        $collection = new Collection();
        foreach ($content as $index => $line) {
            $entryObj = EntryObj::parseEnvLine($line, $groupIndex, $index);
            $collection->push($entryObj);

            if ($entryObj->isSeparator()) {
                ++$groupIndex;
            }
        }

        return $collection->sortBy('index');
    }

    /**
     * Get The File Contents.
     *
     * @throws EnvException
     */
    protected function getFileContents(string $file = ''): string
    {
        $envFile = $this->envEditor->getFilesManager()->getFilePath($file);

        return $this->filesystem->get($envFile);
    }

    /**
     * Save the new collection on .env file.
     *
     * @param Collection<int, EntryObj> $envValues
     *
     * @throws EnvException
     */
    public function save(Collection $envValues, string $fileName = ''): bool
    {
        $env = $envValues
            ->sortBy(fn (EntryObj $entryObj): int => $entryObj->index)
            ->map(fn (EntryObj $entryObj): string => $entryObj->getAsEnvLine());

        $content = implode(PHP_EOL, $env->toArray());

        $result = $this->filesystem->put(
            $this->envEditor->getFilesManager()->getFilePath($fileName),
            $content
        );

        return false !== $result;
    }
}
