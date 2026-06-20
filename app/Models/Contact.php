<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Deal;
use App\Models\Association;

class Contact extends Model
{
    protected $guarded = ["id", "created_at", "updated_at"];

    protected $casts = [
        "last_client_updated" => "datetime"
    ];

    public function deals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class)->using(Association::class);
    }
}
