<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $workspace_id
 * @property string|null $name
 * @property int $type_id
 * @property array $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property EmailServiceType $type
 * @property EloquentCollection $campaigns
 *
 * @method static EmailServiceFactory factory
 */
class SocialService extends BaseModel
{


    /** @var string */
    protected $table = 'social_services';

    /** @var array */
    protected $fillable = [
        'name',
        'type_id',
        'settings',
    ];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'workspace_id' => 'int',
        'type_id' => 'int'
    ];

    /**
     * The type of this provider.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(SocialServiceType::class, 'type_id');
    }

    /**
     * Campaigns using this provider.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'social_service_id');
    }


    public function setSettingsAttribute(array $data): void
    {
        $this->attributes['settings'] = encrypt(json_encode($data));
    }

    public function getSettingsAttribute(string $value): array
    {
        return json_decode(decrypt($value), true);
    }

    public function getInUseAttribute(): bool
    {
        return (bool)$this->campaigns()->count();
    }
}
