<?php
namespace ntentan\middleware\mvc;


//use ntentan\config\Config;
//use ntentan\Context;
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

class ServiceContainerBuilder
{
//    private $templates;
    private $container;

    public function __construct(string $home)
    {
        $this->container = new Container();
        $this->container->provide("string", "home")->with(fn() => $home);
        $this->container->setup([
            Templates::class => [Templates::class, 'singleton' => true],
            TemplateFileResolver::class => [
                function() use ($home) {
                    $fileResolver = new TemplateFileResolver();
                    $fileResolver->appendToPathHierarchy("$home/views/shared");
                    $fileResolver->appendToPathHierarchy("$home/views/layouts");
                    return $fileResolver;
                },
                'singleton' => true
            ],
//            Context::class => [function() use ($context) {return $context;}],
//            Config::class => [function() use ($context) {return $context->getConfig();}],
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
    }

    public function getContainer()
    {
        return $this->container;
    }
}