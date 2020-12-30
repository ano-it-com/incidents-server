<?php

namespace App\Entity\Incident\Action;

use App\Repository\Incident\Action\ActionTaskTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActionTaskTypeRepository::class)
 * @ORM\Table(name="action_task_types")
 */
class ActionTaskType
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     */
    private $handler;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    public function getHandler(): string
    {
        return $this->handler;
    }


    public function setHandler(string $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

}
