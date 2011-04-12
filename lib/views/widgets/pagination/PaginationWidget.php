<?php
namespace ntentan\views\widgets\pagination;

use ntentan\widgets\Widget;
use ntentan\Ntentan;

class PaginationWidget extends Widget
{
    public function __construct($args)
    {
        $pageNumber = $args['page'];
        $numPages = $args['number_of_pages'];
        $baseRoute = $args['base_route'];

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
