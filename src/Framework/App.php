<?php

declare(strict_types=1);

namespace Framework;

use Framework\Middleware\RoutePrefixMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class App implements RequestHandlerInterface
{
    /**
     * @var string[]
     */
    private array $modules = [];

    private ?ContainerInterface $container = null;

    /**
     * @var mixed[]
     */
    private array $middlewares;

    /**
     * index of middleware[]
     */
    private int $index = 0;

    public function __construct(readonly private string $definitions)
    {
    }

    /**
     * add a module
     *
     */
    public function addModule(string $module): self
    {
        $this->modules[] = $module;
        return $this;
    }

    /**
     * add a middleware
     * @param string[]|null $routePrefix
     *
     */
    public function pipe(string $middleware, ?array $routePrefix = []): self
    {
        if ($routePrefix !== null && $routePrefix !== []) {
            $this->middlewares[] = new RoutePrefixMiddleware($this->getContainer(), $routePrefix, $middleware);
        } else {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }

    /**
     * execute all middlewares
     *
     *
     * @throws \Exception if there is no more middleware to process
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->getMiddleware();
        if (!$middleware instanceof \Psr\Http\Server\MiddlewareInterface) {
            throw new \Exception('Aucun middleware n\'a intercepté cette requête');
        }
        return $middleware->process($request, $this);
    }

    /**
     * retrieve modules and launches middleware
     *
     *
     * @throws \Exception if there is no container
     */
    public function run(ServerRequestInterface $request): ResponseInterface|\Exception
    {
        $this->getContainer()->get(Router::class)->addMatchTypes('slug', '[a-z0-9-]+');
        if ($this->container instanceof \Psr\Container\ContainerInterface) {
            foreach ($this->modules as $module) {
                $this->container->get($module);
            }
            return $this->handle($request);
        }
        throw new \Exception('Container is not initialized');
    }

    /**
     * retrieve the container
     */
    public function getContainer(): ContainerInterface
    {
        if (!$this->container instanceof \Psr\Container\ContainerInterface) {
            $builder = new \DI\ContainerBuilder();
            $env = $_ENV['ENV'] ?: 'production';
            if ($env === 'production') {
                $builder->enableCompilation(dirname(__DIR__, 2) . '/tmp/di');
                $builder->enableDefinitionCache();
            }
            $builder->addDefinitions($this->definitions);
            foreach ($this->modules as $module) {
                if ($module::DEFINITIONS) {
                    $builder->addDefinitions($module::DEFINITIONS);
                }
            }
            $this->container = $builder->build();
        }
        return $this->container;
    }

    /**
     * get an instance of a middleware
     */
    private function getMiddleware(): ?\Psr\Http\Server\MiddlewareInterface
    {
        if (array_key_exists($this->index, $this->middlewares) && $this->container) {
            if (is_string($this->middlewares[$this->index])) {
                $middleware = $this->container->get($this->middlewares[$this->index]);
            } else {
                $middleware = $this->middlewares[$this->index];
            }
            $this->index++;
            return $middleware;
        }
        return null;
    }

    /**
     * get the modules
     * @return string[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }
}
