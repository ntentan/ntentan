<?php
namespace ntentan\middleware\mvc;

use ntentan\honam\EngineRegistry;
use ntentan\honam\engines\php\HelperVariable;
use ntentan\honam\engines\php\Janitor;
use ntentan\honam\factories\MustacheEngineFactory;
use ntentan\honam\factories\PhpEngineFactory;
use ntentan\honam\factories\SmartyEngineFactory;
use ntentan\honam\TemplateFileResolver;
use ntentan\honam\TemplateRenderer;
use ntentan\honam\Templates;
use ntentan\panie\Container;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\http\Request;
use ntentan\http\Response;


class ServiceContainerBuilder
{
    private Container $container;
//     private string $home;
    
    public function __construct(string $home)
    {
        $this->container = new Container();
        $this->container->provide("string", "home")->with(fn() => $home);
    }

    public function getContainer(ServerRequestInterface $request, ResponseInterface $response)
    {
//         $this->container->provide("string", "home")->with(fn() => $this->home);
        $this->container->setup([
            Templates::class => [Templates::class, 'singleton' => true],
            Request::class => fn() => $request,
            Response::class => fn() => $response,
            ServerRequestInterface::class => fn() => $request,
            ResponseInterface::class => fn() => $response,
            TemplateFileResolver::class => [
                function(Container $container) {
                    $fileResolver = new TemplateFileResolver();
                    $home = $container->get('$home:string');
                    $fileResolver->appendToPathHierarchy("$home/views/shared");
                    $fileResolver->appendToPathHierarchy("$home/views/layouts");
                    return $fileResolver;
                },
                'singleton' => true
                ],
                TemplateRenderer::class => [
                    function($container) {
                        /** @var EngineRegistry $engineRegistry */
                        $engineRegistry = $container->get(EngineRegistry::class);
                        $templateFileResolver = $container->get(TemplateFileResolver::class);
                        $templateRenderer = new TemplateRenderer($engineRegistry, $templateFileResolver);
                        $engineRegistry->registerEngine(['mustache'], $container->get(MustacheEngineFactory::class));
                        $engineRegistry->registerEngine(['smarty', 'tpl'], $container->get(SmartyEngineFactory::class));
                        $engineRegistry->registerEngine(['tpl.php'],
                            new PhpEngineFactory($templateRenderer,
                                new HelperVariable($templateRenderer, $container->get(TemplateFileResolver::class)),
                                $container->get(Janitor::class)
                            ));
                        return $templateRenderer;
                    },
                    'singleton' => true
                    ],
                ]);
        return $this->container;
    }
}