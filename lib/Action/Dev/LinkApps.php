<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

namespace Horde\GitTools\Action\Dev;

use Horde\GitTools\Cli;

/**
 * Links applications into the web directory.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class LinkApps extends \Horde\GitTools\Action\Base
{
    /**
     *
     */
    public function run()
    {
        $horde_git = rtrim(ltrim($this->_params['git_base']), '/ ');
        $web_dir = rtrim(ltrim($this->_params['web_base']), '/ ');
        $this->_linkApps($horde_git, $web_dir);
    }

    /**
     * Link the applications.
     *
     * @param  string $horde_git  Path to the local base directory of all
     *                            repositories.
     * @param  string $web_dir    Path to the web accessible directory.
     */
    protected function _linkApps($horde_git, $web_dir)
    {
        $this->_dependencies->getOutput()->info(
            'LINKING applications to web directory ' . $web_dir
        );

        if (count($this->_params['apps'])) {
            foreach ($this->_apps as $app) {
                if ($app == 'horde') {
                    continue;
                }
                if (file_exists($horde_git . '/applications/' . $app)) {
                    $this->_linkApp($app);
                }
            }
        } else {
            foreach (new \DirectoryIterator($horde_git . '/applications') as $it) {
                if (!$it->isDot() && $it->isDir() && $it != 'horde' &&
                    is_dir($it->getPathname() . '/config')) {
                    $this->_linkApp($it, $horde_git, $web_dir);
                }
            }
        }
    }

    /**
     * Helper method to links a single application.
     *
     * @param  string $app        The application name.
     * @param  string $horde_git  Path to the local base directory of all
     *                            repositories.
     * @param  string $web_dir    Path to the web accessible directory.
     */
    protected function _linkApp($app, $horde_git, $web_dir)
    {
        print 'LINKING ' . $app . "\n";
        if (!symlink($horde_git . '/applications/' . $app, $web_dir . '/' . $app)) {
            $this->_dependencies->getOutput()->fail(
                'Cannot link ' . $web_dir . '/' . $app . ' to '
                . $horde_git . '/applications/' . $app
            );
        }
        file_put_contents(
            $horde_git . '/applications/' . $app . '/config/horde.local.php',
            '<?php define(\'HORDE_BASE\', \'' . $web_dir . '\');'
        );
    }

}
