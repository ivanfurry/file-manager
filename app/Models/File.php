<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'folder_id', 'name', 'file_path', 'mime_type', 'size'];
    public function user() { return $this->belongsTo(User::class); }
    public function folder() { return $this->belongsTo(Folder::class); }
    public function tags() { return $this->belongsToMany(Tag::class); }
}
