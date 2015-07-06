<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\JobBundle\Twig;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Claroline\CoreBundle\Entity\User;

/**
 * @Service
 * @Tag("twig.extension")
 */
class JobExtension extends \Twig_Extension
{
    private $container;

    /**
     * @InjectParams({
     *     "om" = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct($om)
    {
        $this->om = $om;
    }
    
    public function getFunctions()
    {
        return array(
            'getAnnouncer' => new \Twig_Function_Method($this, 'getAnnouncer')
        );
    }

    public function getName()
    {
        return 'job_extension';
    }

    public function getAnnouncer(User $user)
    {
        return $this->om->getRepository('FormaLibre\JobBundle\Entity\Announcer')->findOneByUser($user);
    }

}
