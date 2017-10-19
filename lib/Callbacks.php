<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

namespace Horde\GitTools;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Callbacks
{
    /**
     * Links the Horde_Role files needed to detect "Horde" as a valid role
     * when using PEAR via Composer.
     *
     * @param  PackageEvent  $event [description]
     * @return [type]        [description]
     */
    public static function linkHordeRole(PackageEvent $event)
    {
        symlink(
            dirname(__FILE__) . '/../vendor/pear-pear.horde.org/Horde_Role/PEAR/Installer/Role/Horde.php',
            dirname(__FILE__) . '/../vendor/pear/pear/PEAR/Installer/Role/Horde.php'
        );
        symlink(
            dirname(__FILE__) . '/../vendor/pear-pear.horde.org/Horde_Role/PEAR/Installer/Role/Horde.xml',
            dirname(__FILE__) . '/../vendor/pear/pear/PEAR/Installer/Role/Horde.xml'
        );
    }

    /**
     * Unlinks the Horde_Role files needed to detect "Horde" as a valid role
     * when using PEAR via Composer.
     *
     * @param  PackageEvent  $event [description]
     * @return [type]        [description]
     */
    public static function unlinkHordeRole(PackageEvent $event)
    {
        unlink(dirname(__FILE__) . '/../vendor/pear/pear/PEAR/Installer/Role/Horde.php');
        unlink(dirname(__FILE__) . '/../vendor/pear/pear/PEAR/Installer/Role/Horde.xml');
    }
}
