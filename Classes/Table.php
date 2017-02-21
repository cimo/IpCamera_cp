<?php
class Table {
    // Vars
    private $searchIndex;
    private $paginationIndex;
    
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct($utility) {
        $this->searchIndex = "";
        $this->paginationIndex = "";
        
        $this->utility = $utility;
    }
    
    public function request($rows, $page, $sessionTag, $reverse, $flat = false) {
        $newRows = $rows;
        
        if ($reverse == true)
            $newRows = array_reverse($rows, true);
        
        // Table - search
        $searchWritten = isset($_POST['searchWritten']) == true ? $_POST['searchWritten'] : -1;
        $search = $this->search($sessionTag . "Search", $searchWritten);
        $elements = $this->utility->arrayLike($newRows, $search['value'], $flat);

        // Table - pagination
        $paginationCurrent = isset($_POST['paginationCurrent']) == true ? $_POST['paginationCurrent'] : -1;
        $pagination = $this->pagination($sessionTag . "Pagination", $paginationCurrent, count($elements), $page);
        $list = array_slice($elements, $pagination['offset'], $pagination['show']);
        
        return Array(
            'search' => $search,
            'pagination' => $pagination,
            'list' => $list
        );
    }
    
    private function search($index, $value) {
        $this->searchIndex = $index;
        
        if (isset($_SESSION[$index]) == false)
            $_SESSION[$index] = "";
        else if ($value != -1)
            $_SESSION[$index] = $value;
        
        return Array(
            'value' => $_SESSION[$index]
        );
    }
    
    private function pagination($index, $value, $count, $show) {
        $this->paginationIndex = $index;
        
        if (isset($_SESSION[$index]) == false)
            $_SESSION[$index] = "";
        if ($value > -1)
            $_SESSION[$index] = $value;
        
        $total = ceil($count / $show);
        $current = $total == 0 ? 0 : $_SESSION[$index] + 1;
        
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