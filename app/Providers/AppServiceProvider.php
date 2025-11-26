<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Users\Domain\Repositories\UserRepositoryInterface;
use Modules\Users\Infrastructure\Persistence\EloquentUserRepository;
use Modules\Users\Domain\Entities\User;

/**
 * Service Provider principal da aplicação
 * Responsável por registrar bindings de dependências
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding de repositórios
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new EloquentUserRepository(new User());
        });

        // Binding de serviços
        $this->app->singleton(\Modules\Auth\Infrastructure\Services\JwtService::class);
        $this->app->singleton(\Modules\Docs\Infrastructure\Services\DocumentService::class);

        // Registrar UseCases como singletons para otimização Swoole
        $this->app->singleton(\Modules\Users\Application\UseCases\CreateUserUseCase::class);
        $this->app->singleton(\Modules\Users\Application\UseCases\GetUserByIdUseCase::class);
        $this->app->singleton(\Modules\Auth\Application\UseCases\AuthenticateUserUseCase::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurações para Swoole/Octane
        if (config('octane.server') === 'swoole') {
            $this->configureSwooleOptimizations();
        }
    }

    /**
     * Configurações de otimização para Swoole
     */
    private function configureSwooleOptimizations(): void
    {
        // Limpar caches entre requisições para evitar memory leaks
        if (class_exists(\Laravel\Octane\Events\RequestReceived::class)) {
            \Event::listen(\Laravel\Octane\Events\RequestReceived::class, function ($event) {
                // Limpar caches que não devem persistir entre requisições
                // Exemplo: cache de configurações temporárias
            });
        }
    }
}
