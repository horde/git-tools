<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Ralf Lang <lang@b1-systems.de>
 * @author   Gunnar Wrobel <wrobel@horde.orh>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

namespace Horde\GitTools\Action\Dev;

use Horde_Yaml as Yaml;

use Horde\GitTools\Cli;

/**
 * Links applications into the web directory.
 *
 * @author    Gunnar Wrobel <wrobel@horde.orh>
 * @author    Ralf Lang <lang@b1-systems.de>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class NewApp extends \Horde\GitTools\Action\Base
{
    /**
     *
     */
    public function run()
    {
        $horde_git = rtrim(ltrim($this->_params['git_base']), '/ ');
        $web_dir = rtrim(ltrim($this->_params['web_base']), '/ ');
        $this->_newApp($horde_git, $web_dir);
    }

    /**
     * Create the application from skeleton.
     *
     * @param  string $horde_git  Path to the local base directory of all
     *                            repositories.
     * @param  string $web_dir    Path to the web accessible directory.
     */
    protected function _newApp($horde_git, $web_dir)
    {
        if (empty($app = $this->_params['app_name'])) {
            $this->_dependencies->getOutput()->warn('no --app-name given');
            exit(1);
        }
        if (empty($author = $this->_params['author'])) {
            $this->_dependencies->getOutput()->warn('no --author given');
            exit(1);
        }
        $this->_dependencies->getOutput()->info(
            'CREATING application ' . $app . ' for ' . $author
        );
        $skeleton_path = $horde_git . '/skeleton';
        if (!is_dir($skeleton_path)) {
            $this->_dependencies->getOutput()->warn('Assumed origin of the skeleton app (' . $skeleton_path . ') does not seem to exist!');
            exit(1);
        }

        $module_path = dirname($skeleton_path) . '/' . $app;
        $this->_recursiveCopy($skeleton_path, $module_path);
        // Fetch filelist
        $list = array();
        $list = $this->_analysedir($module_path, $list);
        // Modify each file
        foreach ($list as $file) {
            $this->_substitute_skeleton($file, $app, $author);
        }
        rename(
            $module_path . '/test/Skeleton',
            $module_path . '/test/' . ucfirst($app)
        );
        rename(
            $module_path . '/locale/skeleton.pot',
            $module_path . '/locale/' . $app . '.pot'
        );
        rename(
            $module_path . '/migration/1_skeleton_base_tables.php',
            $module_path . '/migration/1_' . $app . '_base_tables.php'
        );


    }

    protected function _recursiveCopy($path, $dest)
    {
        @mkdir($dest);
        $objects = scandir($path);
        if (sizeof($objects) > 0) {
            foreach ($objects as $file) {
                if ($file == "." || $file == ".." || $file == ".git") {
                    continue;
                }
                if (is_dir($path . '/' . $file)) {
                    $this->_recursiveCopy($path . '/' . $file, $dest .  '/' . $file);
                } else {
                    copy($path . '/' . $file, $dest . '/' .$file);
                }
            }
        }
    }

    protected function _analysedir($path, $list)
    {
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file!='.' && $file!='..') {
                $file = $path . '/' . $file;
                if (!is_dir($file)) {
                    $list[count($list)]=$file;
                } else {
                    $list += $this->_analysedir($file, $list);
                }
            }
        }
        return $list;
    }

    protected function _substitute_skeleton($filename, $modulname, $author)
    {
        $prjUC = strtoupper(trim($modulname));
        $prjLC = strtolower($prjUC);
        $prjMC = substr($prjUC, 0, 1) . substr($prjLC, 1, strlen($prjLC) - 1);

        $filehandle = fopen(trim($filename), 'r');
        $file = fread($filehandle, filesize($filename));
        fclose($filehandle);
        $newfile = str_replace(
            array('SKELETON', 'Skeleton', 'skeleton', 'Your Name <you@example.com>'),
            array($prjUC, $prjMC, $prjLC, $author),
            $file
        );
        $filehandle = fopen(trim($filename), 'w');
        fwrite($filehandle, $newfile);
        fclose($filehandle);
    }


}
