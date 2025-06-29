<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TenantScope;

class CultureContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'title', 'content', 'language', 'category', 'image_url'
    ];

    protected $casts = [
        'branding_settings' => 'json',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
