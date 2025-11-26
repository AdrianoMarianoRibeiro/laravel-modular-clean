<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Model User - Entidade de domínio para usuários
 * 
 * Implementa JWTSubject para suporte a autenticação JWT com Swoole/Octane
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Nome da tabela no banco de dados
     */
    protected $table = 'users';

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Atributos que devem ser ocultados na serialização
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Mutator para hash de senha automaticamente
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
