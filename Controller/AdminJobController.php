<?php

namespace FormaLibre\JobBundle\Controller;

use Claroline\CoreBundle\Manager\RoleManager;
use FormaLibre\JobBundle\Entity\Community;
use FormaLibre\JobBundle\Form\CommunityType;
use FormaLibre\JobBundle\Manager\JobManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_job_admin_tool')")
 */
class AdminJobController extends Controller
{
    private $formFactory;
    private $jobManager;
    private $request;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "jobManager"   = @DI\Inject("formalibre.manager.job_manager"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "roleManager"  = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        JobManager $jobManager,
        RequestStack $requestStack,
        RoleManager $roleManager
    )
    {
        $this->formFactory = $formFactory;
        $this->jobManager = $jobManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
    }

    /**
     * @EXT\Route(
     *     "/admin/job/management",
     *     name="formalibre_job_admin_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminJobManagementAction()
    {
        $communities = $this->jobManager->getAllCommunities();

        return array('communities' => $communities);
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
}
