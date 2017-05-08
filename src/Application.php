<?php

namespace ntentan;

class Application {
    
    /**
     *
     * @var Context 
     */
    protected $context;
    private $pipeline = [];
    private $paremeters;
    
    /**
     * 
     * @param type $context
     */
    public function __construct(Context $context) {
        $this->context = $context;
        $this->paremeters = Parameters::wrap([]);
        $this->prependMiddleware(middleware\MVCMiddleware::class);
    }
    
    public function getPipeline() {
        return $this->pipeline;
    }
    
    public function setup() {
        
    }
    
    public function appendMiddleware($class, $options = []) {
        $this->pipeline[] = [$class, $options];
    }
    
    public function prependMiddleware($class, $options = []) {
        array_unshift($this->pipeline, [$class, $options]);
    }
    
    public function getParameters() {
        return $this->paremeters;
    }
    
}
