<?php

namespace App\Domain\Incident\TypeHandler;

use App\Domain\Incident\IncidentTypeHandlerInterface;
use App\Domain\CommonPropertyHandlerTrait;
use App\Entity\Location\Location;
use App\Repository\Location\LocationRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class IncidentTypeHandler implements IncidentTypeHandlerInterface
{
    use CommonPropertyHandlerTrait;

    /**
     * Регион
     * @var string
     * @Assert\Type("int")
     * @Assert\NotBlank()
     */
    public $locationId;

    /**
     * Приоритет
     * @var int
     * @Assert\Type("int")
     * @Assert\NotBlank()
     */
    public $priority;

    /**
     * Охват
     * @var int
     * @Assert\Type("int")
     * @Assert\NotBlank()
     */
    public $coverage;

    private LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public static function getCode(): string
    {
        return 'incident';
    }

    public function getPreparedLocationId(): array
    {
        $locations = $this->locationRepository->findAll();
        return array_map(function (Location $location){
            return [
                'id' => $location->getId(),
                'title' => $location->getTitle(),
                'level' => $location->getLevel()
            ];
        }, $locations);
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (!in_array($this->locationId, array_column($this->getPreparedLocationId(), 'id'))) {
            $context->buildViolation("Location with id {$this->locationId} not found")
                ->atPath('locationId')
                ->addViolation();
        }
    }
}
