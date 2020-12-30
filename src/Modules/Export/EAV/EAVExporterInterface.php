<?php

namespace App\Modules\Export\EAV;

interface EAVExporterInterface
{
    public function exportEntities(): void;

    public function exportRelations(): void;
}