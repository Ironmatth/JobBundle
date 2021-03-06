<?php

namespace FormaLibre\JobBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PendingAnnouncerType extends AbstractType
{
    private $lang;

    public function __construct($lang = null)
    {
        $this->lang = $lang;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'lastName',
            'text',
            array(
                'required' => true,
                'translation_domain' => 'platform',
                'label' => 'last_name'
            )
        );
        $builder->add(
            'firstName',
            'text',
            array(
                'required' => true,
                'translation_domain' => 'platform',
                'label' => 'first_name'
            )
        );
        $builder->add(
            'faseNumber',
            'text',
            array(
                'required' => false,
                'translation_domain' => 'job',
                'label' => 'fase_number',
                'mapped' => false
            )
        );
        $builder->add(
            'mail',
            'email',
            array(
                'required' => true,
                'translation_domain' => 'platform',
                'label' => 'email'
            )
        );
        $builder->add(
            'establishment',
            'text',
            array(
                'required' => false,
                'label' => 'establishment',
                'translation_domain' => 'job',
                'mapped' => false
            )
        );
        $builder->add(
            'province',
            'entity',
            array(
                'label' => 'province',
                'class' => 'FormaLibreJobBundle:Province',
                'choice_translation_domain' => true,
                'translation_domain' => 'province',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p');
                },
                'property' => 'translation_key',
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'mapped' => false
            )
        );
        $builder->add(
            'adress',
            'text',
            array(
                'required' => true,
                'translation_domain' => 'job',
                'label' => 'adress',
                'mapped' => false
            )
        );
        $builder->add(
            'phone',
            'text',
            array(
                'required' => false,
                'translation_domain' => 'platform',
                'label' => 'phone'
            )
        );
        $builder->add(
            'username',
            'text',
            array(
                'required' => true,
                'translation_domain' => 'platform',
                'label' => 'username'
            )
        );
        $builder->add(
            'plainPassword',
            'repeated',
            array(
                'required' => true,
                'translation_domain' => 'platform',
                'type' => 'password',
                'first_options' => array('label' => 'password'),
                'second_options' => array('label' => 'password_verification')
            )
        );
        $builder->add(
            'withNotification',
            'checkbox',
            array(
                'required' => true,
                'label' => 'allow_notification_for_new_job_request',
                'mapped' => false,
                'attr' => array('checked' => 'checked'),
                'translation_domain' => 'job',
            )
        );
    }
    public function getName()
    {
        return 'pending_announcer_form';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'job'));
    }
}
