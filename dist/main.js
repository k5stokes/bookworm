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
	
	if (searchEl.length != 0) {
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
	
	// Store popup trigger handlers to allow removal
	let popupTriggerHandlers = new Map();

	function handlePopups() {
		let popupClose = document.querySelectorAll('.popup-close');
		let popupTriggers = document.querySelectorAll('.popup-trigger');
		
		if (popupTriggers.length > 0) {
			popupTriggers.forEach((popupTrigger) => {
				// Remove existing event listener if it exists
				if (popupTriggerHandlers.has(popupTrigger)) {
					popupTrigger.removeEventListener('click', popupTriggerHandlers.get(popupTrigger));
				}

				// Create and store the new handler
				const clickHandler = function(e) {
					e.preventDefault();
					
					let popupId = e.target.getAttribute('href');
					console.log('click ' + popupId);
					document.querySelector(popupId).classList.add('active');
				};
				
				popupTriggerHandlers.set(popupTrigger, clickHandler);
				popupTrigger.addEventListener('click', clickHandler);
			})
		}

		if (popupClose.length != 0) {
			popupClose.forEach((popupCloser) => {
				popupCloser.addEventListener('click', function(e){
					e.preventDefault();
					/*popupCloser.classList.remove('active');*/
					e.target.closest('.popup-wrapper').classList.remove('active');
				});
			})
		}
	}

	handlePopups();
	
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

	
		let validationError = document.getElementById('validation_error');
		//let loadingAnimation = document.querySelector('.loading-animation');
		let dateRangeSelect = document.querySelector('select[name="bookshelf_date_range"]');
		let selectedDateRange = dateRangeSelect.options[dateRangeSelect.selectedIndex].text;

		let startDate = document.getElementById('bookshelf_start_date');
		new AirDatepicker(startDate, {
			isMobile: true,
    		autoClose: true,
			buttons: ['clear'],
			onSelect: ({date}) => {
				const year = date.getFullYear();
				const month = String(date.getMonth() + 1).padStart(2, '0');
				const day = String(date.getDate()).padStart(2, '0');
				const formattedDate = `${year}-${month}-${day}`;
				startDate.value = formattedDate;
				dateRangeSelect.value = 'custom';
			}
		})

		let endDate = document.getElementById('bookshelf_end_date');
		new AirDatepicker(endDate, {
			isMobile: true,
    		autoClose: true,
			buttons: ['clear'],
			onSelect: ({date}) => {
				const year = date.getFullYear();
				const month = String(date.getMonth() + 1).padStart(2, '0');
				const day = String(date.getDate()).padStart(2, '0');
				const formattedDate = `${year}-${month}-${day}`;
				endDate.value = formattedDate;
				dateRangeSelect.value = 'custom';
			}
		})

		// Slider Stuff
		let sliderNavDots = document.querySelectorAll('.slider-nav-dot');
		let carouselButtons = document.querySelectorAll('.carousel-button');
		let sliderNav = document.querySelector('#slider_nav');
		sliderNav.style = 'display: none;';
		carouselButtons.forEach(button => button.style.display = 'none');

		if (window.innerWidth <= 499 && sliderNavDots.length > 2) {
			carouselButtons.forEach(dot => dot.style.display = 'block');
			sliderNav.style = 'display: flex;';
		} else if (window.innerWidth > 500 && window.innerWidth <= 665 && sliderNavDots.length > 3) {
			carouselButtons.forEach(dot => dot.style.display = 'block');
			sliderNav.style = 'display: flex;';
		} else if (window.innerWidth > 665 && window.innerWidth <= 850 && sliderNavDots.length > 4) {
			carouselButtons.forEach(dot => dot.style.display = 'block');
			sliderNav.style = 'display: flex;';
		} else if (window.innerWidth > 850 && sliderNavDots.length > 5) {
			carouselButtons.forEach(dot => dot.style.display = 'block');
			sliderNav.style = 'display: flex;';
		}

		// Gemini Summary fetch
		function fetchGeminiSummary(startVal, endVal, rangeVal) {
			fetch(bookwormAjax.url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'get_gemini_summary',
					nonce: bookwormAjax.bookworm_thinking_nonce,
					bookshelf_start_date: startVal,
					bookshelf_end_date: endVal,
					//bookshelf_date_range: rangeVal
				})
			})
			.then(response => response.json())
			.then(data => {
				const summaryContent = document.getElementById('gemini-summary-content');
				if (data.success) {
					summaryContent.innerHTML = data.data + '</p><p class="small"><em>Summary generated by Google Gemini</em></p></div>';
				} else {
					summaryContent.innerHTML = '<div class="ai-summary"><p>Error: ' + data.data + '</p></div>';
				}
			})
			.catch(error => {
				const summaryContent = document.getElementById('gemini-summary-content');
				summaryContent.innerHTML = '<div class="ai-summary"><p>Error loading summary.</p></div>';
				console.error('Error:', error);
					console.log(data.data);
			});
		}

		// Load Gemini summary
		let generateAISummaryButton = document.getElementById('generate_gemini_summary');
		let geminiClickHandler = null;
		
		function createGeminiHandler(startDate, endDate) {
			return function(event) {
				event.preventDefault();
				let geminiSummaryContent = document.querySelector('#gemini-summary-content');
					geminiSummaryContent.innerHTML = `<div class="loading-spinner">
                     <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce0" attributeName="cy" begin="0;svgSpinners3DotsBounce1.end+0.25s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="12" cy="12" r="3" fill="currentColor"><animate attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.1s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle><circle cx="20" cy="12" r="3" fill="currentColor"><animate id="svgSpinners3DotsBounce1" attributeName="cy" begin="svgSpinners3DotsBounce0.begin+0.2s" calcMode="spline" dur="0.6s" keySplines=".33,.66,.66,1;.33,0,.66,.33" values="12;6;12"/></circle></svg>
                     <p>Generating AI summary...</p>
				</div>`;
				generateAISummaryButton.style = 'display: none;';
				fetchGeminiSummary(startDate, endDate);
			};
		}
		
		geminiClickHandler = createGeminiHandler(bookshelf_start_date, bookshelf_end_date);
		generateAISummaryButton.addEventListener('click', geminiClickHandler);

		// Draw us some charts
		drawRatingsCharts(bookshelf_start_date, bookshelf_end_date);

		// Ratings Charts function
		function drawRatingsCharts(startVal, endVal) {
			let bookAnalyticsForm = document.querySelector('#book_analytics_form');
			var formData = new FormData(bookAnalyticsForm);

			if (startVal && endVal) {
				// Override the date values with passed parameters
				formData.set("bookshelf_start_date", startVal);
				formData.set("bookshelf_end_date", endVal);
			}

			formData.append("action", "book_analytics_ratings_query");

			fetch([bookwormAjax.url], {
				method: "POST",
				credentials: "same-origin",
				body: formData
			})
			.then(r => r.json())
			.then(data => {
				data.data.forEach(item => {

					var frequencyMap = item.data.reduce((accumulator, currentValue) => {
						// If the current number is already a key in the accumulator object, increment its count.
						// Otherwise, initialize its count to 1.
						accumulator[currentValue] = (accumulator[currentValue] || 0) + 1;
						return accumulator;
					}, {}); // The second argument {} is the initial value of the accumulator (an empty object).

					var data = {
						datasets: [{
							label: item.label,
							data: Object.entries(frequencyMap).map(([key, value]) => ({
								x: parseInt(key),
								y: 0,
								r: value
							})),
							backgroundColor: item.bg_color
						}]
					};

					// 1. Find the existing chart instance using the canvas ID
					const existingChart = Chart.getChart(item.rating);

					// 2. If it exists, destroy it
					if (existingChart) {
						existingChart.destroy();
					}

					var bookChart = new Chart(
						document.getElementById(item.rating),
						{
							type: 'bubble',
							data: data,
							options: {
								plugins: {
									tooltip: {
										callbacks: {
											label: function(context) {
												return 'Count: ' + context.raw.r + ' of ' + item.data.length;
											}
										}
									}
								},
								scales: {
									x: { // X-axis configuration
										type: 'linear', // Essential for numerical ranges
										position: 'bottom',
										suggestedMax: 5,
										suggestedMin: 1,
										ticks: {
											stepSize: 1, // Forces the ticks to be 1, 2, 3, etc.
											// or use precision: 0 as an alternative:
											// precision: 0 
										}
									},
									y: { // Y-axis configuration
										type: 'linear', // Essential for numerical ranges
										suggestedMax: 10,
										suggestedMin: -10,
										ticks: {
											display: false // This hides the numbers on the y-axis
										},
										grid: {
											display: false // This hides the grid lines for the y-axis
										}
									}
								}
							}
						}
					);
				});
			})
			.catch((error) => {
				console.log("Error: ");
				console.error(error);
			});
		} // End drawRatingsCharts

		// Book Analytics AJAX
		let bookAnalyticsForm = document.querySelector('#book_analytics_form');
		let dateRangeButton = document.querySelector('#dateRangeButton');
		let timePeriodsList = document.querySelector('#timePeriodsList');
		let analyticsTags = document.querySelector('#analytics_tags');
		let analyticsDateRangeLabel = document.querySelector('.analytics-date-range-label');

		// If you choose the drop-down/select date range, fill in the input fields with the appropriate dates
		dateRangeSelect.addEventListener('change', function() {
			let selectedOptionValue = this.options[this.selectedIndex].value;
			console.log('Selected option value:', selectedOptionValue);
			if (selectedOptionValue === 'P12M') {
				// Past 12 months
				let endDate = new Date();
				let startDate = new Date();
				startDate.setFullYear(endDate.getFullYear() - 1);

				const formatDate = (date) => {
					const year = date.getFullYear();
					const month = String(date.getMonth() + 1).padStart(2, '0');
					const day = String(date.getDate()).padStart(2, '0');
					return `${year}-${month}-${day}`;
				};

				document.getElementById('bookshelf_start_date').value = formatDate(startDate);
				document.getElementById('bookshelf_end_date').value = formatDate(endDate);
			} else if (selectedOptionValue === 'current') {
				// Year-to-Date
				let endDate = new Date();
				let startDate = new Date(endDate.getFullYear(), 0, 1); // January 1st of current year

				const formatDate = (date) => {
					const year = date.getFullYear();
					const month = String(date.getMonth() + 1).padStart(2, '0');
					const day = String(date.getDate()).padStart(2, '0');
					return `${year}-${month}-${day}`;
				};

				document.getElementById('bookshelf_start_date').value = formatDate(startDate);
				document.getElementById('bookshelf_end_date').value = formatDate(endDate);
			} else if (selectedOptionValue === 'custom') {
				// Clear date inputs and let user pick dates
				document.getElementById('bookshelf_start_date').value = '';
				document.getElementById('bookshelf_end_date').value = '';
			}
		});

		if (dateRangeButton) {
			// Basic Analytics
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();

				let bookshelfStartDate = document.querySelector('#bookshelf_start_date').value;
				let bookshelfEndDate = document.querySelector('#bookshelf_end_date').value;

				if (bookshelfStartDate === '' || bookshelfEndDate === '') {
					validationError.innerText = 'Please select both a start and end date.';
					validationError.style.display = 'block';
					return;
				} else {
					validationError.style.display = 'none';
				}
				loadingAnimation.classList.add('active');

				// Update the date range label
				if (bookshelfStartDate && bookshelfEndDate) {
					analyticsDateRangeLabel.innerText = `From ${bookshelfStartDate} to ${bookshelfEndDate}`;
				} else {
					analyticsDateRangeLabel.innerText = selectedDateRange;
				}
		
				var formData = new FormData(bookAnalyticsForm);
				formData.append("action", "book_analytics_query");
				
				fetch([bookwormAjax.url], {
					method: "POST",
					credentials: "same-origin",
					body: formData
				})
				.then((response) => response.text())
				.then((text) => {
					timePeriodsList.innerHTML = text;
					console.log(text);
				})
				.then((data) => {
					if (data) {
						timePeriodsList.innerHTML = data;
						conesole.log(data);
					}
					// Reinitialize carousels with updated content
					let slider = document.querySelector('.analytics-slider');
					console.log(slider);
					if (slider) {
						initializeAnalyticsCarousels();
						
						let sliderNavDots = document.querySelectorAll('.slider-nav-dot');
						let carouselButtons = document.querySelectorAll('.carousel-button');
						let sliderNav = document.querySelector('#slider_nav');
						sliderNav.style = 'display: none;';
						carouselButtons.forEach(button => button.style.display = 'none');

						if (window.innerWidth <= 499 && sliderNavDots.length > 2) {
							carouselButtons.forEach(dot => dot.style.display = 'block');
							sliderNav.style = 'display: flex;';
						} else if (window.innerWidth > 500 && window.innerWidth <= 665 && sliderNavDots.length > 3) {
							carouselButtons.forEach(dot => dot.style.display = 'block');
							sliderNav.style = 'display: flex;';
						} else if (window.innerWidth > 665 && window.innerWidth <= 850 && sliderNavDots.length > 4) {
							carouselButtons.forEach(dot => dot.style.display = 'block');
							sliderNav.style = 'display: flex;';
						} else if (window.innerWidth > 850 && sliderNavDots.length > 5) {
							carouselButtons.forEach(dot => dot.style.display = 'block');
							sliderNav.style = 'display: flex;';
						}
}
					loadingAnimation.classList.remove('active');
					window.scrollTo({top: 0, behavior: 'smooth'});
				})
				.catch((error) => {
					console.log("Error: ");
					console.error(error);
				});
			});

			// Tags
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();

				let bookshelfStartDate = document.querySelector('#bookshelf_start_date').value;
				let bookshelfEndDate = document.querySelector('#bookshelf_end_date').value;

				if (bookshelfStartDate === '' || bookshelfEndDate === '') {
					validationError.innerText = 'Please select both a start and end date.';
					validationError.style.display = 'block';
					return;
				} else {
					validationError.style.display = 'none';
				}
		
				var formData = new FormData(bookAnalyticsForm);
				formData.append("action", "book_analytics_tags_query");
				
				fetch([bookwormAjax.url], {
					method: "POST",
					credentials: "same-origin",
					body: formData
				})
				.then((response) => response.text())
				.then((text) => {
					analyticsTags.innerHTML = text;
				})
				.then((data) => {
					if (data) {
						analyticsTags.innerHTML = data;
					}
					handlePopups();
				})
				.catch((error) => {
					console.log("Error: ");
					console.error(error);
				});
			});

			// AI Notes Summary
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();
				let bookshelfStartDate = document.querySelector('#bookshelf_start_date').value;
				let bookshelfEndDate = document.querySelector('#bookshelf_end_date').value;
				let geminiSummaryContent = document.querySelector('#gemini-summary-content');

				if (bookshelfStartDate === '' || bookshelfEndDate === '') {
					validationError.innerText = 'Please select both a start and end date.';
					validationError.style.display = 'block';
					return;
				} else {
					validationError.style.display = 'none';
				}

				geminiSummaryContent.innerHTML = `&nbsp;`;
				generateAISummaryButton.style = 'display: inline-block;';

				// Remove the old event listener
				if (geminiClickHandler) {
					generateAISummaryButton.removeEventListener('click', geminiClickHandler);
				}

				// Create and add the new event listener with updated dates
				geminiClickHandler = createGeminiHandler(bookshelfStartDate, bookshelfEndDate);
				generateAISummaryButton.addEventListener('click', geminiClickHandler);
			});

			// Ratings
			dateRangeButton.addEventListener("click", function(event) {
				event.preventDefault();

				let bookshelfStartDate = document.querySelector('#bookshelf_start_date').value;
				let bookshelfEndDate = document.querySelector('#bookshelf_end_date').value;

				if (bookshelfStartDate === '' || bookshelfEndDate === '') {
					validationError.innerText = 'Please select both a start and end date.';
					validationError.style.display = 'block';
					return;
				} else {
					validationError.style.display = 'none';
				}

				// Draw us some charts
				drawRatingsCharts();
			});
		}

		// Analytics Sliders - Create a reusable function to initialize carousels
		function initializeAnalyticsCarousels() {
			let analyticsSliders = document.querySelectorAll('.analytics-slider');
			
			if (analyticsSliders.length === 0) {
				return; // No sliders found, exit the function
			}
			function createAnalyticsCarousel(carouselElement) {
			
				let slideWrapper = carouselElement.querySelector('.slides-inner');
				let slides = carouselElement.querySelector('.slides');
				let slideWidth = carouselElement.querySelector('.slide').offsetWidth;
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

				// Click controls for next and previous buttons
				const nextButton = carouselElement.querySelector('.carousel-next');
				const prevButton = carouselElement.querySelector('.carousel-prev');

				if (nextButton) {
					nextButton.addEventListener('click', function(e) {
						e.preventDefault();
						if (currentSlide != (-slideNumber + 1)) {
							goToSlide(currentSlide - 1); // Move to next slide
							let activeSlideDot = carouselElement.querySelector('.slider-nav-dot.active');
							if (activeSlideDot.nextSibling) {
								activeSlideDot.nextSibling.classList.add('active');
								activeSlideDot.classList.remove('active');
							}
						}
					});
				}

				if (prevButton) {
					prevButton.addEventListener('click', function(e) {
						e.preventDefault();
						if (currentSlide != 0) {
							goToSlide(currentSlide + 1); // Move to previous slide
							let activeSlideDot = carouselElement.querySelector('.slider-nav-dot.active');
							if (activeSlideDot.previousSibling) {
								activeSlideDot.previousSibling.classList.add('active');
								activeSlideDot.classList.remove('active');
							}
						}
					});
				}
			}

			analyticsSliders.forEach(createAnalyticsCarousel);
		}

		// Initialize carousels on page load
		initializeAnalyticsCarousels();	

}, false);