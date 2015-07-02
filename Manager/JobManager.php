<?php

namespace FormaLibre\JobBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FormaLibre\JobBundle\Entity\Announcer;
use FormaLibre\JobBundle\Entity\Community;
use FormaLibre\JobBundle\Entity\JobOffer;
use FormaLibre\JobBundle\Entity\JobRequest;
use FormaLibre\JobBundle\Entity\PendingAnnouncer;
use FormaLibre\JobBundle\Event\Log\LogJobAnnouncerCreateEvent;
use FormaLibre\JobBundle\Event\Log\LogJobOfferCreateEvent;
use FormaLibre\JobBundle\Event\Log\LogJobRequestCreateEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("formalibre.manager.job_manager")
 */
class JobManager
{
    private $container;
    private $eventDispatcher;
    private $roleManager;
    private $om;
    private $pagerFactory;

    private $announcerRepo;
    private $communityRepo;
    private $jobOfferRepo;
    private $jobRequestRepo;
    private $pendingRepo;

    private $cvDirectory;
    private $offersDirectory;

    /**
     * @DI\InjectParams({
     *     "container"       = @DI\Inject("service_container"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher,
        RoleManager $roleManager,
        ObjectManager $om,
        PagerFactory $pagerFactory
    )
    {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->roleManager = $roleManager;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;

        $this->announcerRepo = $om->getRepository('FormaLibreJobBundle:Announcer');
        $this->communityRepo = $om->getRepository('FormaLibreJobBundle:Community');
        $this->jobOfferRepo = $om->getRepository('FormaLibreJobBundle:JobOffer');
        $this->jobRequestRepo = $om->getRepository('FormaLibreJobBundle:JobRequest');
        $this->pendingRepo = $om->getRepository('FormaLibreJobBundle:PendingAnnouncer');

        $this->cvDirectory = $this->container->getParameter('claroline.param.files_directory') .
            '/jobbundle/cv/';
        $this->offersDirectory = $this->container->getParameter('claroline.param.files_directory') .
            '/jobbundle/offers/';
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

    public function persistAnnouncer(Announcer $announcer)
    {
        $this->om->persist($announcer);
        $this->om->flush();
    }

    public function deleteAnnouncer(Announcer $announcer)
    {
        $this->om->startFlushSuite();
        $jobOffers = $this->getJobOffersByAnnouncer($announcer);

        foreach ($jobOffers as $jobOffer) {
            $this->deleteJobOffer($jobOffer);
        }
        $this->om->remove($announcer);
        $this->om->endFlushSuite();
    }

    public function createAnnouncersFromUsers(Community $community, array $users)
    {
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $announcer = new Announcer();
            $announcer->setCommunity($community);
            $announcer->setUser($user);
            $this->om->persist($announcer);
            $event = new LogJobAnnouncerCreateEvent($announcer);
            $this->eventDispatcher->dispatch('log', $event);
        }
        $this->om->endFlushSuite();
    }

    public function createJobOffer(JobOffer $jobOffer)
    {
        $this->om->persist($jobOffer);
        $this->om->flush();
        $event = new LogJobOfferCreateEvent($jobOffer);
        $this->eventDispatcher->dispatch('log', $event);
    }


    public function persistJobOffer(JobOffer $jobOffer)
    {
        $this->om->persist($jobOffer);
        $this->om->flush();
    }

    public function deleteJobOffer(JobOffer $jobOffer)
    {
        $fileName = $jobOffer->getOffer();

        if (!is_null($fileName)) {
            $this->deleteFile($fileName, 'offer');
        }
        $this->om->remove($jobOffer);
        $this->om->flush();
    }

    public function createJobRequest(JobRequest $jobRequest)
    {
        $this->om->persist($jobRequest);
        $this->om->flush();
        $event = new LogJobRequestCreateEvent($jobRequest);
        $this->eventDispatcher->dispatch('log', $event);
    }

    public function persistJobRequest(JobRequest $jobRequest)
    {
        $this->om->persist($jobRequest);
        $this->om->flush();
    }

    public function deleteJobRequest(JobRequest $jobRequest)
    {
        $fileName = $jobRequest->getCv();

        if (!is_null($fileName)) {
            $this->deleteFile($fileName, 'cv');
        }
        $this->om->remove($jobRequest);
        $this->om->flush();
    }

    public function persistPendingAnnouncer(PendingAnnouncer $pendingAnnouncer)
    {
        $this->om->persist($pendingAnnouncer);
        $this->om->flush();
    }

    public function deletePendingAnnouncer(PendingAnnouncer $pendingAnnouncer)
    {
        $this->om->remove($pendingAnnouncer);
        $this->om->flush();
    }

    public function acceptPendingAnnouncer(PendingAnnouncer $pendingAnnouncer)
    {
        $this->om->startFlushSuite();
        $user = $pendingAnnouncer->getUser();
        $community = $pendingAnnouncer->getCommunity();
        $announcer = new Announcer();
        $announcer->setUser($user);
        $announcer->setCommunity($community);
        $this->persistAnnouncer($announcer);
        $event = new LogJobAnnouncerCreateEvent($announcer);
        $this->eventDispatcher->dispatch('log', $event);
        $announcerRole = $this->roleManager->getRoleByName('ROLE_JOB_ANNOUNCER');
        $this->roleManager->associateRole($user, $announcerRole);

        $offer = $pendingAnnouncer->getOffer();

        if (!is_null($offer)) {
            $jobOffer = new JobOffer();
            $jobOffer->setAnnouncer($announcer);
            $jobOffer->setCommunity($community);
            $jobOffer->setOffer($offer);
            $jobOffer->setOriginalName($pendingAnnouncer->getOriginalName());
            $jobOffer->setTitle($pendingAnnouncer->getOriginalName());
            $this->createJobOffer($jobOffer);
        }
        $this->deletePendingAnnouncer($pendingAnnouncer);
        $this->om->endFlushSuite();
    }

    public function saveFile(UploadedFile $tmpFile, $type = 'cv')
    {
        $dir = ($type === 'offer') ? $this->offersDirectory : $this->cvDirectory;
        $extension = $tmpFile->getClientOriginalExtension();
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid() .
            '.' .
            $extension;
        $tmpFile->move($dir, $hashName);

        return $hashName;
    }

    public function deleteFile($fileName, $type = 'cv')
    {
        $filePath = ($type === 'offer') ?
            $this->offersDirectory . $fileName :
            $this->cvDirectory . $fileName;

        try {
            unlink($filePath);
        } catch(\Exception $e) {}
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

    public function getCommunitiesByUser(
        User $user,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->communityRepo->findCommunitiesByUser(
            $user,
            $orderedBy,
            $order,
            $executeQuery
        );
    }


    /*****************************************
     * Access to AnnouncerRepository methods *
     *****************************************/


    public function getAnnouncersByUser(
        User $user,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->announcerRepo->findAnnouncersByUser(
            $user,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getAnnouncersByCommunity(
        Community $community,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->announcerRepo->findAnnouncersByCommunity(
            $community,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getAnnouncerByUserAndCommunity(
        User $user,
        Community $community,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->announcerRepo->findAnnouncerByUserAndCommunity(
            $user,
            $community,
            $orderedBy,
            $order,
            $executeQuery
        );
    }


    /************************************************
     * Access to PendingAnnouncerRepository methods *
     ************************************************/

    public function getPendingAnnouncersByCommunity(
        Community $community,
        $orderedBy = 'applicationDate',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->pendingRepo->findPendingAnnouncersByCommunity(
            $community,
            $orderedBy,
            $order,
            $executeQuery
        );
    }


    /****************************************
     * Access to JobOfferRepository methods *
     ****************************************/

    public function getJobOffersByAnnouncer(
        Announcer $announcer,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->jobOfferRepo->findJobOffersByAnnouncer(
            $announcer,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getAvailableJobOffersByCommunity(
        Community $community,
        $withPager = true,
        $orderedBy = 'id',
        $order = 'DESC',
        $page = 1,
        $max = 20
    )
    {
        $jobOffers = $this->jobOfferRepo->findAvailableJobOffersByCommunity(
            $community,
            $orderedBy,
            $order
        );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($jobOffers, $page, $max) :
            $jobOffers;
    }


    /******************************************
     * Access to JobRequestRepository methods *
     ******************************************/

    public function getJobRequestsByUser(
        User $user,
        $withPager = true,
        $orderedBy = 'id',
        $order = 'ASC',
        $page = 1,
        $max = 20
    )
    {
        $jobRequests = $this->jobRequestRepo->findJobRequestsByUser(
            $user,
            $orderedBy,
            $order
        );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($jobRequests, $page, $max) :
            $jobRequests;
    }

    public function getAvailableJobRequestsByCommunity(
        Community $community,
        $withPager = true,
        $orderedBy = 'id',
        $order = 'DESC',
        $page = 1,
        $max = 20
    )
    {
        $jobRequests = $this->jobRequestRepo->findAvailableJobRequestsByCommunity(
            $community,
            $orderedBy,
            $order
        );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($jobRequests, $page, $max) :
            $jobRequests;
    }
}
