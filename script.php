<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.fgautolightbox
 * @copyright   Copyright (C) 2026 FG. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Inštalačný preflight check - overí minimálnu verziu PHP a Joomly PRED
 * samotnou inštaláciou. Bez tohto by neúmyselná inštalácia tohto natívneho
 * J6-only buildu na nekompatibilnom prostredí (napr. Joomla 5, alebo PHP
 * 8.1) skončila až neskôr nejasnou fatal error hláškou pri prvom skutočnom
 * použití pluginu na frontende, namiesto zrozumiteľnej chyby priamo v
 * inštalátore.
 */
return new class () implements InstallerScriptInterface {
    private string $minimumPhp = '8.3.0';

    private string $minimumJoomla = '6.0.0';

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Factory::getApplication()->enqueueMessage(
                sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minimumPhp),
                'error'
            );

            return false;
        }

        if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Factory::getApplication()->enqueueMessage(
                sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minimumJoomla),
                'error'
            );

            return false;
        }

        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        // Joomla leaves a freshly installed plugin disabled. Enable it on a
        // first-time install only; on update the administrator's choice
        // (enabled or disabled) is left untouched.
        if ($type === 'install') {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('fgautolightbox'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('content'));
            $db->setQuery($query)->execute();
        }

        return true;
    }
};
