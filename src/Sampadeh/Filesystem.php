<?php
namespace Sampadeh;

class Filesystem
{

    private $path;

    private $exclude = [];

    public function __construct() {}

    public function copy($destination)
    {
        return is_dir($this->path) ? Dir::copy($this->path, $destination) : File::copy($this->path, $destination);
    }

    public function create($dir = true)
    {
        return $dir ? Dir::create($this->path) : File::create($this->path);
    }

    public function delete()
    {
        return is_dir($this->path) ? Dir::delete($this->path) : File::delete($this->path);
    }

    public function exclude($extension = [], $file = [], $dir = [])
    {
        return $this->exclude = [
            'extension' => (array) $extension,
            'file' => (array) $file,
            'dir' => (array) $dir
        ];
    }

    public function move($destination)
    {
        return is_dir($this->path) ? Dir::move($this->path, $destination) : File::move($this->path, $destination);
    }

    public function path($path)
    {
        $this->path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        $this->path = substr($this->path, - 1) == DIRECTORY_SEPARATOR ? substr($this->path, 0, - 1) : $this->path;
    }

    public function read()
    {
        $handle = fopen($this->path, 'r');
        $data = fread($handle, filesize($this->path));
        fclose($handle);

        return $data;
    }

    public function scan($recursive = true, $dir = '', &$list = [])
    {
        $dir = $dir ?: $this->path;

        if ($handle = opendir($dir)) {
            $this->exclude = empty($this->exclude) ? [
                'extension' => [],
                'file' => [],
                'dir' => []
            ] : $this->exclude;

            while (($file = readdir($handle)) !== false) {
                $extension = File::extension($file);

                if ($file == '.' || $file == '..' || in_array($extension, $this->exclude['extension'])) {
                    continue;
                }

                $path = $dir . DIRECTORY_SEPARATOR . $file;

                if (is_readable($path)) {
                    if (is_dir($path) && ! in_array($file, $this->exclude['dir'])) {
                        $list[] = [
                            'name' => $file,
                            'type' => 'directory',
                            'path' => $path
                        ];

                        if ($recursive) {
                            $this->scan($recursive, $path, $list);
                        }
                    } elseif (is_file($path) && ! in_array($file, $this->exclude['file'])) {
                        $list[] = [
                            'name' => $file,
                            'extension' => $extension,
                            'type' => 'file',
                            'path' => $path
                        ];
                    }
                }
            }

            closedir($handle);
        }

        return array_map('array_filter', $list);
    }

    public function write($data)
    {
        if ($handle = fopen($this->path, 'w')) {
            fwrite($handle, $data);
            fclose($handle);

            return true;
        }

        return false;
    }
}
