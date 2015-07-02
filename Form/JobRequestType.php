<?php

namespace FormaLibre\JobBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'community',
            'entity',
            array(
                'label' => 'candidate_for',
                'class' => 'FormaLibreJobBundle:Community',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) {

                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'required' => true
            )
        );
        $builder->add(
            'title',
            'text',
            array(
                'required' => true,
                'label' => 'title',
                'translation_domain' => 'platform'
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
                'label' => 'cv'
            )
        );
    }
    public function getName()
    {
        return 'job_request_form';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'job'));
    }
}
