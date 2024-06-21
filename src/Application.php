<?php

namespace ntentan;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Application bootstrapping class.
 */
class Application
{
    private RequestInterface $request;
    private ResponseInterface $response;
    
    /**
     * Create an instance of the application.
     */
    public final function __construct(RequestInterface $request, ResponseInterface $response) //SessionContainerFactory $sessionContainerFactory)
    {
        $this->request = $request;
        $this->response = $response;
    }

//    protected function setup() : void
//    {
//        $routes = require "bootstrap/routes.php";
//        $this->router->setRoutes($routes['routes']);
//        $this->defaultPipeline = $routes['default_pipeline'];
//    }
//
//    public function setDatabaseDriverFactory(DriverFactory $driverFactory) : void
//    {
//        DbContext::initialize($driverFactory);
//    }
//
//    public function setOrmFactories(ModelFactoryInterface $modelFactory, DriverAdapterFactoryInterface $driverAdapterFactory, ValidatorFactoryInterface $modelValidatorFactory) : void
//    {
//        ORMContext::initialize($modelFactory, $driverAdapterFactory, $modelValidatorFactory, $this->cache);
//    }
//    
//    public function setMiddlewareFactoryRegistry(MiddlewareFactoryRegistry $middlewareFactoryRegistry)
//    {
//        $this->middlewareFactoryRegistry = $middlewareFactoryRegistry;
//    }
//    
//    private function buildPipeline($pipeline)
//    {
//        $instances = [];
//        foreach($pipeline as $middleware) {
//            $instance = $this->middlewareFactoryRegistry->getFactory($middleware[0])->createMiddleware($middleware[1] ?? []);
//            $instances[] = $instance;
//        }
//        return $instances;
//    }

    public function execute(array $pipeline)
    {
//        $this->setup();
        $this->sessionContainerFactory->createSessionContainer();
        $route = $this->router->route(substr(parse_url(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL), PHP_URL_PATH), 1), $this->prefix);
        $this->context->setParameter('route', $route['route']);
        $this->context->setParameter('route_parameters', $route['parameters']);
        $pipeline = $this->buildPipeline($route['description']['parameters']['pipeline'] ?? $this->defaultPipeline);
        echo $this->runner->run($pipeline, $route);
    }    
}
