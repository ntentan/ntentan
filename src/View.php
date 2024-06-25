<?php

namespace ntentan;
use ntentan\honam\Templates;
use ntentan\interfaces\RenderableInterface;
use ntentan\interfaces\ThemableInterface;
use ntentan\http\StringStream;
use Psr\Http\Message\StreamInterface;

/**
 * A wrapper around the honam template engine system that acts as a view class.
 * Allows ntentan apps to leverage the honam template engine to create html and other forms of text output where 
 * necessary. Views can be automatically injected into action methods so that they are automatically initialized with
 * the proper layout and templates that correspond to the controller and action currently being executed.
 *
 * @author ekow
 */
class View //implements RenderableInterface //, ThemableInterface
{
    /**
     * Description of the default layout
     * 
     * @var string
     */
    private string $layout = 'primary';
    
    /**
     * Description of the default template.
     * 
     * @var string
     */
    private string $template;

    /**
     * Variables assigned for template substitution.
     */
    private array $variables = array();

    private Templates $templates;
    
    private StringStream $outputStream;

    public function __construct(Templates $templates, StringStream $outputStream)
    {
        $this->templates = $templates;
        $this->outputStream = $outputStream;
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
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
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
    
    public function asStream(): StreamInterface
    {
        if ($this->outputStream->getSize() > 0) {
            return $this->outputStream;
        }
        
        $viewData = $this->variables;
        
        if ($this->template != false) {
            $renderedTemplate = $this->templates->render($this->template, $viewData);
        }
        
        if ($this->layout != false) {
            $viewData['contents'] = $renderedTemplate;
            $this->outputStream->setContent($this->templates->render($this->layout, $viewData));
        } else {
            $this->outputStream->setContent($renderedTemplate);
        }
        
        return $this->outputStream;
    }
}
