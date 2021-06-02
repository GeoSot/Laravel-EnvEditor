<?php

namespace GeoSot\EnvEditor\Helpers;

use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EnvFileContentManager
{
    protected $envEditor;
    protected $package = 'env-editor';
    protected $filesystem;

    /**
     * Constructor.
     *
     * @param EnvEditor $envEditor
     */
    public function __construct(EnvEditor $envEditor)
    {
        $this->envEditor = $envEditor;
        $this->filesystem = new Filesystem();
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
    public function getParsedFileContent(string $fileName = '')
    {
        $content = preg_split('/(\r\n|\r|\n)/', $this->getFileContents($fileName));

        $groupIndex = 1;
        $collection = collect([]);
        foreach ($content as $index => $line) {
            if ($line == '') {
                $separator = $this->envEditor->getkeysManager()->getKeysSeparator($groupIndex, $index);
                $collection->push($separator);
                $groupIndex++;
                continue;
            }
            $entry = explode('=', $line, 2);
            $groupArray = [
                'key'       => Arr::get($entry, 0),
                'value'     => Arr::get($entry, 1),
                'group'     => $groupIndex,
                'index'     => $index,
                'separator' => false,
            ];
            $collection->push($groupArray);
        }

        $filtered = $collection->sortBy('index')->reject(function ($value) use ($collection) {
            return $value['separator'] and $collection->where('group', '==', $value['group'])->count() == 1;
        });

        return $filtered;
    }

    /**
     * Get The File Contents.
     *
     * @param string $file
     *
     * @throws EnvException
     *
     * @return mixed
     */
    protected function getFileContents(string $file = '')
    {
        $envFile = $this->envEditor->getFilesManager()->getFilePath($file);

        if (!$this->filesystem->exists($envFile)) {
            throw new EnvException(__($this->package.'::env-editor.exceptions.fileNotExists', ['name' => $envFile]), 0);
        }

        try {
            return $this->filesystem->get($envFile);
        } catch (\Exception $e) {
            throw new EnvException(__($this->package.'::env-editor.exceptions.fileNotExists', ['name' => $envFile]), 2);
        }
    }

    /**
     * Save the new collection on .env file.
     *
     * @param Collection $envValues
     * @param string     $fileName
     *
     * @throws EnvException
     *
     * @return bool
     */
    public function save(Collection $envValues, string $fileName = '')
    {
        $env = $envValues->sortBy(['index'])->map(function ($item) {
            if ($item['key'] == '') {
                return '';
            }

            return $item['key'].'='.$item['value'];
        });

        $content = implode("\n", $env->toArray());
        $result = $this->filesystem->put($this->envEditor->getFilesManager()->getFilePath($fileName), $content);

        return $result !== false;
    }
}
