<div class="footer-nav">
    <div class="flex nowrap align-items-center justify-space-between">
        <div class="nav-icon add-book">
            <a class="nav-icon-anchor" href="/add-book/" alt="Add Book" title="Add Book">
                <!-- <span class="icon-text">+</span> -->
                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/icon_plus.svg'); ?>
            </a>
        </div>
        <div class="nav-icon">
            <a id="bookshelf_nav_item" class="nav-icon-anchor" href="/bookshelf/" alt="Bookshelf" title="Bookshelf">
                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_bookshelf.svg'); ?>
            </a>
            <div id="bookshelf_nav" class="popup popup-small">
                <a id="navPopupCloser" class="close-button" href="#"><img src="<?php echo get_stylesheet_directory_uri() . '/img/icon_close.svg'; ?>" alt="Close button" /></a>
                <p><a href="/bookshelf/nightstand/">Currently Reading</a></p>
                <p><a href="/bookshelf/finished/">Finished</a></p>
                <p class="margin-bottom-0"><a href="/bookshelf/wishlist/">Wishlist</a></p>
            </div>
        </div>
        <div class="nav-icon">
            <a class="nav-icon-anchor" href="/notes/" alt="Notes" title="Notes">
                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/iconb_notes.svg'); ?>
            </a>
        </div>
        <div class="nav-icon">
            <a class="nav-icon-anchor" href="/community/" alt="Community" title="Comunity">
                <?php echo file_get_contents( get_stylesheet_directory_uri() . '/img/community.svg'); ?>
            </a>
        </div>
    </div>
</div>