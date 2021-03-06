<?php

namespace FormaLibre\JobBundle\DataFixtures;

use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRequiredData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $roleRepo = $om->getRepository('Claroline\CoreBundle\Entity\Role');
        $created = false;
        
        $announcerRole = $roleRepo->findOneByName('ROLE_JOB_ANNOUNCER');

        if (is_null($announcerRole)) {
            $announcerRole = new Role();
            $announcerRole->setName('ROLE_JOB_ANNOUNCER');
            $announcerRole->setTranslationKey('job_announcer');
            $announcerRole->setReadOnly(true);
            $announcerRole->setPersonalWorkspaceCreationEnabled(true);
            $announcerRole->setType(Role::PLATFORM_ROLE);
            $om->persist($announcerRole);
            $created = true;
        }
        $seekerRole = $roleRepo->findOneByName('ROLE_JOB_SEEKER');

        if (is_null($seekerRole)) {
            $seekerRole = new Role();
            $seekerRole->setName('ROLE_JOB_SEEKER');
            $seekerRole->setTranslationKey('job_seeker');
            $seekerRole->setReadOnly(true);
            $seekerRole->setPersonalWorkspaceCreationEnabled(true);
            $seekerRole->setType(Role::PLATFORM_ROLE);
            $om->persist($seekerRole);
            $created = true;
        }
        $communityAdminRole = $roleRepo->findOneByName('ROLE_JOB_COMMUNITY_ADMIN');

        if (is_null($communityAdminRole)) {
            $communityAdminRole = new Role();
            $communityAdminRole->setName('ROLE_JOB_COMMUNITY_ADMIN');
            $communityAdminRole->setTranslationKey('job_community_admin');
            $communityAdminRole->setReadOnly(true);
            $communityAdminRole->setPersonalWorkspaceCreationEnabled(true);
            $communityAdminRole->setType(Role::PLATFORM_ROLE);
            $om->persist($communityAdminRole);
            $created = true;
        }

        if ($created) {
            $om->flush();
        }
    }
}
