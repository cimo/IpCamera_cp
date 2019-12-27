<?php
namespace App\Classes\System;

class TableAndPagination {
    // Vars
    private $helper;
    
    private $session;
    
    // Properties
    
    // Functions public
    public function __construct($helper) {
        $this->helper = $helper;
        
        $this->session = $this->helper->getSession();
    }
    
    public function request($rows, $page, $tag, $reverse) {
        $newRows = $reverse == true ? array_reverse($rows, true) : $rows;
        
        // Search
        $searchWritten = isset($_REQUEST['searchWritten']) == true ? $_REQUEST['searchWritten'] : -1;
        $search = $this->search($tag . "_search", $searchWritten);
        $elements = $this->helper->arrayLike($newRows, $search['value']);
        
        // Pagination
        $paginationCurrent = isset($_REQUEST['paginationCurrent']) == true ? $_REQUEST['paginationCurrent'] : -1;
        $pagination = $this->pagination($tag . "_pagination", $paginationCurrent, count($elements), $page);
        
        if ($tag != "page")
            $listHtml = array_slice($elements, $pagination['offset'], $pagination['show']);
        else
            $listHtml = $this->helper->createPageList($elements, false, $pagination);
        
        return Array(
            'search' => $search,
            'pagination' => $pagination,
            'listHtml' => $listHtml,
            'count' => count($elements)
        );
    }
    
    public function checkPost() {
        if (isset($_REQUEST['searchWritten']) == true && isset($_REQUEST['paginationCurrent']) == true)
            return true;
        
        return false;
    }
    
    private function search($index, $value) {
        if ($value == -1)
            $this->session->set($index, 0);
        else
            $this->session->set($index, $value);
	
        $sessionIndex = $this->session->get($index);
        
        return Array(
            'value' => $sessionIndex == 0 ? "" : $sessionIndex
        );
    }
    
    private function pagination($index, $value, $count, $show) {
        if ($value == -1)
            $this->session->set($index, 0);
        else
            $this->session->set($index, $value);
        
        $sessionIndex = $this->session->get($index);
        
        $total = ceil($count / $show);
        $current = $total == 0 ? $total : $sessionIndex + 1;
        
        if ($sessionIndex > $total)
            $sessionIndex = $total;
        
        $offset = $sessionIndex * $show;
        $text = "$current / $total";
        $limit = "$offset,$show";
        
        return Array(
            'show' => $show,
            'offset' => $offset,
            'text' => $text,
            'limit' => $limit
        );
    }
    
    // Functions private
}