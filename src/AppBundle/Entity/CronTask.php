<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CronTask
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class CronTask
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="commands", type="array")
     */
    private $commands;

    /**
     * @var integer
     *
     * @ORM\Column(name="taskInterval", type="integer")
     */
    private $taskInterval;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastrun", type="datetime")
     */
    private $lastrun;


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
     * Set name
     *
     * @param string $name
     * @return CronTask
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set commands
     *
     * @param array $commands
     * @return CronTask
     */
    public function setCommands($commands)
    {
        $this->commands = $commands;

        return $this;
    }

    /**
     * Get commands
     *
     * @return array 
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Set taskInterval
     *
     * @param integer $taskInterval
     * @return CronTask
     */
    public function setTaskInterval($taskInterval)
    {
        $this->taskInterval = $taskInterval;

        return $this;
    }

    /**
     * Get taskInterval
     *
     * @return integer 
     */
    public function getTaskInterval()
    {
        return $this->taskInterval;
    }

    /**
     * Set lastrun
     *
     * @param \DateTime $lastrun
     * @return CronTask
     */
    public function setLastrun($lastrun)
    {
        $this->lastrun = $lastrun;

        return $this;
    }

    /**
     * Get lastrun
     *
     * @return \DateTime 
     */
    public function getLastrun()
    {
        return $this->lastrun;
    }
}
