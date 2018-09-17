<?php

namespace ntentan\interfaces;


interface ThemableInterface
{
    public function getTemplate();
    public function setTemplate($template);
    public function getLayout();
    public function setLayout($layout);
}