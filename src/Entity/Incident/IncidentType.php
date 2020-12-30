<?php

namespace App\Entity\Incident;

use App\Entity\Incident\Action\ActionTaskType;
use App\Entity\Incident\Action\ActionType;
use App\Entity\Incident\ActionsTemplate\ActionsTemplate;
use App\Repository\Incident\IncidentTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncidentTypeRepository::class)
 * @ORM\Table(name="incident_types")
 */
class IncidentType
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $handler;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\ManyToMany(targetEntity=ActionType::class)
     * @ORM\JoinTable(
     *  name="incident_type_action_types",
     *  joinColumns={
     *      @ORM\JoinColumn(name="incident_type_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="action_type_id", referencedColumnName="id")
     *  }
     * )
     */
    private $actionTypes;

    /**
     * @ORM\OneToMany(targetEntity=ActionsTemplate::class, mappedBy="incidentType")
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    private $actionsTemplates;


    public function __construct()
    {
        $this->deleted = false;
        $this->actionTypes = new ArrayCollection();
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


    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function setDescription(?string $description): self
    {
        $this->description = $description;

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


    public function getDeleted(): bool
    {
        return $this->deleted;
    }


    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }


    /**
     * @return Collection|ActionTaskType []
     */
    public function getActionTypes(): Collection
    {
        return $this->actionTypes;
    }


    public function addActionType(ActionType $actionType): self
    {
        if ( ! $this->actionTypes->contains($actionType)) {
            $this->actionTypes[] = $actionType;
        }

        return $this;
    }


    public function removeActionType(ActionType $actionType): self
    {
        if ($this->actionTypes->contains($actionType)) {
            $this->actionTypes->removeElement($actionType);
        }

        return $this;
    }


    /**
     * @return Collection|ActionsTemplate[]
     */
    public function getActionsTemplates(): Collection
    {
        return $this->actionsTemplates;
    }


    public function addActionsTemplate(ActionsTemplate $actionsTemplate): self
    {
        if ( ! $this->actionsTemplates->contains($actionsTemplate)) {
            $this->actionsTemplates[] = $actionsTemplate;
            $actionsTemplate->setIncidentType($this);
        }

        return $this;
    }


    public function removeActionsTemplate(ActionsTemplate $actionsTemplate): self
    {
        if ($this->actionsTemplates->contains($actionsTemplate)) {
            $this->actionsTemplates->removeElement($actionsTemplate);
            // set the owning side to null (unless already changed)
            if ($actionsTemplate->getIncidentType() === $this) {
                $actionsTemplate->setIncidentType(null);
            }
        }

        return $this;
    }

}
