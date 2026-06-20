<?php

namespace App\Repositories;

use App\Models\Association;

class AssociationRepository
{
    public function create(array $data): Association
    {
        return Association::create($data);
    }
}
