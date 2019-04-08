<?php
namespace App\Classes\System;

class TableAndPagination {
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct($utility) {
        $this->utility = $utility;
    }
    
    public function request($rows, $page, $sessionTag, $reverse) {
        $newRows = $reverse == true ? array_reverse($rows, true) : $rows;
        
        // Search
        $searchWritten = isset($_REQUEST['searchWritten']) == true ? $_REQUEST['searchWritten'] : -1;
        $search = $this->search($sessionTag . "_search", $searchWritten);
        $elements = $this->utility->arrayLike($newRows, $search['value']);
        
        // Pagination
        $paginationCurrent = isset($_REQUEST['paginationCurrent']) == true ? $_REQUEST['paginationCurrent'] : -1;
        $pagination = $this->pagination($sessionTag . "_pagination", $paginationCurrent, count($elements), $page);
        
        if ($sessionTag != "page")
            $listHtml = array_slice($elements, $pagination['offset'], $pagination['show']);
        else
            $listHtml = $this->utility->createPageList($elements, false, $pagination);
        
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
            $_SESSION[$index] = 0;
        else
            $_SESSION[$index] = $value;
	
        return Array(
            'value' => $_SESSION[$index] === 0 ? "" : $_SESSION[$index]
        );
    }
    
    private function pagination($index, $value, $count, $show) {
        if ($value == -1)
            $_SESSION[$index] = 0;
        else
            $_SESSION[$index] = $value;
        
        $total = ceil($count / $show);
        $current = $total == 0 ? $total : $_SESSION[$index] + 1;
        
        if ($_SESSION[$index] > $total)
            $_SESSION[$index] = $total;
        
        $offset = $_SESSION[$index] * $show;
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