<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['actor_id', 'action', 'subject_id', 'ip_address'])]
class AuditLog extends Model
{
    public const UPDATED_AT = null;
}