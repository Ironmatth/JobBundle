<?php

namespace FormaLibre\JobBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnnouncersType extends AbstractType
{
    private $users;

    public function __construct(array $users = array())
    {
        $this->users = $users;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'announcers',
            'userpicker',
            array(
                'mapped' => false,
                'required' => false,
                'label' => 'announcers',
                'multiple' => true,
                'show_filters' => false,
                'blacklist' => $this->users
            )
        );
    }
    public function getName()
    {
        return 'community_form';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'job'));
    }
}
