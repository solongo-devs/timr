<?php

namespace TimetrackerBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="role")
 * @ORM\Entity()
 */
class Role implements RoleInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=30)
     */
    private $name;

    /**
     * @ORM\Column(name="role", type="string", length=20, unique=true)
     */
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity="Employee", mappedBy="roles")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @see RoleInterface
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @see RoleInterface
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @see RoleInterface
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @see RoleInterface
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @see RoleInterface
     */
    public function setId($value)
    {
        $this->id = $value;
    }

    /**
     * @see RoleInterface
     */
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     * @see RoleInterface
     */
    public function setRole($value)
    {
        $this->role = $value;
    }

    /**
     * @see RoleInterface
     */
    public function setUsers($value)
    {
        $this->users = $value;
    }
    
}

?>