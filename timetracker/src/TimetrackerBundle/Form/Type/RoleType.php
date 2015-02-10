<?php

namespace TimetrackerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TimetrackerBundle\Entity\RoleToNumberTransformer;

class RoleType extends AbstractType

{
    /**
     * @var ObjectManager
     */
    private $om;

    private $choices;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        // Build our choices array from the database
        $roles = $om->getRepository('TimetrackerBundle:Role')->findAll();
        foreach ($roles as $role)
        {
            // choices[key] = label
            $this->choices[$role->getId()] = $role->getName();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new RoleToNumberTransformer($this->om);
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                "choices" => $this->choices,
                ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'group_select';
    }
}

?>