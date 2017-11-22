<div class="buttons margin_bottom display_none">
    <button class="button_custom refresh">Refresh</button>
    <button class="delete_all button_custom_danger">Delete all</button>
</div>
<div class="input-group pull-right search_input">
    <div class="button_search">
        <i class="fa fa-search"></i>
        <i class="fa fa-circle-o-notch fa-spin display_none"></i>
    </div>
    <input class="form-control" type="text" placeholder="Search" value="<?php echo isset($htmlTable['search']) == true ? $htmlTable['search']['value'] : ""; ?>"/>
</div>
<div class="clearfix"></div>
<ul class="pagination-controle pagination margin_clear">
    <li class="previous">
        <a href="#">Previous</a>
    </li>
    <li>
        <span class="text"><?php echo isset($htmlTable['search']) == true ? $htmlTable['pagination']['text'] : ""; ?></span>
    </li>
    <li class="next">
        <a href="#">Next</a>
    </li>
    <li class="loading">
        <i class="fa fa-circle-o-notch fa-spin display_none"></i>
    </li>
</ul>