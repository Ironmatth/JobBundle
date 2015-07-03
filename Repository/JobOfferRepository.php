<?php

namespace FormaLibre\JobBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FormaLibre\JobBundle\Entity\Announcer;
use FormaLibre\JobBundle\Entity\Community;

class JobOfferRepository extends EntityRepository
{
    public function findJobOffersByAnnouncer(
        Announcer $announcer,
        $orderedBy = 'id',
        $order = 'ASC'
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

        return $query->getResult();
    }

    public function findAvailableJobOffersByCommunity(
        Community $community,
        $orderedBy = 'id',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT o
            FROM FormaLibre\JobBundle\Entity\JobOffer o
            WHERE o.community = :community
            AND (
                o.expirationDate IS NULL
                OR o.expirationDate > :now
            )
            ORDER BY o.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('community', $community);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }

    public function findAllAvailableJobOffers($orderedBy = 'id', $order = 'DESC')
    {
        $dql = "
            SELECT o
            FROM FormaLibre\JobBundle\Entity\JobOffer o
            WHERE o.expirationDate IS NULL
            OR o.expirationDate > :now
            ORDER BY o.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }
}
