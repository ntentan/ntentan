<?php
use ntentan\Ntentan;
function u($url)
{
    return Ntentan::getUrl($url);
}

function n($path)
{
    return Ntentan::getFilePath($path);
}

function s($string)
{
    return Ntentan::toSentence($string);
}