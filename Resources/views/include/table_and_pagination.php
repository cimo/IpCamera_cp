<div class="buttons margin_bottom display_none">
    <button class="button_custom refresh">Refresh</button>
    <button class="delete_all button_custom_danger">Delete all</button>
</div>
<div class="input-group pull-right search_input">
    <div class="button_search">
        <i class="fa fa-search"></i>
    </div>
    <input class="form-control" type="text" placeholder="Search" value="<?php echo isset($htmlFiles['search']) == true ? $htmlFiles['search']['value'] : ""; ?>"/>
</div>
<div class="clearfix"></div>
<ul class="pagination-controle pagination margin_clear">
    <li class="previous">
        <a href="#">Previous</a>
    </li>
    <li>
        <span class="text"><?php echo isset($htmlFiles['search']) == true ? $htmlFiles['pagination']['text'] : ""; ?></span>
    </li>
    <li class="next">
        <a href="#">Next</a>
    </li>
</ul>
<div class="table_spinner">
    <i class="display_none fa fa-cog fa-spin fa-5x"></i>
</div>