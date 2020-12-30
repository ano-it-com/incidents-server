<?php

namespace App\Entity\Incident;

use App\Entity\File\FileOwnerInterface;
use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Comment\Comment;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Repository\Incident\IncidentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncidentRepository::class)
 * @ORM\Table(name="incidents")
 */
class Incident implements FileOwnerInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=IncidentType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb": true})
     */
    private $info = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class)
     * @ORM\JoinTable(
     *  name="incident_responsible_groups",
     *  joinColumns={
     *      @ORM\JoinColumn(name="incident_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *  }
     * )
     */
    private $responsibleGroups;

    /**
     * @ORM\OneToOne(targetEntity=IncidentStatus::class, cascade={"persist", "remove"})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Incident::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $repeatedIncident;

    /**
     * @ORM\OneToMany(targetEntity=IncidentStatus::class, mappedBy="incident")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $statuses;

    /**
     * @ORM\OneToMany(targetEntity=Action::class, mappedBy="incident")
     */
    private $actions;


    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $updatedBy;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;


    public function __construct()
    {
        $this->deleted           = false;
        $this->createdAt         = new \DateTimeImmutable();
        $this->updatedAt         = new \DateTimeImmutable();
        $this->statuses          = new ArrayCollection();
        $this->actions           = new ArrayCollection();
        $this->responsibleGroups = new ArrayCollection();
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


    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }


    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }


    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }


    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }


    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }


    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    public function getUpdatedBy(): User
    {
        return $this->updatedBy;
    }


    public function setUpdatedBy(User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

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
     * @return Collection|IncidentStatus[]
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }


    public function addStatus(IncidentStatus $status): self
    {
        if ( ! $this->statuses->contains($status)) {
            $this->statuses[] = $status;
            $status->setIncident($this);
        }

        return $this;
    }


    public function removeStatus(IncidentStatus $status): self
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
            // set the owning side to null (unless already changed)
            if ($status->getIncident() === $this) {
                $status->setIncident(null);
            }
        }

        return $this;
    }


    public function addAction(Action $action): self
    {
        if ( ! $this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setIncident($this);
        }

        return $this;
    }


    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getIncident() === $this) {
                $action->setIncident(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|Group[]
     */
    public function getResponsibleGroups(): Collection
    {
        return $this->responsibleGroups;
    }


    public function addResponsibleGroup(Group $responsibleGroup): self
    {
        if ( ! $this->responsibleGroups->contains($responsibleGroup)) {
            $this->responsibleGroups[] = $responsibleGroup;
        }

        return $this;
    }


    public function removeResponsibleGroup(Group $responsibleGroup): self
    {
        if ($this->responsibleGroups->contains($responsibleGroup)) {
            $this->responsibleGroups->removeElement($responsibleGroup);
        }

        return $this;
    }


    public function getActionBy(int $actionId): ?Action
    {
        foreach ($this->getActions() as $action) {
            if ($action->getId() === $actionId) {
                return $action;
            }
        }

        return null;
    }


    /**
     * @return Collection|Action[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }


    public function getStatusCode(): ?string
    {
        $status = $this->getStatus();
        if ( ! $status) {
            return null;
        }

        return $status->getCode();
    }


    public function getStatus(): ?IncidentStatus
    {
        return $this->status;
    }


    public function setStatus(?IncidentStatus $status): self
    {
        $this->status = $status;

        return $this;
    }


    public static function getOwnerCode(): string
    {
        return 'incident';
    }


    public function getRepeatedIncident(): ?Incident
    {
        return $this->repeatedIncident;
    }


    public function setRepeatedIncident(?Incident $repeatedIncident): self
    {
        $this->repeatedIncident = $repeatedIncident;

        return $this;
    }


    public function getType(): IncidentType
    {
        return $this->type;
    }


    public function setType(IncidentType $type): self
    {
        $this->type = $type;

        return $this;
    }


    public function getInfo(): ?array
    {
        return $this->info;
    }


    public function setInfo(?array $info): self
    {
        $this->info = $info;

        return $this;
    }

}
