<?php
/*
 * Copyright 2010 James Ekow Abaka Ainooson
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
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
        <h1><?= $app_name ?></h1>
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
