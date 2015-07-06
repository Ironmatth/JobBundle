<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\JobBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        switch (true) {
            case version_compare($currentVersion, '5.1.0', '<'):
                $updater = new Updater\Updater050100($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
        }
    }
}
