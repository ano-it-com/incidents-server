<?php

namespace App\Entity\Incident\ActionsTemplate;

use App\Entity\Incident\IncidentType;
use App\Repository\Incident\ActionsTemplate\ActionsTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActionsTemplateRepository::class)
 * @ORM\Table(name="actions_templates")
 */
class ActionsTemplate
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json", nullable=false, options={"jsonb": true})
     */
    private $actionsMapping = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=IncidentType::class, inversedBy="actionsTemplates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $incidentType;


    public function __construct()
    {
        $this->deleted        = false;
        $this->actionsMapping = [];
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


    public function getDeleted(): bool
    {
        return $this->deleted;
    }


    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }


    public function getActionsMapping(): array
    {
        return $this->actionsMapping;
    }


    public function setActionsMapping(array $actionsMapping): self
    {
        $this->actionsMapping = $actionsMapping;

        return $this;
    }


    public function getSort(): int
    {
        return $this->sort;
    }


    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }


    public function getIncidentType(): IncidentType
    {
        return $this->incidentType;
    }


    public function setIncidentType($incidentType): self
    {
        $this->incidentType = $incidentType;

        return $this;
    }

}