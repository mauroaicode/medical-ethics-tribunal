<?php

declare(strict_types=1);

namespace Src\Domain\User\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Shared\Enums\FileType;
use Src\Domain\Shared\Traits\InteractsWithCustomMedia;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserStatus;

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
 * @property-read Carbon|null $email_verified_at
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, InteractsWithCustomMedia;

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'status' => UserStatus::class,
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'google_2fa_enabled' => 'boolean',
        ];
    }

    /**
     * @return HasOne<Complainant>
     */
    public function complainant(): HasOne
    {
        return $this->hasOne(Complainant::class);
    }

    /**
     * @return HasOne<Doctor>
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * @return HasOne<Magistrate>
     */
    public function magistrate(): HasOne
    {
        return $this->hasOne(Magistrate::class);
    }
}

