<?php
/**
 * Source file for the admin component
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
        echo $helpers->stylesheet
                ->add(load_asset('css/admin.css', $stylesheet))
                ->add(load_asset("css/fx.css"))
                ->add(load_asset('css/forms.css', n("lib/views/helpers/forms/css/forms.css")))
                ->add(load_asset("css/grid.css"))
                ->add(load_asset('css/list.css', n('lib/views/helpers/lists/css/default.css')))
                ->context('admin');
        echo $helpers->stylesheet($extra_stylesheet);

        echo $helpers->javascript
                ->add(load_asset('js/jquery.js'))
                ->context('admin');
        echo $helpers->javascript($extra_javascript);
    ?>
</head>
<body>
<div class="row" id="header">
    <div class="column grid_10_6">
        <?php if($app_name != ''): ?>
        <h1><?= $app_name ?></h1>
        <?php endif ?>
        <h2>Administrator Console</h2>
    </div>
    <div class="column grid_10_4">
        <div id='profile'>Logged in as <?= $username ?>. <a href="<?= $logout_route ?>">Log out</a></div>
    </div>
</div>
<div class="row">
    <div class="column grid_20_3">
        <?php echo $widgets->menu($sections_menu)->alias('sections') ?></div>
    <div class="column grid_20_17">
        <div id="admin-contents">
            <?php echo $contents->unescape() ?>
        </div>
    </div>
</div>
</body>
</html>
