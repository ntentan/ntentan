<?php

namespace ntentan;

class Application {
    
    /**
     *
     * @var Context 
     */
    protected $context;
    private $pipeline = [middleware\MVCMiddleware::class];
    
    /**
     * 
     * @param type $context
     */
    public function __construct(Context $context) {
        $this->context = $context;
    }
    
    public function getPipeline() {
        return $this->pipeline;
    }
    
    public function setup() {
        
    }
    
    public function appendMiddleware($class) {
        $this->pipeline[] = $class;
    }
    
    public function prependMiddleware($class) {
        array_unshift($this->pipeline, $class);
    }
    
}
