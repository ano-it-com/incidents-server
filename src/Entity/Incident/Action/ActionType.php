<?php

namespace App\Entity\Incident\Action;

use App\Repository\Incident\Action\ActionTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActionTypeRepository::class)
 * @ORM\Table(name="action_types")
 */
class ActionType
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\ManyToMany(targetEntity=ActionTaskType::class)
     * @ORM\JoinTable(
     *  name="action_type_action_task_types",
     *  joinColumns={
     *      @ORM\JoinColumn(name="action_type_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="action_task_type_id", referencedColumnName="id")
     *  }
     * )
     */
    private $actionTaskTypes;


    public function __construct()
    {
        $this->actionTaskTypes = new ArrayCollection();
    }


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


    public function getSort(): ?int
    {
        return $this->sort;
    }


    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }


    public function getActive(): ?bool
    {
        return $this->active;
    }


    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }


    /**
     * @return Collection|ActionTaskType []
     */
    public function getActionTaskTypes(): Collection
    {
        return $this->actionTaskTypes;
    }


    public function addActionTaskType(ActionTaskType $actionTaskType): self
    {
        if ( ! $this->actionTaskTypes->contains($actionTaskType)) {
            $this->actionTaskTypes[] = $actionTaskType;
        }

        return $this;
    }


    public function removeActionTaskType(ActionTaskType $actionTaskType): self
    {
        if ($this->actionTaskTypes->contains($actionTaskType)) {
            $this->actionTaskTypes->removeElement($actionTaskType);
        }

        return $this;
    }
}
