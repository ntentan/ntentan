<?php

namespace ntentan;
use ntentan\honam\Templates;
use ntentan\interfaces\RenderableInterface;
use ntentan\interfaces\ThemableInterface;

/**
 * A wrapper around the honam template engine system that acts as a view class.
 * Allows ntentan apps to leverage the honam template engine to create html and other forms of text output where 
 * necessary. Views can be automatically injected into action methods so that they are automatically initialized with
 * the proper layout and templates that correspond to the controller and action currently being executed.
 *
 * @author ekow
 */
class View implements RenderableInterface, ThemableInterface
{
    /**
     * Description of the default layout
     * 
     * @var string
     */
    private $layout = 'main';
    
    /**
     * Description of the default template.
     * 
     * @var string
     */
    private $template;
    
    /**
     * The lifetime of the cached version of this template.
     * 
     * @var double
     */
    private $cacheTimeout = false;

    /**
     * Variables assigned for template substitution.
     * 
     * @var Array
     */
    private $variables = array();

    /**
     * Set the mimetype of the response.
     * Calling this method will cause a Content-Type header to be sent to the client with the content type that is
     * passed to this method.
     * 
     * @param string $contentType Content type
     */
    public function setContentType($contentType)
    {
        header("Content-Type: $contentType");
    }

    /**
     * Get the lifetime of the cached version of this view.
     * @return type
     */
    public function getCacheTimeout()
    {
        return $this->cacheTimeout;
    }

    /**
     * Set the lifetime of the cached version of this view.
     * Once a cache timeout is set, a rendered version of the view is cached and served until the cache expires.
     * 
     * @param double $cacheTimeout
     */
    public function setCacheTimeout($cacheTimeout)
    {
        $this->cacheTimeout = $cacheTimeout;
    }

    /**
     * Set the layout for rendering the view.
     * Layouts represents a common wrapper markup (usually the html headers and common elements) that is shared by
     * several different pages in a given application. The layout must provide a substitution for a "contents" variable.
     * This contents variable will be substituted with the rendered template. Layout can be set to false in cases where
     * a layout is not needed.
     * 
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Get the current layout assigned to this view.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Get the current template assigned to this view.
     * 
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the template for rendering the content of this view.
     * Templates are used render the actual body of the page. Content generated from rendering the template is later
     * substituted into the layout. In cases where the layout has been disabled (by being set to false), the view
     * only presents the output of the template.
     * 
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Set a value to be substituted later into either the template or the layout.
     * Variables assigned are accessible to both the layout and the template.
     * 
     * @param mixed $params1
     * @param string $params2
     */
    public function set($params1, $params2 = null)
    {
        if (is_string($params1)) {
            $this->variables[$params1] = $params2;
        } elseif (is_array($params1)) {
            $this->variables = $params1 + $this->variables;
        }
    }

    public function get($key)
    {
        return $this->variables[$key] ?? null;
    }

    /**
     * Render the view to a string whenever this view object is used as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $viewData = $this->variables;
        $templates = Context::getInstance()->getTemplates();
        if ($this->template != false) {
            $renderedTemplate = $templates->render($this->template, $viewData);
            $viewData['contents'] = $renderedTemplate;
        }
        if ($this->layout != false) {
            return $templates->render($this->layout, $viewData);
        }
    }
}
