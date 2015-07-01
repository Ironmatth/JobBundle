<?php

namespace FormaLibre\JobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\JobBundle\Repository\JobOfferRepository")
 * @ORM\Table(name="formalibre_jobbundle_job_offer")
 */
class JobOffer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\JobBundle\Entity\Community"
     * )
     * @ORM\JoinColumn(name="community_id", onDelete="CASCADE")
     */
    protected $community;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\JobBundle\Entity\Announcer"
     * )
     * @ORM\JoinColumn(name="announcer_id", onDelete="CASCADE")
     */
    protected $announcer;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $code;

    /**
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     */
    protected $expirationDate;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $offer;

    /**
     * @ORM\Column(name="original_name", nullable=true)
     */
    protected $originalName;

    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function getTitle()
    {
        return $this->title;
    }

    function setTitle($title)
    {
        $this->title = $title;
    }

    function getDescription()
    {
        return $this->description;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function getCommunity()
    {
        return $this->community;
    }

    function setCommunity(Community $community)
    {
        $this->community = $community;
    }

    function getAnnouncer()
    {
        return $this->announcer;
    }

    function setAnnouncer(Announcer $announcer)
    {
        $this->announcer = $announcer;
    }

    function getCode()
    {
        return $this->code;
    }

    function setCode($code)
    {
        $this->code = $code;
    }

    function getExpirationDate()
    {
        return $this->expirationDate;
    }

    function setExpirationDate(\DateTime $expirationDate = null)
    {
        $this->expirationDate = $expirationDate;
    }

    function getOffer()
    {
        return $this->offer;
    }

    function setOffer($offer)
    {
        $this->offer = $offer;
    }

    function getOriginalName()
    {
        return $this->originalName;
    }

    function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
    }
}
