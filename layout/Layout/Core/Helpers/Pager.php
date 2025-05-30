<?php
/**
 * Created by PhpStorm.
 * User: serkan
 * Date: 08.09.2016
 * Time: 10:14
 */

namespace Layout\Core\Helper;


class Pager
{
    private $_page;
    private $_total_pages;
    private $_url;
    const adjacent = 4;

    /**
     * Pager constructor.
     */
    public function __construct($page=0,$total_pages=0,$url="")
    {
        $this->_page = $page;
        $this->_total_pages = $total_pages;
        $this->_url = $url;
    }

    public function html(){
        if($this->total_pages() <= 1){
            return "";
        }

        $html = "<ul class=\"pagination pagination-split\">";
        $previous_page_no = 0;
        if($this->_page > 1){
            $html .= " <li><a href=\"".$this->_url."/".($this->_page - 1)."\"><span class=\"glyphicon glyphicon-menu-left\" aria-hidden=\"true\"></span>&nbsp;</a>";
        }
        foreach($this->page_array() as $page_no){
            if(($page_no - $previous_page_no) > 1){
                $html .= " <li class='disabled'><a>...</a></li>";
            }
            $previous_page_no = $page_no;
            if($page_no == $this->_page){
                $html .= " <li class='active'><a>".$page_no."</a></li>";
            }else {
                $html .= " <li><a href=\"".$this->_url."/".$page_no."\">".$page_no."</a></li>";
            }
        }

        if($this->_page < $this->_total_pages){
            $html .= " <li><a href=\"".$this->_url."/".($this->_page + 1)."\"><span class=\"glyphicon glyphicon-menu-right\" aria-hidden=\"true\"></span></i>&nbsp;</a>";
        }
        $html .="</ul>";

        return $html;
    }

    public function total_pages(){
        return $this->_total_pages;
    }

    private function page_array()
    {
        $pages = array();

        if($this->_page - self::adjacent > 1){
            $pages[] = 1;
        }

        for($i = (($this->_page - self::adjacent) > 1 ? ( $this->_page - self::adjacent) : 1 ); $i<= $this->_page + self::adjacent && $i < $this->_total_pages;$i++){
            $pages[] = $i;
        }
        $pages[] = $this->_total_pages;

        return $pages;
    }

}