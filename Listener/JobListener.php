<?php

namespace FormaLibre\JobBundle\Listener;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class JobListener
{
    private $httpKernel;
    private $request;

    /**
     * @DI\InjectParams({
     *     "httpKernel"   = @DI\Inject("http_kernel"),
     *     "requestStack" = @DI\Inject("request_stack")
     * })
     */
    public function __construct(HttpKernelInterface $httpKernel,RequestStack $requestStack)
    {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @DI\Observe("administration_tool_formalibre_job_admin_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibreJobBundle:AdminJob:adminToolIndex';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_formalibre_announcer_widget")
     *
     * @param DisplayWidgetEvent $event
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onAnnouncerWidgetDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->request) {

            throw new NoHttpRequestException();
        }
        $widgetInstance = $event->getInstance();
        $params = array();
        $params['_controller'] = 'FormaLibreJobBundle:Job:announcerWidget';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate(
            array(),
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_formalibre_seeker_widget")
     *
     * @param DisplayWidgetEvent $event
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onSeekerWidgetDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->request) {

            throw new NoHttpRequestException();
        }
        $widgetInstance = $event->getInstance();
        $params = array();
        $params['_controller'] = 'FormaLibreJobBundle:Job:seekerWidget';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate(
            array(),
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_formalibre_admin_widget")
     *
     * @param DisplayWidgetEvent $event
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onAdminWidgetDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->request) {

            throw new NoHttpRequestException();
        }
        $widgetInstance = $event->getInstance();
        $params = array();
        $params['_controller'] = 'FormaLibreJobBundle:Job:adminWidget';
        $params['widgetInstance'] = $widgetInstance->getId();
        $subRequest = $this->request->duplicate(
            array(),
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }
}
