<?php

namespace FormaLibre\JobBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CommunityRepository extends EntityRepository
{
    public function findAllCommunities(
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT c
            FROM FormaLibre\JobBundle\Entity\Community c
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }
}
