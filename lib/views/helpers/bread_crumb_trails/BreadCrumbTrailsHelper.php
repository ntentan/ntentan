<?php
namespace ntentan\views\helpers\bread_crumb_trails;

use ntentan\Ntentan;
use ntentan\views\helpers\Helper;
use ntentan\views\template_engines\TemplateEngine;

class BreadCrumbTrailsHelper extends Helper
{
    private $trailData = false;
    private $separator = '>';
        
    public function trail_data($data = false)
    {
        if($data === false)
        {
            if($this->trailData === false)
            {
                $url = Ntentan::getUrl('/');
                $trailData = array(array('url' => $url, 'label' => 'Home'));
                foreach(explode('/', Ntentan::$route) as $route)
                {
                    $url .= "$route/";
                    $trailData[] = array('url' => $url, 'label' => Ntentan::toSentence($route));
                }
                return $trailData;
            }
            else
            {
                return $this->trailData;
            }
        }
        else
        {
            $this->trailData = $data;
            return $this;
        }
    }
    
    public function separator($separator)
    {
        $this->separator = $separator;
        return $this;
    }
    
    public function help()
    {
        $markup = '';
        foreach($this->trail_data() as $i => $trailData)
        {
            if($i > 0) $markup .= "{$this->separator} ";
            $markup .= "<a href='{$trailData['url']}'>{$trailData['label']}</a> ";
        }
        return "<div class='bread-crumb'>$markup</div>";
    }
}
