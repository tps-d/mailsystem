<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EmailServiceFactory;
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
class EmailService extends BaseModel
{
    use HasFactory;

    // NOTE(david): we require this because of namespace issues when resolving factories from models
    // not in the default `App\Models` namespace.
    protected static function newFactory()
    {
        return EmailServiceFactory::new();
    }

    /** @var string */
    protected $table = 'sendportal_email_services';

    /** @var array */
    protected $fillable = [
        'name',
        'from_name',
        'from_email',
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
        return $this->belongsTo(EmailServiceType::class, 'type_id');
    }

    /**
     * Campaigns using this provider.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'email_service_id');
    }

    /**
     * Automations using this email service.
     */
    public function automations(): HasMany
    {
        return $this->hasMany(Automation::class, 'email_service_id');
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
