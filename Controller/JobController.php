<?php

namespace FormaLibre\JobBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use FormaLibre\JobBundle\Entity\JobRequest;
use FormaLibre\JobBundle\Entity\PendingAnnouncer;
use FormaLibre\JobBundle\Form\PendingAnnouncerType;
use FormaLibre\JobBundle\Form\SeekerType;
use FormaLibre\JobBundle\Manager\JobManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class JobController extends Controller
{
    private $configHandler;
    private $facetManager;
    private $formFactory;
    private $jobManager;
    private $mailManager;
    private $request;
    private $roleManager;
    private $translator;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "facetManager"  = @DI\Inject("claroline.manager.facet_manager"),
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "jobManager"    = @DI\Inject("formalibre.manager.job_manager"),
     *     "mailManager"   = @DI\Inject("claroline.manager.mail_manager"),
     *     "requestStack"  = @DI\Inject("request_stack"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"    = @DI\Inject("translator"),
     *     "userManager"   = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        FacetManager $facetManager,
        FormFactory $formFactory,
        JobManager $jobManager,
        MailManager $mailManager,
        RequestStack $requestStack,
        RoleManager $roleManager,
        TranslatorInterface $translator,
        UserManager $userManager
    )
    {
        $this->configHandler = $configHandler;
        $this->facetManager = $facetManager;
        $this->formFactory = $formFactory;
        $this->jobManager = $jobManager;
        $this->mailManager = $mailManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->translator = $translator;
        $this->userManager = $userManager;
    }

    /**
     * @EXT\Route(
     *     "/pending/announcer/create/form",
     *     name="formalibre_job_pending_announcer_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function pendingAnnouncerCreateFormAction()
    {
        $form = $this->formFactory->create(
            new PendingAnnouncerType(),
            new User()
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/pending/announcer/create",
     *     name="formalibre_job_pending_announcer_create",
     *     options={"expose"=true}
     * )
     * @EXT\Template("FormaLibreJobBundle:Job:pendingAnnouncerCreateForm.html.twig")
     */
    public function pendingAnnouncerCreateAction()
    {
        $user = new User();

        $form = $this->formFactory->create(
            new PendingAnnouncerType(),
            $user
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->roleManager->setRoleToRoleSubject(
                $user,
                $this->configHandler->getParameter('default_role')
            );
            $user = $this->userManager->createUserWithRole(
                $user,
                PlatformRoles::USER
            );
//            //then we adds the differents value for facets.
//            foreach ($facets as $facet) {
//                foreach ($facet->getPanelFacets() as $panel) {
//                    foreach ($panel->getFieldsFacet() as $field) {
//                        $this->facetManager->setFieldValue($user, $field, $form->get($field->getName())->getData(), true);
//                    }
//                }
//            }
            $pendingAnnouncer = new PendingAnnouncer();
            $pendingAnnouncer->setUser($user);
            $offerFile = $form->get('file')->getData();
            $community = $form->get('community')->getData();

            if (!is_null($offerFile)) {
                $originalName = $offerFile->getClientOriginalName();
                $originalExtension = $offerFile->getClientOriginalExtension();
                $pendingAnnouncer->setOriginalName($originalName . '.' . $originalExtension);
                $hashName = $this->jobManager->saveFile($offerFile, 'offer');
                $pendingAnnouncer->setOffer($hashName);
            }
            $pendingAnnouncer->setCommunity($community);
            $pendingAnnouncer->setApplicationDate(new \DateTime());
            $this->jobManager->persistPendingAnnouncer($pendingAnnouncer);

            $msg = $this->get('translator')->trans('account_created', array(), 'platform');
            $this->get('request')->getSession()->getFlashBag()->add('success', $msg);

            if ($this->configHandler->getParameter('registration_mail_validation')) {
                $msg = $this->translator->trans('please_validate_your_account', array(), 'platform');
                $this->request->getSession()->getFlashBag()->add('success', $msg);
            }

            if ($this->configHandler->getParameter('auto_logging_after_registration')) {
                //this is bad but I don't know any other way (yet)
                $tokenStorage = $this->get('security.token_storage');
                $providerKey = 'main';
                $token = new UsernamePasswordToken(
                    $user,
                    $user->getPassword(),
                    $providerKey,
                    $user->getRoles()
                );
                $tokenStorage->setToken($token);
                //a bit hacky I know ~

                return $this->get('claroline.authentication_handler')->onAuthenticationSuccess($this->request, $token);
            }

            return $this->redirect($this->generateUrl('claro_security_login'));
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/seeker/create/form",
     *     name="formalibre_job_seeker_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function seekerCreateFormAction()
    {
        $form = $this->formFactory->create(
            new SeekerType(),
            new User()
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/seeker/create",
     *     name="formalibre_job_seeker_create",
     *     options={"expose"=true}
     * )
     * @EXT\Template("FormaLibreJobBundle:Job:seekerCreateForm.html.twig")
     */
    public function seekerCreateAction()
    {
        $user = new User();

        $form = $this->formFactory->create(
            new SeekerType(),
            $user
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->roleManager->setRoleToRoleSubject(
                $user,
                $this->configHandler->getParameter('default_role')
            );
            $user = $this->userManager->createUserWithRole(
                $user,
                PlatformRoles::USER
            );
//            //then we adds the differents value for facets.
//            foreach ($facets as $facet) {
//                foreach ($facet->getPanelFacets() as $panel) {
//                    foreach ($panel->getFieldsFacet() as $field) {
//                        $this->facetManager->setFieldValue($user, $field, $form->get($field->getName())->getData(), true);
//                    }
//                }
//            }
            $seekerRole = $this->roleManager->getRoleByName('ROLE_JOB_SEEKER');

            if (!is_null($seekerRole)) {
                $this->roleManager->associateRole($user, $seekerRole);
            }
            $cvFile = $form->get('file')->getData();
            $community = $form->get('community')->getData();

            if (!is_null($cvFile)) {
                $originalName = $cvFile->getClientOriginalName();
                $hashName = $this->jobManager->saveFile($cvFile, 'cv');
                $jobRequest = new JobRequest();
                $jobRequest->setCommunity($community);
                $jobRequest->setUser($user);
                $jobRequest->setCv($hashName);
                $jobRequest->setOriginalName($originalName);
                $jobRequest->setTitle($originalName);
                $this->jobManager->persistJobRequest($jobRequest);
            }

            $msg = $this->get('translator')->trans('account_created', array(), 'platform');
            $this->get('request')->getSession()->getFlashBag()->add('success', $msg);

            if ($this->configHandler->getParameter('registration_mail_validation')) {
                $msg = $this->translator->trans('please_validate_your_account', array(), 'platform');
                $this->request->getSession()->getFlashBag()->add('success', $msg);
            }

            if ($this->configHandler->getParameter('auto_logging_after_registration')) {
                //this is bad but I don't know any other way (yet)
                $tokenStorage = $this->get('security.token_storage');
                $providerKey = 'main';
                $token = new UsernamePasswordToken(
                    $user,
                    $user->getPassword(),
                    $providerKey,
                    $user->getRoles()
                );
                $tokenStorage->setToken($token);
                //a bit hacky I know ~

                return $this->get('claroline.authentication_handler')->onAuthenticationSuccess($this->request, $token);
            }

            return $this->redirect($this->generateUrl('claro_security_login'));
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/announcer/widget",
     *     name="formalibre_job_announcer_widget",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function announcerWidgetAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/seeker/widget",
     *     name="formalibre_job_seeker_widget",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function seekerWidgetAction()
    {
        return array();
    }
}
