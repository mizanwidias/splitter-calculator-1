<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabGroup extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi (mass assignment)
    protected $fillable = ['name', 'slug', 'parent_id'];

    // Relasi: satu folder punya banyak lab
    public function parent()
    {
        return $this->belongsTo(LabGroup::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(LabGroup::class, 'parent_id');
    }

    public function labs()
    {
        return $this->hasMany(Lab::class);
    }

    // Di app/Models/LabGroup.php
    public function fullSlugPath()
    {
        if ($this->parent) {
            return $this->parent->fullSlugPath() . '/' . $this->slug;
        }
        return $this->slug;
    }

    public function fullNamePath()
    {
        if ($this->parent) {
            return $this->parent->fullNamePath() . ' / ' . $this->name;
        }
        return $this->name;
    }

    public function existsOnDisk(): bool
    {
        $path = storage_path('app/labs/' . $this->fullSlugPath());
        return file_exists($path);
    }
}
