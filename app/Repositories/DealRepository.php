<?php

namespace App\Repositories;

use App\Models\Deal;

class DealRepository
{
    public function getById(int $id): Deal
    {
        return Deal::find($id);
    }

    public function create(array $data): Deal
    {
        return Deal::create($data);
    }

    public function update(Deal $deal, array $data): Deal
    {
        $deal->update($data);
        return $deal->fresh();
    }
}
