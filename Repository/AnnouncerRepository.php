<?php

namespace FormaLibre\JobBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use FormaLibre\JobBundle\Entity\Community;

class AnnouncerRepository extends EntityRepository
{
    public function findAllAnnouncers(
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT a
            FROM FormaLibre\JobBundle\Entity\Announcer a
            ORDER BY a.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAnnouncersByUser(
        User $user,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT a
            FROM FormaLibre\JobBundle\Entity\Announcer a
            WHERE a.user = :user
            ORDER BY a.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAnnouncerByUserAndCommunity(
        User $user,
        Community $community,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT a
            FROM FormaLibre\JobBundle\Entity\Announcer a
            WHERE a.user = :user
            WHERE a.community = :community
            ORDER BY a.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('community', $community);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findAnnouncersByCommunity(
        Community $community,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT a
            FROM FormaLibre\JobBundle\Entity\Announcer a
            WHERE a.community = :community
            ORDER BY a.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('community', $community);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findNotifiableAnnouncersByCommunity(
        Community $community,
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT a
            FROM FormaLibre\JobBundle\Entity\Announcer a
            WHERE a.community = :community
            AND a.withNotification = true
            ORDER BY a.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('community', $community);

        return $executeQuery ? $query->getResult() : $query;
    }
}
