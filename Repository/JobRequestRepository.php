<?php

namespace FormaLibre\JobBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use FormaLibre\JobBundle\Entity\Community;

class JobRequestRepository extends EntityRepository
{
    public function findJobRequestsByUser(
        User $user,
        $orderedBy = 'id',
        $order = 'ASC'
    )
    {
        $dql = "
            SELECT r
            FROM FormaLibre\JobBundle\Entity\JobRequest r
            WHERE r.user = :user
            ORDER BY r.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findAvailableJobRequestsByCommunity(
        Community $community,
        $orderedBy = 'id',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT r
            FROM FormaLibre\JobBundle\Entity\JobRequest r
            WHERE r.community = :community
            AND r.visible = true
            AND (
                r.expirationDate IS NULL
                OR r.expirationDate > :now
            )
            ORDER BY r.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('community', $community);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }
}
