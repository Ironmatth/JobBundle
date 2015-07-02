<?php

namespace FormaLibre\JobBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use FormaLibre\JobBundle\Entity\Announcer;
use FormaLibre\JobBundle\Entity\Community;
use FormaLibre\JobBundle\Entity\PendingAnnouncer;
use FormaLibre\JobBundle\Form\AnnouncersType;
use FormaLibre\JobBundle\Form\CommunityType;
use FormaLibre\JobBundle\Manager\JobManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_job_admin_tool')")
 */
class AdminJobController extends Controller
{
    private $formFactory;
    private $jobManager;
    private $mailManager;
    private $request;
    private $roleManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "jobManager"   = @DI\Inject("formalibre.manager.job_manager"),
     *     "mailManager"  = @DI\Inject("claroline.manager.mail_manager"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "roleManager"  = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"   = @DI\Inject("translator")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        JobManager $jobManager,
        MailManager $mailManager,
        RequestStack $requestStack,
        RoleManager $roleManager,
        TranslatorInterface $translator
    )
    {
        $this->formFactory = $formFactory;
        $this->jobManager = $jobManager;
        $this->mailManager = $mailManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "/admin/job/tool/index",
     *     name="formalibre_job_admin_tool_index",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminToolIndexAction(User $authenticatedUser)
    {
        $communities = $this->jobManager->getCommunitiesByUser($authenticatedUser);

        return array('communities' => $communities);
    }

    /**
     * @EXT\Route(
     *     "/admin/communities/management",
     *     name="formalibre_job_admin_communities_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function communitiesManagementAction()
    {
        $communities = $this->jobManager->getAllCommunities();

        return array('communities' => $communities);
    }

    /**
     * @EXT\Route(
     *     "/admin/community/{community}/annnouncers/management",
     *     name="formalibre_job_admin_announcers_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function announcersManagementAction(User $authenticatedUser, Community $community)
    {
        $communities = $this->jobManager->getCommunitiesByUser($authenticatedUser);
        $announcers = $this->jobManager->getAnnouncersByCommunity($community);

        return array(
            'currentCommunity' => $community,
            'communities' => $communities,
            'announcers' => $announcers
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/community/{community}/pending/annnouncers/management",
     *     name="formalibre_job_admin_pending_announcers_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function pendingAnnouncersManagementAction(User $authenticatedUser, Community $community)
    {
        $communities = $this->jobManager->getCommunitiesByUser($authenticatedUser);
        $pendingAnnouncers = $this->jobManager->getPendingAnnouncersByCommunity($community);

        return array(
            'currentCommunity' => $community,
            'communities' => $communities,
            'pendingAnnouncers' => $pendingAnnouncers
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/community/create/form",
     *     name="formalibre_job_admin_community_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:AdminJob:communityCreateModalForm.html.twig")
     */
    public function communityCreateFormAction()
    {
        $roleAdmin = $this->roleManager->getRoleByName('ROLE_ADMIN');
        $form = $this->formFactory->create(
            new CommunityType(array($roleAdmin)),
            new Community()
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/community/create",
     *     name="formalibre_job_admin_community_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:AdminJob:communityCreateModalForm.html.twig")
     */
    public function communityCreateAction()
    {
        $roleAdmin = $this->roleManager->getRoleByName('ROLE_ADMIN');
        $community = new Community();
        $form = $this->formFactory->create(
            new CommunityType(array($roleAdmin)),
            $community
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->jobManager->persistCommunity($community);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/community/{community}/edit/form",
     *     name="formalibre_job_admin_community_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:AdminJob:communityEditModalForm.html.twig")
     */
    public function communityEditFormAction(Community $community)
    {
        $roleAdmin = $this->roleManager->getRoleByName('ROLE_ADMIN');
        $form = $this->formFactory->create(
            new CommunityType(array($roleAdmin)),
            $community
        );

        return array('form' => $form->createView(), 'community' => $community);
    }

    /**
     * @EXT\Route(
     *     "/admin/community/{community}/edit",
     *     name="formalibre_job_admin_community_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:AdminJob:communityEditModalForm.html.twig")
     */
    public function communityEditAction(Community $community)
    {
        $roleAdmin = $this->roleManager->getRoleByName('ROLE_ADMIN');
        $form = $this->formFactory->create(
            new CommunityType(array($roleAdmin)),
            $community
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->jobManager->persistCommunity($community);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'community' => $community);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/community/{community}/delete",
     *     name="formalibre_job_admin_community_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function communityDeleteAction(Community $community)
    {
        $this->jobManager->deleteCommunity($community);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/pending/announcer/{pendingAnnouncer}/accept",
     *     name="formalibre_job_admin_pending_announcer_accept",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function acceptPendingAnnouncerAction(PendingAnnouncer $pendingAnnouncer)
    {
        $user = $pendingAnnouncer->getUser();
        $community = $pendingAnnouncer->getCommunity();
        $this->jobManager->acceptPendingAnnouncer($pendingAnnouncer);

        $object = $this->translator->trans(
            'accept_pending_announcer_object',
            array(),
            'job'
        );
        $content = $this->translator->trans(
            'accept_pending_announcer_content',
            array(),
            'job'
        );
        $receivers = array($user);
        $sender = null;

        $this->mailManager->send(
            $object,
            $content,
            $receivers,
            $sender
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/pending/announcer/{pendingAnnouncer}/decline",
     *     name="formalibre_job_admin_pending_announcer_decline",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function declinePendingAnnouncerAction(PendingAnnouncer $pendingAnnouncer)
    {
        $user = $pendingAnnouncer->getUser();
        $community = $pendingAnnouncer->getCommunity();
        $this->jobManager->deletePendingAnnouncer($pendingAnnouncer);

        $object = $this->translator->trans(
            'decline_pending_announcer_object',
            array(),
            'job'
        );
        $content = $this->translator->trans(
            'decline_pending_announcer_content',
            array(),
            'job'
        );
        $receivers = array($user);
        $sender = null;

        $this->mailManager->send(
            $object,
            $content,
            $receivers,
            $sender
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/community/{community}/announcers/create/form",
     *     name="formalibre_job_admin_announcers_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:AdminJob:announcersCreateModalForm.html.twig")
     */
    public function announcersCreateFormAction(Community $community)
    {
        $users = array();
        $announcers = $this->jobManager->getAllAnnouncers();

        foreach ($announcers as $announcer) {
            $users[] = $announcer->getUser();
        }
        $form = $this->formFactory->create(new AnnouncersType($users));

        return array('form' => $form->createView(), 'community' => $community);
    }

    /**
     * @EXT\Route(
     *     "/admin/community/{community}/announcers/create",
     *     name="formalibre_job_admin_announcers_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:AdminJob:announcersCreateModalForm.html.twig")
     */
    public function announcersCreateAction(Community $community)
    {
        $users = array();
        $announcers = $this->jobManager->getAllAnnouncers();

        foreach ($announcers as $announcer) {
            $users[] = $announcer->getUser();
        }
        $form = $this->formFactory->create(new AnnouncersType($users));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $users = $form->get('announcers')->getData();

            if (count($users) > 0) {
                $this->jobManager->createAnnouncersFromUsers($community, $users);
            }

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'community' => $community);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/announcer/{announcer}/delete",
     *     name="formalibre_job_admin_announcer_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function deleteAnnouncerAction(Announcer $announcer)
    {
        $this->jobManager->deleteAnnouncer($announcer);

        return new JsonResponse('success', 200);
    }
}
