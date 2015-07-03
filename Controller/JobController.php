<?php

namespace FormaLibre\JobBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use FormaLibre\JobBundle\Entity\Announcer;
use FormaLibre\JobBundle\Entity\JobRequest;
use FormaLibre\JobBundle\Entity\JobOffer;
use FormaLibre\JobBundle\Entity\PendingAnnouncer;
use FormaLibre\JobBundle\Form\AnnouncerType;
use FormaLibre\JobBundle\Form\JobOfferType;
use FormaLibre\JobBundle\Form\JobRequestType;
use FormaLibre\JobBundle\Form\PendingAnnouncerType;
use FormaLibre\JobBundle\Form\SeekerType;
use FormaLibre\JobBundle\Manager\JobManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class JobController extends Controller
{
    private $authorization;
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
     *     "authorization" = @DI\Inject("security.authorization_checker"),
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
        AuthorizationCheckerInterface $authorization,
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
        $this->authorization = $authorization;
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
            $community = $form->get('community')->getData();
            $withNotification = $form->get('withNotification')->getData();
            $pendingAnnouncer->setCommunity($community);
            $pendingAnnouncer->setWithNotification($withNotification);
            $pendingAnnouncer->setApplicationDate(new \DateTime());
            $this->jobManager->persistPendingAnnouncer($pendingAnnouncer);

            $receivers = $community->getAdmins();

            if (count($receivers) > 0) {
                $object = $this->translator->trans(
                    'new_pending_announcer_object',
                    array(),
                    'job'
                );
                $content = $this->translator->trans(
                    'new_pending_announcer_content',
                    array(
                        '%name%' => $receivers[0]->getFirstName() . ' ' . $receivers[0]->getLastName(),
                        '%firstName%' => $user->getFirstName(),
                        '%lastName%' => $user->getLastName(),
                        '%registrationNumber%' => $form->get('registrationNumber')->getData(),
                        '%faseNumber%' => $form->get('registrationNumber')->getData(),
                        '%phone%' => $user->getPhone(),
                        '%url%' => $this->generateUrl(
                            'formalibre_job_admin_pending_announcers_management',
                            array('community' => $community->getId())
                        )
                    ),
                    'job'
                );
                $sender = null;

                $this->mailManager->send(
                    $object,
                    $content,
                    $receivers,
                    $sender
                );
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
                $this->jobManager->createJobRequest($jobRequest);
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
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function announcerWidgetAction(User $authenticatedUser)
    {
        $announcer = null;
        $community = null;
        $announcers = $this->jobManager->getAnnouncersByUser($authenticatedUser);

        if (count($announcers) > 0) {
            $announcer = $announcers[0];
            $community = $announcer->getCommunity();
        }

        return array('announcer' => $announcer, 'community' => $community);
    }

    /**
     * @EXT\Route(
     *     "/announcer/{announcer}/job/offers/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}",
     *     name="formalibre_job_announcer_job_offers_list",
     *     defaults={"page"=1, "max"=20, "orderedBy"="id","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function announcerJobOffersListAction(
        User $authenticatedUser,
        Announcer $announcer,
        $page = 1,
        $max = 20,
        $orderedBy = 'id',
        $order = 'ASC'
    )
    {
        $this->checkAnnouncerAccess($announcer, $authenticatedUser);
        $jobOffers = $this->jobManager->getJobOffersByAnnouncer(
            $announcer,
            true,
            $orderedBy,
            $order,
            $page,
            $max
        );

        return array(
            'jobOffers' => $jobOffers,
            'announcer' => $announcer,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/announcer/{announcer}/job/requests/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}",
     *     name="formalibre_job_announcer_job_requests_list",
     *     defaults={"page"=1, "max"=20, "orderedBy"="id","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function announcerJobRequestsListAction(
        User $authenticatedUser,
        Announcer $announcer,
        $page = 1,
        $max = 20,
        $orderedBy = 'id',
        $order = 'DESC'
    )
    {
        $this->checkAnnouncerAccess($announcer, $authenticatedUser);
        $jobRequests = $this->jobManager->getAvailableJobRequestsByCommunity(
            $announcer->getCommunity(),
            true,
            $orderedBy,
            $order,
            $page,
            $max
        );

        return array(
            'jobRequests' => $jobRequests,
            'announcer' => $announcer,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/seeker/widget",
     *     name="formalibre_job_seeker_widget",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function seekerWidgetAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/admin/widget",
     *     name="formalibre_job_admin_widget",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminWidgetAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/seeker/job/requests/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}",
     *     name="formalibre_job_seeker_job_requests_list",
     *     defaults={"page"=1, "max"=20, "orderedBy"="id","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function seekerJobRequestsListAction(
        User $authenticatedUser,
        $page = 1,
        $max = 20,
        $orderedBy = 'id',
        $order = 'ASC'
    )
    {
        $jobRequests = $this->jobManager->getJobRequestsByUser(
            $authenticatedUser,
            true,
            $orderedBy,
            $order,
            $page,
            $max
        );

        return array(
            'jobRequests' => $jobRequests,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/announcer/{announcer}/job/offer/create/form",
     *     name="formalibre_job_job_offer_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function jobOfferCreateFormAction(User $authenticatedUser, Announcer $announcer)
    {
        $this->checkAnnouncerAccess($announcer, $authenticatedUser);
        $form = $this->formFactory->create(
            new JobOfferType(),
            new JobOffer()
        );

        return array('form' => $form->createView(), 'announcer' => $announcer);
    }

    /**
     * @EXT\Route(
     *     "/announcer/{announcer}/job/offer/create",
     *     name="formalibre_job_job_offer_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:Job:jobOfferCreateForm.html.twig")
     */
    public function jobOfferCreateAction(User $authenticatedUser, Announcer $announcer)
    {
        $this->checkAnnouncerAccess($announcer, $authenticatedUser);
        $jobOffer = new JobOffer();
        $jobOffer->setAnnouncer($announcer);
        $jobOffer->setCommunity($announcer->getCommunity());

        $form = $this->formFactory->create(
            new JobOfferType(),
            $jobOffer
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();

            if (!is_null($file)) {
                $originalName = $file->getClientOriginalName();
                $hashName = $this->jobManager->saveFile($file, 'offer');
                $jobOffer->setOffer($hashName);
                $jobOffer->setOriginalName($originalName);
                $this->jobManager->createJobOffer($jobOffer);
            }

            return $this->redirect(
                $this->generateUrl(
                    'formalibre_job_announcer_job_offers_list',
                    array('announcer' => $announcer->getId())
                )
            );
        } else {

            return array('form' => $form->createView(), 'announcer' => $announcer);
        }
    }

    /**
     * @EXT\Route(
     *     "/announcer/job/offer/{jobOffer}/edit/form",
     *     name="formalibre_job_job_offer_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function jobOfferEditFormAction(User $authenticatedUser, JobOffer $jobOffer)
    {
        $this->checkJobOfferEditAccess($jobOffer, $authenticatedUser);
        $form = $this->formFactory->create(
            new JobOfferType(),
            $jobOffer
        );

        return array('form' => $form->createView(), 'jobOffer' => $jobOffer);
    }

    /**
     * @EXT\Route(
     *     "/announcer/job/offer/{jobOffer}/edit",
     *     name="formalibre_job_job_offer_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:Job:jobOfferEditForm.html.twig")
     */
    public function jobOfferEditAction(User $authenticatedUser, JobOffer $jobOffer)
    {
        $this->checkJobOfferEditAccess($jobOffer, $authenticatedUser);
        $form = $this->formFactory->create(
            new JobOfferType(),
            $jobOffer
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();

            if (!is_null($file)) {
                $currentFileName = $jobOffer->getOffer();

                if (!is_null($currentFileName)) {
                    $this->jobManager->deleteFile($currentFileName, 'offer');
                }
                $originalName = $file->getClientOriginalName();
                $hashName = $this->jobManager->saveFile($file, 'offer');
                $jobOffer->setOffer($hashName);
                $jobOffer->setOriginalName($originalName);
                $this->jobManager->persistJobOffer($jobOffer);
            }

            return $this->redirect(
                $this->generateUrl(
                    'formalibre_job_announcer_job_offers_list',
                    array('announcer' => $jobOffer->getAnnouncer()->getId())
                )
            );
        } else {

            return array('form' => $form->createView(), 'jobOffer' => $jobOffer);
        }
    }

    /**
     * @EXT\Route(
     *     "/announcer/job/offer/{jobOffer}/delete",
     *     name="formalibre_job_job_offer_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function jobOfferDeleteAction(User $authenticatedUser, JobOffer $jobOffer)
    {
        $this->checkJobOfferEditAccess($jobOffer, $authenticatedUser);
        $this->jobManager->deleteJobOffer($jobOffer);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/seeker/job/request/create/form",
     *     name="formalibre_job_job_request_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function jobRequestCreateFormAction()
    {
        $this->checkSeekerAccess();
        $form = $this->formFactory->create(new JobRequestType(), new JobRequest());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/seeker/job/request/create",
     *     name="formalibre_job_job_request_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:Job:jobRequestCreateForm.html.twig")
     */
    public function jobRequestCreateAction(User $authenticatedUser)
    {
        $this->checkSeekerAccess();
        $jobRequest = new JobRequest();
        $this->checkSeekerAccess();
        $form = $this->formFactory->create(new JobRequestType(), $jobRequest);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $jobRequest->setUser($authenticatedUser);
            $file = $form->get('file')->getData();

            if (!is_null($file)) {
                $originalName = $file->getClientOriginalName();
                $hashName = $this->jobManager->saveFile($file, 'cv');
                $jobRequest->setCv($hashName);
                $jobRequest->setOriginalName($originalName);
                $this->jobManager->createJobRequest($jobRequest);

                $expirationDate = $jobRequest->getExpirationDate();

                if ($jobRequest->getVisible() && (is_null($expirationDate) || $expirationDate > new \DateTime())) {
                    $community = $jobRequest->getCommunity();
                    $notifiableAnnouncers = $this->jobManager->getNotifiableAnnouncersByCommunity($community);

                    if (count($notifiableAnnouncers) > 0) {
                        $receivers = array();

                        foreach ($notifiableAnnouncers as $announcer) {
                            $receivers[] = $announcer->getUser();
                        }
                        $object = $this->translator->trans(
                            'new_job_request_object',
                            array(),
                            'job'
                        );
                        $content = $this->translator->trans(
                            'new_job_request_content',
                            array(
                                '%name%' => $receivers[0]->getFirstName() . ' ' .  $receivers[0]->getLastName(),
                                '%communityName%' => $community->getName()
                            ),
                            'job'
                        );
                        $sender = null;

                        $this->mailManager->send(
                            $object,
                            $content,
                            $receivers,
                            $sender
                        );
                    }
                }
            }

            return $this->redirect(
                $this->generateUrl('formalibre_job_seeker_job_requests_list')
            );
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/seeker/job/request/{jobRequest}/edit/form",
     *     name="formalibre_job_job_request_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function jobRequestEditFormAction(
        User $authenticatedUser,
        JobRequest $jobRequest
    )
    {
        $this->checkJobRequestEditAccess($jobRequest, $authenticatedUser);
        $form = $this->formFactory->create(new JobRequestType(), $jobRequest);

        return array('form' => $form->createView(), 'jobRequest' => $jobRequest);
    }

    /**
     * @EXT\Route(
     *     "/seeker/job/request/{jobRequest}/edit",
     *     name="formalibre_job_job_request_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:Job:jobRequestEditForm.html.twig")
     */
    public function jobRequestEditAction(
        User $authenticatedUser,
        JobRequest $jobRequest
    )
    {
        $this->checkJobRequestEditAccess($jobRequest, $authenticatedUser);
        $form = $this->formFactory->create(new JobRequestType(), $jobRequest);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();

            if (!is_null($file)) {
                $currentFileName = $jobRequest->getCv();

                if (!is_null($currentFileName)) {
                    $this->jobManager->deleteFile($currentFileName, 'cv');
                }
                $originalName = $file->getClientOriginalName();
                $hashName = $this->jobManager->saveFile($file, 'cv');
                $jobRequest->setCv($hashName);
                $jobRequest->setOriginalName($originalName);
            }
            $this->jobManager->persistJobRequest($jobRequest);

            return $this->redirect(
                $this->generateUrl('formalibre_job_seeker_job_requests_list')
            );
        } else {

            return array('form' => $form->createView(), 'jobRequest' => $jobRequest);
        }
    }

    /**
     * @EXT\Route(
     *     "/seeker/job/request/{jobRequest}/delete",
     *     name="formalibre_job_job_request_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function jobRequestDeleteAction(User $authenticatedUser, JobRequest $jobRequest)
    {
        $this->checkJobRequestEditAccess($jobRequest, $authenticatedUser);
        $this->jobManager->deleteJobRequest($jobRequest);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/announcer/{announcer}/edit/form",
     *     name="formalibre_job_announcer_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function announcerEditFormAction(User $authenticatedUser, Announcer $announcer)
    {
        $this->checkAnnouncerAccess($announcer, $authenticatedUser);
        $form = $this->formFactory->create(new AnnouncerType(), $announcer);

        return array('form' => $form->createView(), 'announcer' => $announcer);
    }

    /**
     * @EXT\Route(
     *     "/announcer/{announcer}/edit",
     *     name="formalibre_job_announcer_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:Job:announcerEditForm.html.twig")
     */
    public function announcerEditAction(User $authenticatedUser, Announcer $announcer)
    {
        $this->checkAnnouncerAccess($announcer, $authenticatedUser);
        $form = $this->formFactory->create(new AnnouncerType(), $announcer);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->jobManager->persistAnnouncer($announcer);

            return $this->redirect(
                $this->generateUrl(
                    'formalibre_job_announcer_edit_form',
                    array('announcer' => $announcer->getId())
                )
            );
        } else {

            return array('form' => $form->createView(), 'announcer' => $announcer);
        }
    }

    private function checkAnnouncerAccess(Announcer $announcer, User $user)
    {
        $announcerUser = $announcer->getUser();

        if ($announcerUser->getId() !== $user->getId()) {

            throw new AccessDeniedException();
        }
    }

    private function checkSeekerAccess()
    {
        if (!$this->authorization->isGranted('ROLE_JOB_SEEKER')) {

            throw new AccessDeniedException();
        }
    }

    private function checkJobOfferEditAccess(JobOffer $jobOffer, User $user)
    {
        $jobOfferUser = $jobOffer->getAnnouncer()->getUser();

        if ($jobOfferUser->getId() !== $user->getId()) {

            throw new AccessDeniedException();
        }
    }

    private function checkJobRequestEditAccess(JobRequest $jobRequest, User $user)
    {
        $jobRequestUser = $jobRequest->getUser();

        if ($jobRequestUser->getId() !== $user->getId()) {

            throw new AccessDeniedException();
        }
    }
}
