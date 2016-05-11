<?php

namespace PatternKit\Loader;

class FileLoader
{
    protected $assets = array();

    public function get($identifier)
    {
        $id = md5($identifier);
        if (isset($this->assets[$id])) {
            if (filemtime($identifier) > $this->assets[$id]['mtime']) {
                return $this->load($identifier);
            } else {
                return $this->assets[$id]['data'];
            }
        } else {
            return $this->load($identifier);
        }
    }

    public function getAssetPath($name, $type)
    {
        global $app;
        $valid_types = array(
          'templates',
          'data',
          'schemas',
          'docs',
          'sg',
        );

        if (in_array($type, $valid_types)) {
            $return = null;
            $paths = $app['config']['paths'][$type];
            if (is_array($paths)) {
                $paths = array_reverse($paths);
            }
            if ($paths) {
                foreach ($paths as $path) {
                    $extension = $app['config']['extensions'][$type];
                    $dir = './'.$path;
                    $file_path = $dir.'/'.$name.$extension;
                    if (is_dir($dir) && is_readable($file_path)) {
                        $return = $file_path;
                        break;
                    }
                }
            }

            return $return;
        } else {
            throw new Exception(
              $type.' is not equal to template, data or schema'
            );
        }
    }

    protected function load($path)
    {
        $file = file_get_contents($path);
        $id = md5($file);
        $mtime = filemtime($path);
        $assets[$id] = array(
          'mtime' => $mtime,
          'data' => $file,
        );

        return $assets[$id]['data'];
    }
}
