<?php

namespace FormaLibre\JobBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use FormaLibre\JobBundle\Installation\AdditionalInstaller;

class FormaLibreJobBundle extends PluginBundle
{
    public function hasMigrations()
    {
        return true;
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
