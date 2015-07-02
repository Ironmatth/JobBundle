<?php

namespace FormaLibre\JobBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use FormaLibre\JobBundle\Entity\JobOffer;

class LogJobOfferCreateEvent extends LogGenericEvent
{
    const ACTION = 'job-offer-creation';

    public function __construct(JobOffer $jobOffer)
    {
        $announcer = $jobOffer->getAnnouncer();
        $community = $jobOffer->getCommunity();
        $user = $announcer->getUser();
        $details = array();
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['community'] = $community->getName();
        $details['title'] = $jobOffer->getTitle();
        $details['code'] = $jobOffer->getCode();
        $details['offer'] = $jobOffer->getOffer();
        $details['originalOfferName'] = $jobOffer->getOriginalName();

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
