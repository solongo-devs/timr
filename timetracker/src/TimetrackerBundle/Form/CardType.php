<?php

namespace TimetrackerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CardType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('signature')
            ->add('employee', 'entity', [
                'class'     => 'TimetrackerBundle:Employee',
                'property'  => 'fullName',
            ]);
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TimetrackerBundle\Entity\Card'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'timetrackerbundle_card';
    }
}
