<?php

namespace FormaLibre\JobBundle\Form;

use Doctrine\ORM\EntityRepository;
use FormaLibre\JobBundle\Entity\Community;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class JobRequestType extends AbstractType
{
    private $community;

    public function __construct(Community $community)
    {
        $this->community = $community;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $community = $this->community;

        $builder->add(
            'community',
            'entity',
            array(
                'label' => 'candidate_for',
                'class' => 'FormaLibreJobBundle:Community',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) use ($community) {

                    return $er->createQueryBuilder('c')
                        ->where('c.id != :communityId')
                        ->setParameter('communityId', $community->getId())
                        ->orderBy('c.name', 'ASC');
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
                'format' => 'dd-MM-yyyy',
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
        $builder->add(
            'visible',
            'checkbox',
            array(
                'required' => true,
                'label' => 'accept_visibility_message',
                'translation_domain' => 'job',
                'constraints' => array(new NotBlank())
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
