<?php


namespace GeoSot\EnvEditor\Helpers;


use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class EnvFileContentManager
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
     * Parse the .env Contents
     *
     * @param string $fileName
     *
     * @return Collection
     * @throws EnvException
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
                $entry = explode("=", $line, 2);
                $groupArray = [
                    'key' => $entry[0],
                    'value' => array_get($entry, 1),
                    'group' => $groupIndex,
                    'index' => $index,
                    'separator' => false
                ];
                $collection->push($groupArray);

        }

        $filtered = $collection->sortBy('index')->reject(function ($value) use ($collection) {
            return ($value['separator'] and $collection->where('group', '==', $value['group'])->count() == 1);
        });

        return $filtered;
    }

    /**
     * Get The File Contents
     * @param string $file
     *
     * @return mixed
     * @throws EnvException
     */
    protected function getFileContents(string $file = '')
    {

        $envFile = $this->envEditor->getFilesManager()->getFilePath($file) ;

        if (!File::exists($envFile)) {
            throw new EnvException(__($this->package . '::exceptions.fileNotExists', ['name' => $envFile]), 0);
        }
        return File::get($envFile);
    }

    /**
     * Save the new collection on .env file
     *
     * @param Collection $envValues
     * @param  string    $fileName
     *
     * @return  bool
     * @throws EnvException
     */
    public function save(Collection $envValues, string $fileName = '')
    {
        $env = $envValues->sortBy(['index'])->map(function ($item) {
            if ($item['key'] == '') {
                return '';
            } else {
                return $item['key'] . '=' . $item['value'];
            }
        });

        $content = implode("\n", $env->toArray());
        $result = File::put($this->envEditor->getFilesManager()->getFilePath($fileName), $content);
        return $result !== false;
    }

}