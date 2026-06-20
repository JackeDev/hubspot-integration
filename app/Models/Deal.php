<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Contact;
use App\Models\Association;

class Deal extends Model
{
    protected $guarded = ["id", "created_at", "updated_at"];

    protected $casts = [
        "last_client_updated" => "datetime"
    ];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)->using(Association::class);
    }
}
