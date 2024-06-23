<?php

namespace ntentan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of Runner
 *
 * @author ekow
 */
class PipelineRunner 
{

    private middleware\Registry $registry;
//    private array $middlewareClasses;
    private array $pipeline;

    public function __construct(middleware\Registry $registry) 
    {
        $this->registry = $registry;
    }

    public function run(array $pipeline, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface 
    {
        $this->pipeline = $pipeline;
        return $this->registry
                        ->get($this->pipeline[0][0], $this->pipeline[0][1] ?? [])
                        ->run($request, $response, $this);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface 
    {
        $nextMiddleWare = next($this->pipeline);
        if ($nextMiddleWare !== false) {
            $middleware = $this->registry->get($nextMiddleWare[0], $nextMiddleWare[1]);
            return $middleware->run($request, $response, $this);
        }
    }
}
