<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*
 * The next block of code can be added to every view or .php 
 * file to prevent direct access.
 */
if(!defined('ROOT_DIR')){
    Logger::log('Direct access. Forbidden','error');
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
//load UI template components (JS, CSS and others)
//it is optional. to use a theme but recomended
Page::theme($themeName='WebFiori Theme');

//sets the title of the page
$lang = Page::lang();
if($lang == 'AR'){
    Page::title('مثال على صفحة');
    //adds a paragraph to the body of the page.
    $p = new PNode();
    $p->addText('أهلا و سهلا من إطار "ويب فيوري"!');
    Page::insert($p);
}
else{
    Page::title('Example Page');
    //adds a paragraph to the body of the page.
    $p = new PNode();
    $p->addText('Hello from "WebFiori Framework"!');
    Page::insert($p);
}
$image = new HTMLNode('img',FALSE);
$image->setAttribute('src', Page::imagesDir().'/image.png');
Page::insert($image);
//display the view
Page::render();
