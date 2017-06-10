<div class="buttons margin_bottom display_none">
    <button class="btn btn-primary refresh">Refresh</button>
    <button class="btn btn-danger delete_all">Delete all</button>
</div>
<div class="input-group pull-right search_input margin_bottom">
    <input class="form-control" type="text" placeholder="Search" value="<?php echo isset($files['search']) == true ? $files['search']['value'] : ""; ?>"/>
    <span class="input-group-btn">
        <button class="btn btn-primary" type="button">
            <i class="fa fa-search"></i>
        </button>
    </span>
</div>
<div class="clearfix"></div>
<ul class="pagination-controle pagination margin_clear">
    <li class="previous">
        <a href="#">Previous</a>
    </li>
    <li>
        <span class="text"><?php echo isset($files['search']) == true ? $files['pagination']['text'] : ""; ?></span>
    </li>
    <li class="next">
        <a href="#">Next</a>
    </li>
</ul>
<div class="table_spinner">
    <i class="display_none fa fa-cog fa-spin fa-5x"></i>
</div>