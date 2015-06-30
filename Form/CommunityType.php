<?php

namespace FormaLibre\JobBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommunityType extends AbstractType
{
    private $roles;

    public function __construct(array $roles = array())
    {
        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'required' => true,
                'label' => 'name',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'admins',
            'userpicker',
            array(
                'required' => false,
                'label' => 'administrators',
                'translation_domain' => 'job',
                'multiple' => true,
                'show_filters' => false,
                'forced_roles' => $this->roles
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
