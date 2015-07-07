<?php

namespace FormaLibre\JobBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\JobBundle\Repository\AnnouncerRepository")
 * @ORM\Table(name="formalibre_jobbundle_announcer")
 */
class Announcer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\JobBundle\Entity\Community"
     * )
     * @ORM\JoinColumn(name="community_id", onDelete="CASCADE")
     */
    protected $community;

    /**
     * @ORM\Column(name="with_notification", type="boolean")
     */
    protected $withNotification = true;

    /**
     * @ORM\Column(name="fase_number")
     */
    protected $faseNumber;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\JobBundle\Entity\Province"
     * )
     * @ORM\JoinColumn(name="province_id", onDelete="CASCADE")
     */
    protected $province;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $adress;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $establishment;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getCommunity()
    {
        return $this->community;
    }

    public function setCommunity(Community $community)
    {
        $this->community = $community;
    }

    public function getWithNotification()
    {
        return $this->withNotification;
    }

    public function setWithNotification($withNotification)
    {
        $this->withNotification = $withNotification;
    }

    public function getFaseNumber()
    {
        return $this->faseNumber;
    }

    public function setFaseNumber($faseNumber)
    {
        $this->faseNumber = $faseNumber;
    }

    public function setAdress($adress)
    {
        $this->adress = $adress;
    }

    public function getAdress()
    {
        return $this->adress;
    }

    public function setProvince(Province $province)
    {
        $this->province = $province;
    }

    public function getProvince()
    {
        return $this->province;
    }

    public function getEstablishment()
    {
        return $this->establishment;
    }

    public function setEstablishment($establishment)
    {
        $this->establishment = $establishment;
    }
}
