<?php

namespace FormaLibre\JobBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            'text',
            array(
                'required' => true,
                'label' => 'title',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'phone',
            'text',
            array(
                'required' => false,
                'label' => 'phone',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'establishment',
            'text',
            array(
                'required' => false,
                'label' => 'establishment',
                'translation_domain' => 'job'
            )
        );
        $builder->add(
            'immersion',
            'checkbox',
            array(
                'required' => true,
                'label' => 'immersion',
                'translation_domain' => 'job'
            )
        );
        $builder->add(
            'discipline',
            'text',
            array(
                'required' => false,
                'label' => 'discipline',
                'translation_domain' => 'job'
            )
        );
        $builder->add(
            'level',
            'text',
            array(
                'required' => false,
                'label' => 'level',
                'translation_domain' => 'job'
            )
        );
        $builder->add(
            'duration',
            'text',
            array(
                'required' => false,
                'label' => 'duration',
                'translation_domain' => 'job'
            )
        );
        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';
        $builder->add(
            'expirationDate',
            'datepicker',
            array(
                'required' => false,
                'format' => 'dd-mm-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime'
            )
        );
        $builder->add(
            'file',
            'file',
            array(
                'mapped' => false,
                'required' => false,
                'label' => 'job_offer'
            )
        );
    }
    public function getName()
    {
        return 'job_offer_form';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'job'));
    }
}
