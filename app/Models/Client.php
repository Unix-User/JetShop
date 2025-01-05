<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Team;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'email', 'phone', 'team_id'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}