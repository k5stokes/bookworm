document.addEventListener('DOMContentLoaded', function () {
	// Filters Toggle Button
	let filtersToggleButton = document.querySelector('.filters-toggle-button');
	let bookFiltersPanel = document.querySelector('.book-filters-panel');
	let closeFiltersButton = document.querySelector('.filters-panel-close');
	
	if (filtersToggleButton && bookFiltersPanel) {
		filtersToggleButton.addEventListener('click', function(e){
			e.preventDefault();
			let isExpanded = this.getAttribute('aria-expanded') === 'true';
			this.setAttribute('aria-expanded', !isExpanded);
			bookFiltersPanel.classList.toggle('active');
		});
	}
	
	if (closeFiltersButton && bookFiltersPanel) {
		closeFiltersButton.addEventListener('click', function(e){
			e.preventDefault();
			bookFiltersPanel.classList.remove('active');
			filtersToggleButton.setAttribute('aria-expanded', 'false');
		});
	}

	let bookFilterForm = document.getElementById('book_filter_form');
	let bookSortSelect = document.getElementById('bookshelf-sort-select');
	let bookFilterSelect = document.getElementById('bookshelf_tag_filters');
	let bookshelf = document.getElementById('bookshelf');
	let loadingAnimation = document.querySelector('.loading-animation');
	let bookSortingButton = document.querySelector('.sort-button');
	let bookshelfSortingInput = document.querySelector('#book_filter_form input[name="bookshelf_sorting"]');
	let searchEl = jQuery('#book_title');
	let resultsEl = jQuery('#search_results');
	let searchClose = document.querySelector('.search-close');
	
	let filterButton = document.querySelector('#filterButton');
	//let filterItems = document.querySelectorAll('#filter_wrapper input');
	
	/* Book Filtering with Checkboxes */
	if (filterButton) {
		filterButton.addEventListener("click", function(event) {
			event.preventDefault();
			loadingAnimation.classList.add('active');
			if (!bookFiltersPanel) {
				let bookFiltersPanel = document.getElementById('book-filters-panel');
				bookFiltersPanel.classList.toggle('active');
			} else {
				bookFiltersPanel.classList.remove('active');
			}
			

			var formData = new FormData(bookFilterForm);
			//formData.append("bookshelf_sorting", bookshelfSortingInput.value);
			formData.append("action", "book_list");
			
			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then((response) => response.text())
			.then((text) => {
				console.log(text);
				bookshelf.innerHTML = text;
			})
			.then((data) => {
				if (data) {
					console.log(data);
					bookshelf.innerHTML = data;
				}
				loadingAnimation.classList.remove('active');
				window.scrollTo({top: 0, behavior: 'smooth'});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		});
	}
	
	/* Book Filtering with a select menu */
	if (bookFilterSelect) {
		bookFilterSelect.addEventListener("change", function bookFilter() {
			loadingAnimation.classList.add('active');
			var formData = new FormData(bookFilterForm);
			formData.append("bookshelf_sorting", bookshelfSortingInput.value);
			formData.append("action", "book_list");
			
			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then((response) => response.text())
			.then((text) => {
				//console.log(text);
				bookshelf.innerHTML = text;
			})
			.then((data) => {
				if (data) {
					//console.log(data);
					bookshelf.innerHTML = data;
				}
				loadingAnimation.classList.remove('active');
				window.scrollTo({top: 0, behavior: 'smooth'});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		})
	}

	if (bookSortSelect) {
		/*
		bookSortSelect.addEventListener('change', function(event) {
			
			loadingAnimation.classList.add('active');
			var formData = new FormData(bookFilterForm);
			
			formData.append("action", "book_list");
			
			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then((response) => response.text())
			.then((text) => {
				//console.log(text);
				bookshelf.innerHTML = text;
			})
			.then((data) => {
				if (data) {
					//console.log(data);
					bookshelf.innerHTML = data;
				}
				loadingAnimation.classList.remove('active');
				window.scrollTo({top: 0, behavior: 'smooth'});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		})
		*/
	}

	if (bookSortingButton) {
		bookSortingButton.addEventListener('click', function(e){
			//e.preventDefault();
			if (bookshelfSortingInput.value == 'ASC') {
				bookshelfSortingInput.value = 'DESC';
			} else {
				bookshelfSortingInput.value = 'ASC';
			}
			loadingAnimation.classList.add('active');
			var formData = new FormData(bookFilterForm);
			formData.append("bookshelf_sorting", bookshelfSortingInput.value);
			formData.append("action", "book_list");
			
			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then((response) => response.text())
			.then((text) => {
				//console.log(text);
				bookshelf.innerHTML = text;
			})
			.then((data) => {
				if (data) {
					//console.log(data);
					bookshelf.innerHTML = data;
				}
				loadingAnimation.classList.remove('active');
				window.scrollTo({top: 0, behavior: 'smooth'});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		})
	}
	
	if (searchEl.length == 0) {
		searchEl.on('input', function () {
			jQuery('#search_results').addClass('active');
			jQuery('.close-button').addClass('active');
			jQuery.post(bookwormAjax.url, {
				action: 'book_search',
				nonce: bookwormAjax.bookworm_thinking_nonce,
				data: {
					s: jQuery(this).val(),
					id: jQuery('#user_id_from').val()
				}
			}).done(function(data) {
				resultsEl.html(data);
				
				// Loop through search result book links
				var bookLinks = document.querySelectorAll('.book-search-entry-link');
				
				bookLinks.forEach(function (bookLink, index) {
					var bwId = bookLink.dataset.bwid;
					
					bookLink.href = '/update-book/?id=' + bwId;
				});
			})
		})
	}
	
	if (searchClose) {
		searchClose.addEventListener('click', function(e){
			e.preventDefault();
			searchClose.classList.remove('active');
			jQuery('#search_results').removeClass('active');
		})
	}
	
	let popupClose = document.querySelectorAll('.popup-close');
	let popupNotesTriggers = document.querySelectorAll('.popup-trigger');
	
	if (popupNotesTriggers.length > 0) {
		popupNotesTriggers.forEach((popupNotesTrigger) => {
			popupNotesTrigger.addEventListener('click', function(e) {
				e.preventDefault();
				
				let popupId = e.target.getAttribute('href');
				console.log('click ' + popupId);
				document.querySelector(popupId).classList.add('active');
			})
		})
	}

	if (popupClose.length == 0) {
		popupClose.forEach((popupCloser) => {
			popupCloser.addEventListener('click', function(e){
				e.preventDefault();
				/*popupCloser.classList.remove('active');*/
				e.target.closest('.popup-wrapper').classList.remove('active');
			});
		})
	}
	
	// Handle click and hold on Bookshelf Nav Item
    function handleClickHold(el, timeout) {
        var timesUp = 0;
        let timeoutID;
        function mouseDown() {
            timeoutID = setTimeout(function(){
                logoRedraw();
                timesUp = 1;
            }, timeout);
        }
            
        function mouseUp() {
            clearTimeout(timeoutID);
            if (timesUp == 0) {
                window.location = "/";
            }
            timesUp = 0;
        }
        
        el.addEventListener("mousedown", mouseDown);
        el.addEventListener("mouseup", mouseUp);
    }

    let bookshelfNavItem = document.getElementById('bookshelf_nav_item');
    if (bookshelfNavItem) {
	    console.log('nav item is here');
	    let bookshelfLink = bookshelfNavItem.href;
	    let bookshelfNav = document.getElementById('bookshelf_nav');
	
	    //var bwOnLongTouch; 
	    var bwTimer;
	    var bwLongTouch = 0; // long touch? yes/no 1/0
	    var bwTouchDuration = 500; //length of time we want the user to touch before we do something
	   
	    function bwTouchStart(e) {
	        e.preventDefault();
	        console.log('touch start is start');
	        bwTimer = setTimeout(bwOnLongTouch, bwTouchDuration); 
	    }
	
	    function bwTouchEnd() {
	        //stops short touches from firing the event
	        if (bwTimer) {
	            clearTimeout(bwTimer); // clearTimeout, not cleartimeout...
	        }
	        if (bwLongTouch == 0) {
	            window.location = bookshelfLink;
	        }
	        bwLongTouch = 0; // reset if long touch
	    }
	    
	    function bwOnLongTouch() {
	        bwLongTouch = 1; // long touch has happened
	        console.log('long touch');
	        bookshelfNav.classList.add('active'); 
	    }
	    
	    bookshelfNavItem.addEventListener('touchstart', bwTouchStart);
		bookshelfNavItem.addEventListener('touchend', bwTouchEnd);
	}

    let navPopupCloser = document.getElementById('navPopupCloser');
    if (navPopupCloser) {
	    let bookshelfNav = document.getElementById('bookshelf_nav');
	    navPopupCloser.addEventListener('click', function(e){
	        e.preventDefault();
	        bookshelfNav.classList.remove('active');
	    });
	}
	
	// Touch Sliders
	let bookshelfSlider = document.querySelector('.slider');
		if (bookshelfSlider && window.innerWidth <= 939) {

			function createCarousel(carouselElement) {
			
				let slideWrapper = carouselElement.querySelector('.slides-inner');
				let slides = carouselElement.querySelector('.slides');
				let slideWidth = carouselElement.offsetWidth;
				let slideNumber = carouselElement.querySelectorAll('.slide').length;
				let slideWrapperWidth = slideWidth * slideNumber;
				let sliderNav = carouselElement.querySelector('#slider_nav');
				let currentSlide = 0;
				let firstDot = sliderNav.firstElementChild;

				sliderNav.classList.add('active');
				firstDot.classList.add('active');
				slides.setAttribute("style", "width:" + slideWrapperWidth + "px");
			
				// Function to move to a specific slide
				function goToSlide(slideIndex) {
					currentSlide = slideIndex;
					slideWrapper.style.transform = `translateX(${currentSlide * slideWidth}px)`;
				}

				const touchStartHandler = function(event) {
					touchDistance = 0;
					startX = event.touches[0].clientX;
				}
				
				const touchSlideMover = function(event) {
					const moveX = event.touches[0].clientX;
					touchDistance = startX - moveX;

					// Update slider position based on touch movement (adjust logic as needed)
					slideWrapper.style.transform = `translateX(${currentSlide * slideWidth - touchDistance}px)`;
				}

				const touchEndHandler = function(event) {

					console.log('total distance = ' + touchDistance);
					// Logic to determine which slide to snap to based on touch movement distance
					const threshold = slideWidth / 5; // Adjust threshold for sensitivity

					if (Math.abs(touchDistance) > threshold) {
						if (touchDistance > 0) {
							if (currentSlide != (-slideNumber + 1)) {
								goToSlide(currentSlide - 1); // Move to next slide
								let activeSlideDot = carouselElement.querySelector('.slider-nav-dot.active');
								activeSlideDot.nextSibling.classList.add('active');
								activeSlideDot.classList.remove('active');
							} else {
								goToSlide(currentSlide); // Stay on the same slide
							}
						} else {
							if (currentSlide != 0) {
								goToSlide(currentSlide + 1); // Move to previous slide
								let activeSlideDot = carouselElement.querySelector('.slider-nav-dot.active');
								activeSlideDot.previousSibling.classList.add('active');
								activeSlideDot.classList.remove('active');
							} else {
								goToSlide(currentSlide); // Stay on the same slide
							}
						}
					} else {
						goToSlide(currentSlide); // Stay on the same slide
						console.log('sit, Ubu, sit. Good dog.');
					}
				};

				slideWrapper.addEventListener('touchstart', touchStartHandler);
				slideWrapper.addEventListener('touchmove', touchSlideMover);
				slideWrapper.addEventListener('touchend', touchEndHandler);
			}

			const carousels = document.querySelectorAll('.slider');
			carousels.forEach(createCarousel);

		}

}, false);