<div class="margin_bottom">
    <button id="camera_files_refresh" class="btn btn-primary">Refresh</button>
    <button id="camera_files_delete_all" class="btn btn-danger">Delete all</button>
</div>
<div class="input-group search_input pull-right margin_bottom">
    <input class="form-control" type="text" placeholder="Search" value="<?php echo $files['search']['value']; ?>"/>
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
        <span class="text"><?php echo $files['pagination']['text']; ?></span>
    </li>
    <li class="next">
        <a href="#">Next</a>
    </li>
</ul>
<div class="table_spinner">
    <i class="display_none fa fa-cog fa-spin fa-5x"></i>
</div>