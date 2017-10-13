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

namespace Horde\GitTools\Module;

use Horde\GitTools\Action;
use Horde_Argv_Option;

/**
 * Class for handling 'dev' actions.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Dev extends Base
{

    /**
     * Handles the module's actions.
     *
     * @param  array $arguments  Argv arguments
     * @param  array $params    Configuration parameters.
     */
    public function handle(\Components_Configs $config)
    {
        // Grab the CLI options
        $this->_params = $config->getOptions();

        // ...and the arguments.
        $arguments = $config->getArguments();

        // Exit early if we aren't actually calling any dev related commands.
        if (array_shift($arguments) != 'dev' || !count($arguments)) {
            // Exit early if we didn't request any actual actions.
            return false;
        }

        switch (array_shift($arguments)) {
        case 'install':
            $this->_doInstallDev();
            return true;
        case 'new':
            $this->_doNewApp();
            return true;
        }
    }

    /**
     * Create a new Horde App from skeleton
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doNewApp()
    {
        $params = $this->_params;
        $action = new Action\Dev\NewApp($params, $this->_dependencies);
        $action->run();

    }

    /**
     * Perform linking of all repositories into the web_directory.
     *
     * @param  array $params  Configuration parameters.
     */
    protected function _doInstallDev()
    {
        $params = $this->_params;

        // First, empty directory.
        $action = new Action\Dev\EmptyLinkedDirectory($params, $this->_dependencies);
        $action->run();

        $action = new Action\Dev\LinkHorde($params, $this->_dependencies);
        $action->run();

        $action = new Action\Dev\LinkApps($params, $this->_dependencies);
        $action->run();

        $action = new Action\Dev\LinkFramework($params, $this->_dependencies);
        $action->run();
    }

    /* Horde_Cli_Modular methods */

    public function getOptionGroupOptions($action = null)
    {
        return array(
            new Horde_Argv_Option(
                '',
                '--copy',
                array(
                    'action' => 'store_true',
                    'help'   => 'Copy files instead of linking.'
                )
            ),
            new Horde_Argv_Option(
                '',
                '--author',
                array(
                    'action' => 'store',
                    'help'   => 'Author string "First Last <email@foo.org>".'
                )
            ),
            new Horde_Argv_Option(
                '',
                '--app-name',
                array(
                    'action' => 'store',
                    'help'   => 'Name of the new application to create.'
                )
            )
        );
    }

    public function hasOptionGroup()
    {
        return true;
    }

    /**
     * Returns the title for the option group representing this module.
     *
     * @return string  The group title.
     */
    public function getOptionGroupTitle()
    {
        return 'Dev actions';
    }

    /**
     * Returns the description for the option group representing this module.
     *
     * @return string  The group description.
     */
    public function getOptionGroupDescription()
    {
        return 'This command performs various development commands on all local repositories.';
    }

    /**
     * Return the options that should be explained in the context help.
     *
     * @return array A list of option help texts.
     */
    public function getContextOptionHelp($action = null)
    {
        $options = array(
            'install' => array('--copy' => '')
        );

        if (!empty($options[$action])) {
            return $options[$action];
        }

        return array();
    }

    /**
     * Returns additional usage title for this module.
     *
     * @return string  The usage title.
     */
    public function getTitle()
    {
        return 'dev ACTION';
    }

    /**
     * Returns additional usage description for this module.
     *
     * @return string The description.
     */
    public function getUsage()
    {
        return 'Perform a development install related action.';
    }

    /**
     * Return the action arguments supported by this module.
     *
     * @return array A list of supported action arguments.
     */
    public function getActions()
    {
        return array('install'  => 'Link/Install all repositories to the web directory.',
            'new' => 'Create a new component'
        );
    }

    /**
     * Return the help text for the specified action.
     *
     * @param string $action The action.
     *
     * @return string The help text.
     */
    public function getHelp()
    {
        return 'This module performs development related tasks.

Available actions for this module are:' . $this->_actionFormatter();
    }

}
