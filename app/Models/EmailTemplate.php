<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'name',
    'description',
    'subject',
    'content',
    'preview_text',
    'category',
    'thumbnail',
    'is_default',
    'variables',
  ];

  protected $casts = [
    'is_default' => 'boolean',
    'variables' => 'array',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function campaigns()
  {
    return $this->hasMany(Campaign::class, 'template_id');
  }

  public function team()
  {
    return $this->belongsTo(Team::class);
  }
}
