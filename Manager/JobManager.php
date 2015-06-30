<?php

namespace FormaLibre\JobBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use FormaLibre\JobBundle\Entity\Community;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("formalibre.manager.job_manager")
 */
class JobManager
{
    private $om;
    private $communityRepo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->communityRepo = $om->getRepository('FormaLibreJobBundle:Community');
    }

    public function persistCommunity(Community $community)
    {
        $this->om->persist($community);
        $this->om->flush();
    }

    public function deleteCommunity(Community $community)
    {
        $this->om->remove($community);
        $this->om->flush();
    }


    /*****************************************
     * Access to CommunityRepository methods *
     *****************************************/

    public function getAllCommunities(
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->communityRepo->findAllCommunities($orderedBy, $order, $executeQuery);
    }
}
