<?php
/**
 * Utility class which provides services such as
 */
class ImageCache
{

    public static function resizeImage($src,$dest,$width,$height,$tag='')
    {
        $im = imagecreatefromjpeg($src);
        $o_width = imagesx($im);
        $o_height = imagesy($im);

        $aspect = $o_width / $o_height;

        if($width<=0)
        {
            $width = $aspect * $height;
        }
        else if($height<=0)
        {
            $height = $width / $aspect;
        }

        $dest_im = imagecreatetruecolor($width, $height);
        imagecopyresampled($dest_im, $im, 0,0,0,0,$width,$height,$o_width,$o_height);

        if($tag!='')
        {
            imagefttext($dest_im,12,0,11,21,0xffffff,"VeraSe.ttf",$tag);
            imagefttext($dest_im,12,0,10,20,0x000000,"VeraSe.ttf",$tag);
        }

        imagejpeg($dest_im, $dest, 100);
        imagedestroy($im);
        imagedestroy($dest_im);
    }

    public static function cropImage($src, $dest, $width, $height,$head=false)
    {
        $im = imagecreatefromjpeg($src);
        $o_width = imagesx($im);
        $o_height = imagesy($im);
        if($head==false) $top = ($o_height/2)-($height/2); else $top=0;
        $left = ($o_width/2)-($width/2);
        $im2 = imagecreatetruecolor ($width, $height);

        imagecopyresampled($im2,$im,0,0,$left,$top,$width,$height,$width,$height);
        imagejpeg($im2, $dest, 100);
        imagedestroy($im);
        imagedestroy($im2);
    }

    public static function width($file,$width)
    {
        if(!is_file($file)) return;
        $src = "cache/$file.cachew.$width.jpeg";

        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            ImageCache::resize_image($file,$src,$width, 0,$tag);
        }
        return $src;
    }

    public static function height($file,$height)
    {
        if(!is_file($file)) return;
        $src = "cache/$file.cacheh.$height.jpeg";
        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            ImageCache::resize_image($file,$src,0, $height);
        }
        return $src;
    }

    public static function thumbnail($file,$width,$height,$head=false)
    {
        if(!is_file($file)) return;
        $src = "cache/images/" . md5("$file.thumb.$width.$height") . ".jpeg";

        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            $im = imagecreatefromjpeg($file);
            $i_width = imagesx($im);
            $i_height = imagesy($im);
            imagedestroy($im);

            if($width>$height)
            {
                ImageCache::resizeImage($file,"cache/images/temp.jpeg",$width,0);
                ImageCache::cropImage("cache/images/temp.jpeg",$src,$width,$height,$head);
            }
            else if($height>$width)
            {
                ImageCache::resizeImage($file,"cache/images/temp.jpeg",$height,0);
                ImageCache::cropImage("cache/images/temp.jpeg",$src,$width,$height,$head);
            }
            else
            {
                if($i_width>$i_height) ImageCache::resizeImage($file,"cache/images/temp.jpeg",0,$height);
                else ImageCache::resizeImage($file,"cache/images/temp.jpeg",$width,0);
                ImageCache::cropImage("cache/images/temp.jpeg",$src,$width,$height,$head);
            }
            unlink("cache/images/temp.jpeg");
        }
        return $src;
    }
}
