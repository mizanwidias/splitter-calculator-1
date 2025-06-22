<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topology extends Model
{
    protected $table = 'topologies';

    protected $fillable = [
        'id',
        'lab_id',
        'name',
        'description',
        'nodes',
        'connections',
        'power'
    ];

    protected $casts = [
        'nodes' => 'array',
        'connections' => 'array',
    ];

    // Topology.php
    public function nodeItems()
    {
        return $this->hasMany(Node::class);
    }
}
