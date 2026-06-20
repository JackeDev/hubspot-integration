<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $guarded = ["id", "created_at", "updated_at"];

    protected $casts = [
        "last_client_updated" => "datetime"
    ];
}
