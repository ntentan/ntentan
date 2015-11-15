<?php
/**
 * template for the default login layout
 * 
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @category Components
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */
?><!DOCTYPE HTML>
<html lang='en'>
<head>
    <title><?php echo $title ?></title>
    <?php
        echo $this->helpers->stylesheet
            ->add(load_asset('css/auth.css', n("lib/controllers/components/auth/assets/css/auth.css")))
            ->add(load_asset("css/fx.css"))
            ->add(load_asset('css/forms.css', n("lib/views/helpers/forms/css/forms.css")))
            ->context('auth');

        echo $this->helpers->javascript
            ->add(load_asset('js/jquery.js'))
            ->context('auth');
    ?>
</head>
<body>
    <div id="header">
        <h1><?php echo $app_name ?></h1>
    </div>
    <div id="body">
        <?php echo $contents->unescape() ?>
    </div>
</body>
</html>

