<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topology extends Model
{
    protected $table = 'topologies';

    protected $fillable = [
        'id',
        'lab_id',
        'nama',
        'deskripsi',
        'nodes',
        'connections',
    ];

    protected $casts = [
        'nodes' => 'array',
        'connections' => 'array',
    ];

    // Topology.php
    public function nodes()
    {
        return $this->hasMany(Node::class);
    }
}
