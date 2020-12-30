<?php

namespace App\Entity\Incident\Action;

use App\Entity\File\FileOwnerInterface;
use App\Entity\Incident\Incident;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Repository\Incident\Action\ActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActionRepository::class)
 * @ORM\Table(name="actions")
 */
class Action implements FileOwnerInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ActionType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity=ActionStatus::class, cascade={"persist", "remove"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=ActionStatus::class, mappedBy="action")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $statuses;

    /**
     * @ORM\OneToMany(targetEntity=ActionTask::class, mappedBy="action")
     */
    private $actionTasks;

    /**
     * @ORM\ManyToOne(targetEntity=Incident::class, inversedBy="actions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $incident;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $responsibleGroup;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $responsibleUser;

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

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $templateId;


    public function __construct()
    {
        $this->deleted = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->statuses = new ArrayCollection();
        $this->actionTasks = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getType(): ActionType
    {
        return $this->type;
    }


    public function setType(ActionType $type): self
    {
        $this->type = $type;

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


    public function getResponsibleUser(): ?User
    {
        return $this->responsibleUser;
    }


    public function setResponsibleUser(?User $responsibleUser): self
    {
        $this->responsibleUser = $responsibleUser;

        return $this;
    }


    public function getResponsibleGroup(): Group
    {
        return $this->responsibleGroup;
    }


    public function setResponsibleGroup(Group $responsibleGroup): self
    {
        $this->responsibleGroup = $responsibleGroup;

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


    public function getStatusCode(): ?string
    {
        $status = $this->getStatus();
        if (!$status) {
            return null;
        }

        return $status->getCode();
    }


    public function getStatus(): ?ActionStatus
    {
        return $this->status;
    }


    public function setStatus(?ActionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }


    public function getIncident(): Incident
    {
        return $this->incident;
    }


    public function setIncident(Incident $incident): self
    {
        $this->incident = $incident;

        return $this;
    }


    /**
     * @return Collection|ActionStatus []
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }


    public function addStatus(ActionStatus $status): self
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses[] = $status;
            $status->setAction($this);
        }

        return $this;
    }


    public function removeStatus(ActionStatus $status): self
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
            // set the owning side to null (unless already changed)
            if ($status->getAction() === $this) {
                $status->setAction(null);
            }
        }

        return $this;
    }


    public function addActionTask(ActionTask $status): self
    {
        if (!$this->actionTasks->contains($status)) {
            $this->actionTasks[] = $status;
            $status->setAction($this);
        }

        return $this;
    }


    public function removeActionTask(ActionTask $status): self
    {
        if ($this->actionTasks->contains($status)) {
            $this->actionTasks->removeElement($status);
            // set the owning side to null (unless already changed)
            if ($status->getAction() === $this) {
                $status->setAction(null);
            }
        }

        return $this;
    }


    public function getActionTaskById(int $actionTaskId): ?ActionTask
    {
        foreach ($this->getActionTasks() as $actionTask) {
            if ($actionTask->getId() === $actionTaskId) {
                return $actionTask;
            }
        }

        return null;
    }

    public function getPreviousStatus(): ?ActionStatus
    {
        $statuses = $this->getStatuses();
        $status = $statuses->slice(-2, 1);
        return count($status) == 1 ? current($status) : null;
    }


    /**
     * @return Collection|ActionTask []
     */
    public function getActionTasks(): Collection
    {
        return $this->actionTasks;
    }


    public static function getOwnerCode(): string
    {
        return 'action';
    }


    public function getTemplateId(): ?int
    {
        return $this->templateId;
    }


    public function setTemplateId(?int $templateId): self
    {
        $this->templateId = $templateId;

        return $this;
    }
}