<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamPermission extends Model
{
  protected $fillable = [
    'team_id',
    'user_id',
    'permissions',
  ];

  protected $casts = [
    'permissions' => 'array',
  ];

  public static array $defaultPermissions = [
    'owner' => [
      'manage_team',
      'manage_members',
      'manage_billing',
      'view_analytics',
      'manage_campaigns',
      'manage_templates',
      'manage_subscribers',
      'manage_automations',
    ],
    'admin' => [
      'manage_members',
      'view_analytics',
      'manage_campaigns',
      'manage_templates',
      'manage_subscribers',
      'manage_automations',
    ],
    'member' => [
      'view_analytics',
      'manage_campaigns',
      'manage_templates',
      'manage_subscribers',
    ],
  ];
}
