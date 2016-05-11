<?php

namespace PatternKit\Navigation;

use Mni\FrontYAML\Parser;

class Navigation
{
    public static function getNav($pattern)
    {
        global $app;
        $categories = $app['config']['categories'];
        $schema_paths = array();
        $nav = array();
        $nav['title'] = $app['config']['title'];

        foreach ($app['config']['paths']['schemas'] as $path) {
            $files = scandir("./".$path);
            $schema_paths[] = array(
              'location' => $path,
              'files' => $files,
            );
        }

        if ($categories) {
            foreach ($categories as $category) {
                $value = strtolower(str_replace(' ', '_', $category));
                $nav['categories'][$value] = array();
                $nav['categories'][$value]['title'] = $category;
                $nav['categories'][$value]['path'] = '/'.$value;
            }
        }


        foreach ($schema_paths as $path) {
            foreach ($path['files'] as $file) {
                if (strpos($file, 'json') !== false) {
                    $nav_item = array();
                    $contents = json_decode(
                      $app['loader']->get($path['location'].'/'.$file)
                    );
                    $contents->name = substr($file, 0, -5);
                    $category = isset($contents->category) ? $contents->category : false;
                    $nav_item['title'] = isset($contents->title) ? $contents->title : $contents->name;
                    $nav_item['path'] = $contents->name;
                    if ($contents->name == $pattern) {
                        $nav_item['active'] = true;
                    }
                    if ($category) {
                        $nav['categories'][$category]['items'][] = $nav_item;
                    }

                }
            }
        }

        return $nav;
    }


//// Create secondary navigation for styleguide

    public static function getDocNav($pattern)
    {
        global $app;
        $nav = array();
        $parser = new Parser();

        foreach ($app['config']['paths']['sg'] as $path) {
            $files = glob('./'.$path.'/*'.$app['config']['extensions']['sg']);
            foreach ($files as $value) {
                $value_parts = str_split(
                  basename($value),
                  strpos(basename($value), ".")
                );
                $nav_item = array();
                $sg_file = $app['loader']->get($value);
                $sg_data = $parser->parse($sg_file);
                $data['sg_yaml'] = $sg_data->getYAML();
                $nav_item['title'] = $data['sg_yaml']['title'];
                $nav_item['path'] = $value_parts[0];
                if ($value_parts[0] == $pattern) {
                    $nav_item['active'] = true;
                }
                if ($value_parts[0] == 'index') {
                    $nav_item['path'] = null;
                    array_unshift($nav, $nav_item);
                } else {
                    $nav[] = $nav_item;
                }
            }
        }

        return $nav;
    }
}
