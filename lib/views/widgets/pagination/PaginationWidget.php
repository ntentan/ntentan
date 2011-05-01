<?php
namespace ntentan\views\widgets\pagination;

use ntentan\views\widgets\Widget;
use ntentan\Ntentan;

class PaginationWidget extends Widget
{
    private $pageNumber;
    private $numberOfPages;
    private $baseRoute;

    public function init($pageNumber = null, $numberOfPages = null, $baseRoute = null)
    {
        $this->pageNumber = $pageNumber;
        $this->numberOfPages = $numberOfPages;
        $this->baseRoute = $baseRoute;
    }

    public function set_page_number($pageNumber)
    {
        $this->pageNumber = $pageNumber;
        return $this;
    }

    public function set_number_of_pages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;
        return $this;
    }

    public function set_base_route($baseRoute)
    {
        $this->baseRoute = $baseRoute;
        return $this;
    }

    public function preRender()
    {
        $pageNumber = $this->pageNumber;
        $numPages = $this->numberOfPages;
        $baseRoute = $this->baseRoute;

        if($pageNumber > 1)
        {
            $pagingLinks[] = array(
                "link" => Ntentan::getUrl(
                    $baseRoute . ($pageNumber - 1)
                ),
                "label" => "< Prev"
            );
        }

        if($numPages <= 21 || $pageNumber < 11)
        {
            for($i = 1; $i <= ($numPages > 21 ? 21 : $numPages) ; $i++)
            {
                $pagingLinks[] = array(
                    "link" => Ntentan::getUrl(
                        $baseRoute . $i
                    ),
                    "label" => "$i",
                    "selected" => $pageNumber == $i
                );
            }
        }
        else
        {
            if($numPages - $pageNumber < 11)
            {
                $startOffset = $pageNumber - (20 - ($numPages - $pageNumber));
                $endOffset = $pageNumber + ($numPages - $pageNumber);
            }
            else
            {
                $startOffset = $pageNumber - 10;
                $endOffset = $pageNumber + 10;
            }
            for($i = $startOffset ; $i <= $endOffset; $i++)
            {
                $pagingLinks[] = array(
                    "link" => Ntentan::getUrl(
                        $baseRoute . $i
                    ),
                    "label" => "$i",
                    "selected" => $pageNumber == $i
                );
            }
        }

        if($pageNumber < $numPages)
        {
            $pagingLinks[] = array(
                "link" => Ntentan::getUrl(
                    $baseRoute . ($pageNumber + 1)
                ),
                "label" => "Next >"
            );
        }
        $this->set("pages", $pagingLinks);
    }
}
