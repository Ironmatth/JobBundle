<?php

namespace FormaLibre\JobBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\JobBundle\Repository\CommunityRepository")
 * @ORM\Table(name="formalibre_jobbundle_community")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Community
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinTable(name="formalibre_jobbundle_community_admins")
     */
    protected $admins;
    /**
     * @ORM\Column(nullable = true)
     */
    protected $locale;

    public function __construct()
    {
        $this->admins = new ArrayCollection();
    }

    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function getName()
    {
        return $this->name;
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function getAdmins()
    {
        return $this->admins->toArray();
    }

    public function addAdmin(User $user)
    {
        if (!$this->admins->contains($user)) {
            $this->admins->add($user);
        }

        return $this;
    }

    public function removeAdmin(User $user)
    {
        if ($this->admins->contains($user)) {
            $this->admins->removeElement($user);
        }

        return $this;
    }

    function getLocale()
    {
        return $this->locale;
    }

    function setLocale($locale)
    {
        $this->locale = $locale;
    }
}
