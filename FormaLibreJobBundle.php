<?php

namespace FormaLibre\JobBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class FormaLibreJobBundle extends PluginBundle
{
    public function hasMigrations()
    {
        return true;
    }
}
