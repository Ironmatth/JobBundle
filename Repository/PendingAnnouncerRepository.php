<?php

namespace FormaLibre\JobBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FormaLibre\JobBundle\Entity\Community;

class PendingAnnouncerRepository extends EntityRepository
{
    public function findPendingAnnouncersByCommunity(
        Community $community,
        $orderedBy = 'applicationDate',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT p
            FROM FormaLibre\JobBundle\Entity\PendingAnnouncer p
            WHERE p.community = :community
            ORDER BY p.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('community', $community);

        return $executeQuery ? $query->getResult() : $query;
    }
}
