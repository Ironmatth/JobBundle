<?php

namespace FormaLibre\JobBundle\Repository;

use Claroline\CoreBundle\Entity\User;
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

    public function findCommunitiesByUser(
        User $user,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT c
            FROM FormaLibre\JobBundle\Entity\Community c
            JOIN c.admins a
            WHERE a = :user
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findCommunityByLocale($locale)
    {
        $dql = '
            SELECT c
            FROM FormaLibre\JobBundle\Entity\Community c
            WHERE c.locale = :locale
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('locale', $locale);

        return $query->getOneOrNullResult();
    }
}
