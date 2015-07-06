<?php

namespace FormaLibre\JobBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class SeekerRepository extends EntityRepository
{
    public function findSeekerByUser(User $user)
    {
        $dql = '
            SELECT s
            FROM FormaLibre\JobBundle\Entity\Seeker s
            WHERE s.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getOneOrNullResult();
    }
}
