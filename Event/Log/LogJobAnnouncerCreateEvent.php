<?php

namespace FormaLibre\JobBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use FormaLibre\JobBundle\Entity\Announcer;

class LogJobAnnouncerCreateEvent extends LogGenericEvent
{
    const ACTION = 'job-announcer-creation';

    public function __construct(Announcer $announcer)
    {
        $user = $announcer->getUser();
        $community = $announcer->getCommunity();
        $details = array();
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['community'] = $community->getName();

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
