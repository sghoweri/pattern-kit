<?php

namespace PatternKit\Helpers;

class TwigHelper
{
    public static function getPaths($config)
    {
        $twig_template_paths = array();

        array_push($twig_template_paths, ROOT_PATH.'/resources/templates');

        if (is_string($config)) {
            array_push($twig_template_paths, realpath('./'.$config));
        } elseif (is_array($config)) {
            foreach ($config as $value) {
                array_push($twig_template_paths, realpath('./'.$value));
            }
        }

        return $twig_template_paths;
    }
}
