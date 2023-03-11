<?php
namespace ntentan;

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application logic. They are stored in modules and they contain methods
 * which are called from the url. Parameters to the methods are also passed
 * through the URL. If a method is not specified, the default method is called.
 * The methods called by the controllers are expected to set data into variables
 * which are later rendered as output to the end user through views.
 *
 * @author  James Ekow Abaka Ainooson
 * @todo    There must be a controller interface to be satisfied by all controllers
 */
class Controller
{
    /**
     * The action method that this controller will execute.
     *
     * @var string
     */
    private $actionMethod;

    /**
     * The parameters that are sent to the action method.
     *
     * @var array
     */
    private $actionParameters;

    /**
     * The name of a model binder to use as default for this controller only.
     * This model binder overrides the global default model binder but can be overidden by a model binder on an action
     * method.
     * @var string
     */
    protected $defaultModelBinderClass;

    /**
     * Set the action method to be executed by this controller.
     *
     * @param string $actionMethod 
     * @return void
     */
    public function setActionMethod($actionMethod) : void
    {
        $this->actionMethod = $actionMethod;
    }

    /**
     * Set the parameters to be passed to the action method.
     *
     * @param array $actionParameters
     */
    public function setActionParameters(array $actionParameters) : void
    {
        $this->actionParameters = $actionParameters;
    }

    /**
     * Get the action method selected to be executed for this controller.
     * @return string
     */
    public function getActionMethod() : string
    {
        return $this->actionMethod;
    }

    /**
     * Get the parameters to be passed to the action method.
     * @return array
     */
    public function getActionParameters() : array
    {
        return $this->actionParameters;
    }

    /**
     * Return the default model binder class for this controller if any.
     * @return string
     */
    public function getDefaultModelBinderClass()
    {
        return $this->defaultModelBinderClass;
    }
}
