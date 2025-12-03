<div class="side-nav">
    <div class="">
        <div class="nav-icon nav-icon-bookshelf bg-lt-yellow">
            <a id="bookshelf_nav_item_side" class="nav-icon-anchor" href="/bookshelf/">
                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_bookshelf.svg'); ?>
            </a>
            <span>Bookshelf</span>
            <div id="bookshelf_nav_side" class="popup popup-small">
                <p><a href="/bookshelf/nightstand/">Currently Reading</a></p>
                <p><a href="/bookshelf/finished/">Finished</a></p>
                <p class="margin-bottom-0"><a href="/bookshelf/wishlist/">Wishlist</a></p>
            </div>
        </div>
        <div class="nav-icon nav-icon-notes bg-lt-blue">
            <a class="nav-icon-anchor" href="/notes/">
                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_notes.svg'); ?>
            </a>
            <span>Notes</span>
        </div>
        <div class="nav-icon nav-icon-recommend bg-lt-purple">
            <a class="nav-icon-anchor" href="">
                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_recommend.svg'); ?>
            </a>
            <span>Community</span>
        </div>
        <div class="nav-icon nav-icon-add-book add-book">
            <a class="nav-icon-anchor" href="/add-book/">
                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/icon_plus.svg'); ?>
            </a>
            <span>Add Book</span>
        </div>
    </div>
</div>