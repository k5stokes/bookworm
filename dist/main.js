document.addEventListener('DOMContentLoaded', function () {
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