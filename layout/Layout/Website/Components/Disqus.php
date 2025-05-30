<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 25.11.2018
 * Time: 21:11
 */

namespace Layout\Website\Components;


use Layout\Website\Models\WebsiteComponent;

class Disqus extends WebsiteComponent
{
    protected $name = WebsiteComponent::disqus;
//    protected $cached_minutes = 60*24;
}