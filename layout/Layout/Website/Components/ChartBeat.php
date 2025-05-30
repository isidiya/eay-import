<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 25.11.2018
 * Time: 21:11
 */

namespace Layout\Website\Components;


use Layout\Website\Models\WebsiteComponent;

class ChartBeat extends WebsiteComponent
{
    protected $name = WebsiteComponent::chartbeat;
    protected $cached_minutes = 60*24;
}