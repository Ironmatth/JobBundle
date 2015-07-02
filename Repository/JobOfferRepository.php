<?php

namespace FormaLibre\JobBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FormaLibre\JobBundle\Entity\Announcer;

class JobOfferRepository extends EntityRepository
{
    public function findJobOffersByAnnouncer(
        Announcer $announcer,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT o
            FROM FormaLibre\JobBundle\Entity\JobOffer o
            WHERE o.announcer = :announcer
            ORDER BY o.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('announcer', $announcer);

        return $executeQuery ? $query->getResult() : $query;
    }
}
