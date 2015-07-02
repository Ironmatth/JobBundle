<?php

namespace FormaLibre\JobBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use FormaLibre\JobBundle\Entity\JobRequest;

class LogJobRequestCreateEvent extends LogGenericEvent
{
    const ACTION = 'job-request-creation';

    public function __construct(JobRequest $jobRequest)
    {
        $user = $jobRequest->getUser();
        $community = $jobRequest->getCommunity();
        $details = array();
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['community'] = $community->getName();
        $details['title'] = $jobRequest->getTitle();
        $details['cv'] = $jobRequest->getCv();
        $details['originalCVName'] = $jobRequest->getOriginalName();

        parent::__construct(
            self::ACTION,
            $details,
            $user
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }
}
