<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Card
 *
 * @ORM\Table(name="cards")
 * @ORM\Entity
 *
 * @UniqueEntity("signature")
 */
class Card
{
    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="string", length=255, options={"unsigned"=true})
     * @ORM\Id
     */
    private $signature;

    /**
     * @var integer
     *
     * @ORM\Column(name="employee_id", type="integer")
     */
    private $employeeId;

    /**
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="card")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id")
     */
    protected $employee;

    /**
     * @ORM\OneToMany(targetEntity="Log", mappedBy="card")
     * @ORM\OrderBy({"time" = "ASC"})
     */
    protected $logs;

    public function __construct()
    {
        $this->logs = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set signature
     *
     * @param string $signature
     * @return Card
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set employeeId
     *
     * @param integer $employeeId
     * @return Card
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    /**
     * Get employeeId
     *
     * @return integer 
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * Set employee
     *
     * @param \TimetrackerBundle\Entity\Employee $employee
     * @return Card
     */
    public function setEmployee(\TimetrackerBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return \TimetrackerBundle\Entity\Employee 
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Card
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Add logs
     *
     * @param \TimetrackerBundle\Entity\Log $logs
     * @return Card
     */
    public function addLog(\TimetrackerBundle\Entity\Log $logs)
    {
        $this->logs[] = $logs;

        return $this;
    }

    /**
     * Remove logs
     *
     * @param \TimetrackerBundle\Entity\Log $logs
     */
    public function removeLog(\TimetrackerBundle\Entity\Log $logs)
    {
        $this->logs->removeElement($logs);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLogs()
    {
        return $this->logs;
    }
}
