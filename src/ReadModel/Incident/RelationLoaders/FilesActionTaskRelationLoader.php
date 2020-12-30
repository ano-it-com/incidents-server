<?php

namespace App\ReadModel\Incident\RelationLoaders;

use App\Entity\File\File;
use App\Entity\Incident\Action\ActionTask;
use App\Entity\Security\User;
use App\ReadModel\ReadModelBuilder\Metadata\Metadata;

class FilesActionTaskRelationLoader extends FilesRelationLoader
{

    public static function supportsEntity(string $entityClass): bool
    {
        return $entityClass === File::class;
    }

    public function addRelationsToDeferredLoading(string $entityClass, string $dtoClass, $fieldName, array $rows, Metadata $metadata): void
    {
        if (!($ownerCode = $this->getOwnerCode($fieldName, $entityClass))) {
            return;
        }

        $annotation = $metadata->getAnnotationForDTOProperty($dtoClass, $fieldName);

        if (!$annotation) {
            throw new \InvalidArgumentException('Annotation not found for property ' . $fieldName . ' of DTO class ' . $dtoClass);
        }
        $relationDTOClass = $annotation->class;

        /** @var ActionTask $entityClass */
        foreach ($rows as $row) {
            $ownerId = $row['id'];
            $this->toLoadEntities[$relationDTOClass][$ownerCode][] = $ownerId;
        }
    }

    public function getRowsForProperty(array $row, string $fieldName, string $entityClass, string $dtoClass, Metadata $metadata): ?array
    {
        if (!($ownerCode = $this->getOwnerCode($fieldName, $entityClass))) {
            return null;
        }

        $targetDtoClass = $metadata->getTargetDtoClassForProperty($fieldName, $entityClass, $dtoClass);

        return array_values(array_filter($this->loadedRows[$targetDtoClass], function ($relationRow) use ($row, $ownerCode) {
            return $relationRow['owner_id'] === $row['id'] && $relationRow['owner_code'] === $ownerCode;
        }));
    }

    protected function getOwnerCode(string $fieldName, string $entityClass): ?string
    {
        /** @var ActionTask $entityClass */
        if ($fieldName == 'filesReport') {
            return $entityClass::REPORT_FILES_OWNER_CODE;
        } else if ($fieldName == 'filesInput') {
            return $entityClass::INPUT_FILES_OWNER_CODE;
        }
        return null;
    }
}