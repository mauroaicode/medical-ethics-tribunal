<?php

declare(strict_types=1);

namespace Src\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Session\Models\Session;
use Src\Domain\Shared\Traits\InteractsWithCustomMedia;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\QueryBuilders\UserQueryBuilder;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $last_name
 * @property-read DocumentType $document_type
 * @property-read string $document_number
 * @property-read string $phone
 * @property-read string $address
 * @property-read string $email
 * @property-read string $password
 * @property-read string|null $google_2fa_secret
 * @property-read bool $google_2fa_enabled
 * @property-read string|null $google2fa_temp_secret
 * @property-read string|null $last_login_ip
 * @property-read Carbon|null $last_login_at
 * @property-read UserStatus $status
 * @property-read bool $requires_password_change
 * @property-read Carbon|null $email_verified_at
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 *
 * @method static UserQueryBuilder query()
 * @method UserQueryBuilder withAdminRoles()
 * @method UserQueryBuilder withRoles()
 * @method UserQueryBuilder withoutTrashed()
 */
class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use InteractsWithCustomMedia;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'document_type',
        'document_number',
        'phone',
        'address',
        'email',
        'password',
        'google_2fa_secret',
        'google_2fa_enabled',
        'google2fa_temp_secret',
        'last_login_ip',
        'last_login_at',
        'status',
        'requires_password_change',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_2fa_secret',
        'google2fa_temp_secret',
    ];

    /**
     * @return HasOne<Complainant, $this>
     */
    public function complainant(): HasOne
    {
        return $this->hasOne(Complainant::class);
    }

    /**
     * @return HasOne<Doctor, $this>
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * @return HasOne<Magistrate, $this>
     */
    public function magistrate(): HasOne
    {
        return $this->hasOne(Magistrate::class);
    }

    /**
     * Get all audit logs for this user.
     *
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Get all sessions for this user.
     *
     * @return HasMany<Session, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->update([
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @param  Builder  $query
     */
    public function newEloquentBuilder(mixed $query): UserQueryBuilder
    {
        return new UserQueryBuilder($query);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'status' => UserStatus::class,
            'requires_password_change' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'google_2fa_enabled' => 'boolean',
        ];
    }
}
