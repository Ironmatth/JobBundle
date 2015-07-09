<?php

namespace FormaLibre\JobBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SeekerType extends AbstractType
{
    private $lang;

    public function __construct($lang = null)
    {
        $this->lang = $lang;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $lang = $this->lang;
        $builder->add(
            'userType',
            'choice',
            array(
                'label' => 'you_are',
                'translation_domain' => 'job',
                'choices' => array(
                    'announcer' => 'school_director',
                    'seeker' => 'teacher'
                ),
                'multiple' => false,
                'required' => false,
                'mapped' => false
            )
        );
        $builder->add(
            'community',
            'entity',
            array(
                'label' => 'candidate_for',
                'translation_domain' => 'job',
                'class' => 'FormaLibreJobBundle:Community',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) use ($lang) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC')
                        ->where('c.locale != ?1')
                        ->setParameter(1, $lang);
                },
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'mapped' => false
            )
        );
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
            'registrationNumber',
            'text',
            array(
                'required' => true,
                'translation_domain' => 'job',
                'label' => 'registration_number',
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
            'file',
            'file',
            array(
                'translation_domain' => 'job',
                'mapped' => false,
                'required' => false,
                'label' => 'cv'
            )
        );
        $builder->add(
            'cv_title',
            'text',
            array(
                'mapped' => false,
                'required' => true,
                'translation_domain' => 'job',
                'label' => 'cv_title'
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
                'input' => 'datetime',
                'mapped' => false,
                'translation_domain' => 'job',
                'label' => 'cv_expiration_date'
            )
        );
        $builder->add(
            'visible',
            'checkbox',
            array(
                'required' => true,
                'label' => 'accept_visibility_message',
                'translation_domain' => 'job',
                'constraints' => array(new NotBlank()),
                'mapped' => false
            )
        );
    }

    public function getName()
    {
        return 'seeker_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'job',
                'language' => $this->lang
            )
        );
    }
}
