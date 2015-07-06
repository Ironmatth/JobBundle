<?php

namespace FormaLibre\JobBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="formalibre_jobbundle_province")
 */
class Province
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
    protected $translationKey;

    function getId()
    {
        return $this->id;
    }

    function setTranslationKey($translationKey)
    {
        $this->translationKey = $translationKey;
    }

    function getTranslationKey()
    {
        return $this->translationKey;
    }

}
