<?php

namespace FormaLibre\JobBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use FormaLibre\JobBundle\Entity\Announcer;
use FormaLibre\JobBundle\Entity\Community;
use FormaLibre\JobBundle\Entity\JobRequest;
use FormaLibre\JobBundle\Entity\JobOffer;
use FormaLibre\JobBundle\Entity\PendingAnnouncer;
use FormaLibre\JobBundle\Form\AnnouncerType;
use FormaLibre\JobBundle\Form\AnnouncersType;
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
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    private $localeManager;
    private $fileDir;
    private $cvDirectory;
    private $offersDirectory;
    private $extGuesser;

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
     *     "userManager"   = @DI\Inject("claroline.manager.user_manager"),
     *     "localeManager" = @DI\Inject("claroline.common.locale_manager"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "fileDir"       = @DI\Inject("%claroline.param.files_directory%"),
     *     "extGuesser"    = @DI\Inject("claroline.utilities.mime_type_guesser")
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
        UserManager $userManager,
        LocaleManager $localeManager,
        TokenStorageInterface $tokenStorage,
        $fileDir,
        $extGuesser
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
        $this->localeManager = $localeManager;
        $this->tokenStorage = $tokenStorage;
        $this->fileDir = $fileDir;
        $this->cvDirectory = $fileDir . '/jobbundle/cv/';
        $this->offersDirectory = $fileDir . '/jobbundle/offers/';
        $this->extGuesser = $extGuesser;
    }

    /**
     * @EXT\Route(
     *     "/pending/announcer/create/form/{lang}",
     *     name="formalibre_job_pending_announcer_create_form",
     *     options={"expose"=true},
     *     defaults={"lang"="fr"}
     * )
     * @EXT\Template()
     */
    public function pendingAnnouncerCreateFormAction($lang)
    {
        $this->request->setLocale($lang);
        $form = $this->formFactory->create(
            new PendingAnnouncerType($lang),
            new User()
        );

        return array('form' => $form->createView(), 'lang' => $lang);
    }

    /**
     * @EXT\Route(
     *     "/pending/announcer/create/{lang}",
     *     name="formalibre_job_pending_announcer_create",
     *     options={"expose"=true},
     *     defaults={"lang"="fr"}
     * )
     * @EXT\Template("FormaLibreJobBundle:Job:pendingAnnouncerCreateForm.html.twig")
     */
    public function pendingAnnouncerCreateAction($lang)
    {
        $user = new User();

        $form = $this->formFactory->create(
            new PendingAnnouncerType(),
            $user
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user->setLocale($lang);
            $this->roleManager->setRoleToRoleSubject(
                $user,
                $this->configHandler->getParameter('default_role')
            );
            $user = $this->userManager->createUserWithRole(
                $user,
                PlatformRoles::USER
            );
            
            $this->facetManager->setFieldValue(
                $user, 
                $this->jobManager->getFieldFacet('fase_number'), 
                $form->get('faseNumber')->getData(),
                true
            );
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
                        //'%registrationNumber%' => $form->get('registrationNumber')->getData(),
                        '%faseNumber%' => $form->get('faseNumber')->getData(),
                        '%phone%' => $user->getPhone(),
                        '%url%' => $this->generateUrl(
                            'formalibre_job_pending_announcers_management',
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

            return $this->redirect($this->generateUrl('claro_security_login'));
        } else {

            return array('form' => $form->createView(), 'lang' => $lang);
        }
    }

    /**
     * @EXT\Route(
     *     "/seeker/create/form/{lang}",
     *     name="formalibre_job_seeker_create_form",
     *     options={"expose"=true},
     *     defaults={"lang"="fr"}
     * )
     * @EXT\Template()
     */
    public function seekerCreateFormAction($lang)
    {
        $this->request->setLocale($lang);
        $form = $this->formFactory->create(
            new SeekerType($lang),
            new User()
        );

        return array('form' => $form->createView(), 'lang' => $lang);
    }

    /**
     * @EXT\Route(
     *     "/seeker/create/{lang}",
     *     name="formalibre_job_seeker_create",
     *     options={"expose"=true},
     *     defaults={"lang"="fr"}
     * )
     * @EXT\Template("FormaLibreJobBundle:Job:seekerCreateForm.html.twig")
     */
    public function seekerCreateAction($lang)
    {
        $user = new User();

        $form = $this->formFactory->create(
            new SeekerType(),
            $user
        );
        $user->setLocale($lang);
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
            
            $this->facetManager->setFieldValue(
                $user, 
                $this->jobManager->getFieldFacet('registration_number'), 
                $form->get('registrationNumber')->getData(),
                true
            );
            
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
                $jobRequest->setTitle($form->get('cv_title')->getData());
                $jobRequest->setVisible($form->get('visible')->getData());
                $jobRequest->setExpirationDate($form->get('expirationDate')->getData());
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

            return array('form' => $form->createView(), 'lang' => $lang);
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
        $communities = $this->jobManager->getAllCommunities();
        
        return array('communities' => $communities);
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
        
        $communities = $this->jobManager->getAllCommunities();

        return array(
            'jobRequests' => $jobRequests,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'communities' => $communities
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
            }
            $this->jobManager->persistJobOffer($jobOffer);

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

    /**
     * @EXT\Route(
     *     "/announcers/management/menu",
     *     name="formalibre_job_announcers_management_menu",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function announcersManagementMenuAction(User $authenticatedUser)
    {
        $communities = $this->jobManager->getCommunitiesByUser($authenticatedUser);

        return array('communities' => $communities);
    }

    /**
     * @EXT\Route(
     *     "/community/{community}/annnouncers/management",
     *     name="formalibre_job_announcers_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function announcersManagementAction(User $authenticatedUser, Community $community)
    {
        $this->checkCommunityAdminAccess($community, $authenticatedUser);
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
     *     "/community/{community}/pending/annnouncers/management",
     *     name="formalibre_job_pending_announcers_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function pendingAnnouncersManagementAction(User $authenticatedUser, Community $community)
    {
        $this->checkCommunityAdminAccess($community, $authenticatedUser);
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
     *     "/community/{community}/announcers/create/form",
     *     name="formalibre_job_announcers_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:Job:announcersCreateModalForm.html.twig")
     */
    public function announcersCreateFormAction(User $authenticatedUser, Community $community)
    {
        $this->checkCommunityAdminAccess($community, $authenticatedUser);
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
     *     "/community/{community}/announcers/create",
     *     name="formalibre_job_announcers_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreJobBundle:Job:announcersCreateModalForm.html.twig")
     */
    public function announcersCreateAction(User $authenticatedUser, Community $community)
    {
        $this->checkCommunityAdminAccess($community, $authenticatedUser);
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
     *     "/announcer/{announcer}/delete",
     *     name="formalibre_job_announcer_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function deleteAnnouncerAction(User $authenticatedUser, Announcer $announcer)
    {
        $community = $announcer->getCommunity();
        $this->checkCommunityAdminAccess($community, $authenticatedUser);
        $this->jobManager->deleteAnnouncer($announcer);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/pending/announcer/{pendingAnnouncer}/accept",
     *     name="formalibre_job_pending_announcer_accept",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function acceptPendingAnnouncerAction(
        User $authenticatedUser,
        PendingAnnouncer $pendingAnnouncer
    )
    {
        $community = $pendingAnnouncer->getCommunity();
        $this->checkCommunityAdminAccess($community, $authenticatedUser);
        $user = $pendingAnnouncer->getUser();
        $this->jobManager->acceptPendingAnnouncer($pendingAnnouncer);

        $object = $this->translator->trans(
            'accept_pending_announcer_object',
            array(),
            'job'
        );
        $content = $this->translator->trans(
            'accept_pending_announcer_content',
            array('%name%' => $user->getFirstName() . ' ' . $user->getLastName()),
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
     *     "/pending/announcer/{pendingAnnouncer}/decline",
     *     name="formalibre_job_pending_announcer_decline",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function declinePendingAnnouncerAction(
        User $authenticatedUser,
        PendingAnnouncer $pendingAnnouncer
    )
    {
        $community = $pendingAnnouncer->getCommunity();
        $this->checkCommunityAdminAccess($community, $authenticatedUser);
        $user = $pendingAnnouncer->getUser();
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
     *     "/job/offers/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}",
     *     name="formalibre_job_job_offers_list",
     *     defaults={"page"=1, "max"=20, "orderedBy"="id","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function jobOffersListAction(
        $page = 1,
        $max = 20,
        $orderedBy = 'id',
        $order = 'DESC'
    )
    {
        $jobOffers = $this->jobManager->getAllAvailableJobOffers(
            true,
            $orderedBy,
            $order,
            $page,
            $max
        );

        return array(
            'jobOffers' => $jobOffers,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }
    
    /**
     * @EXT\Route(
     *     "/open/job/request/{jobRequest}",
     *     name="formalibre_job_request_open",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function openCVAction(JobRequest $jobRequest)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $requestUser = $jobRequest->getUser();
        
        if (!$this->authorization->isGranted('ROLE_JOB_ANNOUNCER') && $currentUser !== $requestUser) {
            throw new AccessDeniedException();
        }
        
        $path = $this->cvDirectory . DIRECTORY_SEPARATOR . $jobRequest->getCv();
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'pdf') return $this->downloadCVAction($jobRequest, 'true');
        
        return array(
            'path' => $path,
            'jobRequest' => $jobRequest
        );
    }
    
    /**
     * @EXT\Route(
     *     "/download/job/request/{jobRequest}/force/{force}",
     *     name="formalibre_job_request_download",
     *     options={"expose"=true},
     *     defaults={"force"="true"}
     * )
     * @EXT\Template()
     */
    public function downloadCVAction(JobRequest $jobRequest, $force)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $requestUser = $jobRequest->getUser();
        
        if (!$this->authorization->isGranted('ROLE_JOB_ANNOUNCER') && ($currentUser !== $requestUser)) {
            throw new AccessDeniedException();
        }
        
        $response = new StreamedResponse();
        $path = $this->cvDirectory . DIRECTORY_SEPARATOR . $jobRequest->getCv();
        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );
        
        
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mimeType = $this->extGuesser->guess($ext);
        $response->headers->set('Content-Type', $mimeType);

        if ($force === 'true') {
            $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
            $response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-Disposition', 'attachment; filename=' . urlencode($jobRequest->getTitle() . '.' . $ext));
        }
        
        return $response;
    }
    
    /**
     * @EXT\Route(
     *     "/job_offers/community/{community}/page/{page}/from/{from}/to/{to}",
     *     name="formalibre_job_offers_open",
     *     defaults={"page"=1, "search"="", "from": "1420153200", "to": "1451689200"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/job_offers/community/{community}/search/{search}/page/{page}/from/{from}/to/{to}",
     *     name="formalibre_job_offers_open_search",
     *     defaults={"page"=1, "from": "1420153200", "to": "1451689200"},
     *     options = {"expose"=true}
     * )
     * @EXT\Template
     */
    public function listJobOffersAction(Community $community, $page, $search, $from, $to)
    {
         $query = $this->jobManager->getJobOffers($community, $search, $from, $to, true);
         $pager = $this->get('claroline.pager.pager_factory')->createPager($query, $page, 25);
         $communities = $this->jobManager->getAllCommunities();
         
         return array(
            'pager' => $pager,
            'search' => $search,
            'community' => $community,
            'page' => $page,
            'from' => $from,
            'to' => $to,
            'communities' => $communities
        );
    }
    
        /**
     * @EXT\Route(
     *     "/open/job/offer/{jobOffer}",
     *     name="formalibre_job_offer_open",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function openJobOfferAction(JobOffer $jobOffer)
    {
        $path = $this->offersDirectory . DIRECTORY_SEPARATOR . $jobOffer->getOffer();
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'pdf') return $this->downloadJobOfferAction($jobOffer, 'true');
        
        return array(
            'path' => $path,
            'jobOffer' => $jobOffer
        );
    }
    
    /**
     * @EXT\Route(
     *     "/download/job/offer/{jobOffer}/force/{force}",
     *     name="formalibre_job_offer_download",
     *     options={"expose"=true},
     *     defaults={"force"="true"}
     * )
     * @EXT\Template()
     */
    public function downloadJobOfferAction(JobOffer $jobOffer, $force)
    {        
        $response = new StreamedResponse();
        $path = $this->offersDirectory . DIRECTORY_SEPARATOR . $jobOffer->getOffer();
        
        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );
        
        
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mimeType = $this->extGuesser->guess($ext);
        $response->headers->set('Content-Type', $mimeType);

        if ($force === 'true') {
            $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
            $response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-Disposition', 'attachment; filename=' . urlencode($jobOffer->getTitle() . '.' . $ext));
        }
        
        return $response;
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

    private function checkCommunityAdminAccess(Community $community, User $user)
    {
        $admins = $community->getAdmins();

        if (!in_array($user, $admins)) {

            throw new AccessDeniedException();
        }
    }
}
