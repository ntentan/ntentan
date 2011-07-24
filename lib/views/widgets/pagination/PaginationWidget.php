<?php
namespace ntentan\views\widgets\pagination;

use ntentan\views\widgets\Widget;
use ntentan\Ntentan;

class PaginationWidget extends Widget
{
    private $pageNumber;
    private $numberOfPages;
    private $baseRoute;
    private $numberOfLinks;
    private $halfNumberOfLinks;

    public function init($pageNumber = null, $numberOfPages = null, $baseRoute = null, $numberOfLinks = 21)
    {
        $this->pageNumber = $pageNumber;
        $this->numberOfPages = $numberOfPages;
        $this->baseRoute = $baseRoute;
        $this->numberOfLinks = $numberOfLinks;
        $this->halfNumberOfLinks = ceil($numberOfLinks / 2);
    }

    public function execute()
    {
        $baseRoute = $this->baseRoute;

        if($this->pageNumber > 1)
        {
            $pagingLinks[] = array(
                "link" => is_string($baseRoute) ? Ntentan::getUrl($baseRoute . ($this->pageNumber - 1)) : $baseRoute($this->pageNumber - 1),
                "label" => "< Prev"
            );
        }

        if($this->numberOfPages <= $this->numberOfLinks || $this->pageNumber < $this->halfNumberOfLinks)
        {
            for($i = 1; $i <= ($this->numberOfPages > $this->numberOfLinks ? $this->numberOfLinks : $this->numberOfPages) ; $i++)
            {
                $pagingLinks[] = array(
                    "link" => is_string($baseRoute) ? Ntentan::getUrl($baseRoute . $i) : $baseRoute($i),
                    "label" => "$i",
                    "selected" => $this->pageNumber == $i
                );
            }
        }
        else
        {
            if($this->numberOfPages - $this->pageNumber < $this->halfNumberOfLinks)
            {
                $startOffset = $this->pageNumber - (($this->numberOfLinks - 1) - ($this->numberOfPages - $this->pageNumber));
                $endOffset = $this->pageNumber + ($this->numberOfPages - $this->pageNumber);
            }
            else
            {
                $startOffset = $this->pageNumber - ($this->halfNumberOfLinks - 1);
                $endOffset = $this->pageNumber + ($this->halfNumberOfLinks - 1);
            }
            for($i = $startOffset ; $i <= $endOffset; $i++)
            {
                $pagingLinks[] = array(
                    "link" => is_string($baseRoute) ? Ntentan::getUrl($baseRoute . $i) : $baseRoute($i),
                    "label" => "$i",
                    "selected" => $this->pageNumber == $i
                );
            }
        }

        if($this->pageNumber < $this->numberOfPages)
        {
            $pagingLinks[] = array(
                "link" => is_string($baseRoute) ? Ntentan::getUrl($baseRoute . ($this->pageNumber + 1)) : $baseRoute($this->pageNumber + 1),
                "label" => "Next >"
            );
        }
        $this->set("pages", $pagingLinks);
    }
}
