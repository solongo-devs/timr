<?php

namespace TimetrackerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use TimetrackerBundle\Entity\RoleToNumberTransformer;

class UserType extends AbstractType
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$transformer = new RoleToNumberTransformer($this->entityManager);

        $builder->add('id', 'hidden')
			->add('username', 'text', array('disabled' => true))
			->add('save', 'submit')
			->add('roles', 'entity', array(
				'class' => 'TimetrackerBundle:Role',
    			'property' => 'name',
    			'multiple' => true,
    			'expanded' => true,
			));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TimetrackerBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'user';
    }
}

?>