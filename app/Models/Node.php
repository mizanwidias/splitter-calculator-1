<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    protected $table = 'nodes';

    public function topology()
    {
        return $this->belongsTo(Topology::class);
    }
}
