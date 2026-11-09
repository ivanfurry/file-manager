<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'parent_id', 'name'];
    public function user() { return $this->belongsTo(User::class); }
    public function parent() { return $this->belongsTo(Folder::class, 'parent_id'); }
    public function files() { return $this->hasMany(File::class); }
}
