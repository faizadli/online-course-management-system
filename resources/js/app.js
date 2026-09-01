import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Landing navbar scroll effect (hero home page only)
    const landingNav = document.getElementById('landing-nav');
    if (landingNav && landingNav.dataset.heroNav === 'true') {
        const updateNav = () => {
            landingNav.classList.toggle('nav-scrolled', window.scrollY > 60);
        };
        landingNav.classList.remove('nav-scrolled');
        updateNav();
        window.addEventListener('scroll', updateNav, { passive: true });
    }

    // Landing mobile menu
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
        });
        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => mobileMenu.classList.remove('open'));
        });
    }

    // Scroll reveal
    const revealElements = document.querySelectorAll('.reveal');
    if (revealElements.length) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
        );
        revealElements.forEach((el) => observer.observe(el));
    }

    // Animate progress bars & chart bars on dashboard
    document.querySelectorAll('[data-progress]').forEach((bar) => {
        const width = bar.dataset.progress;
        requestAnimationFrame(() => {
            bar.style.width = width + '%';
        });
    });

    document.querySelectorAll('[data-bar-height]').forEach((bar) => {
        const height = bar.dataset.barHeight;
        requestAnimationFrame(() => {
            bar.style.height = height + '%';
        });
    });

    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('dashboard-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay?.classList.toggle('hidden');
        });
        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }

    // Courses catalog — search, filters & pagination
    initCoursesCatalog();

    // Checkout payment method accordion
    initCheckoutPage();

    // Instructor courses search & pagination
    initInstructorCourses();
});

function initCheckoutPage() {
    const paymentMethods = document.getElementById('payment-methods');
    if (!paymentMethods) return;

    paymentMethods.addEventListener('click', (e) => {
        const header = e.target.closest('.payment-method-header');
        if (!header) return;

        const method = header.closest('.payment-method');
        paymentMethods.querySelectorAll('.payment-method').forEach((el) => {
            el.classList.toggle('is-open', el === method);
        });
    });
}

function initInstructorCourses() {
    const section = document.getElementById('instructor-courses');
    if (!section) return;

    const searchInput = document.getElementById('instructor-course-search');
    const grid = document.getElementById('instructor-courses-grid');
    const emptyState = document.getElementById('instructor-courses-empty');
    const pagination = document.getElementById('instructor-courses-pagination');
    const resultCount = document.getElementById('instructor-courses-count');
    const cards = Array.from(section.querySelectorAll('.instructor-course-card'));
    const perPage = parseInt(section.dataset.perPage, 10) || 3;

    let currentPage = 1;
    let searchDebounceTimer = null;

    const getFilteredCards = () => {
        const query = searchInput.value.trim().toLowerCase();
        if (!query) return [...cards];

        return cards.filter((card) => {
            const title = card.dataset.title || '';
            const category = card.dataset.category || '';
            return title.includes(query) || category.includes(query);
        });
    };

    const getPageNumbers = (totalPages, current) => {
        if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, i) => i + 1);
        }

        const pages = [1];
        if (current > 3) pages.push('…');

        const start = Math.max(2, current - 1);
        const end = Math.min(totalPages - 1, current + 1);
        for (let i = start; i <= end; i++) pages.push(i);

        if (current < totalPages - 2) pages.push('…');
        pages.push(totalPages);

        return pages;
    };

    const renderPagination = (totalPages) => {
        pagination.innerHTML = '';
        if (totalPages <= 1) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'inline-flex items-center gap-5 sm:gap-6';

        const goToPage = (page) => {
            currentPage = page;
            render();
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        const createNavBtn = (label, page, disabled, direction) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.disabled = disabled;
            btn.setAttribute('aria-label', label);
            btn.className = 'pagination-nav-btn';

            if (direction === 'prev') {
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"/></svg><span>Prev</span>';
            } else {
                btn.innerHTML = '<span>Next</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"/></svg>';
            }

            if (!disabled) {
                btn.addEventListener('click', () => goToPage(page));
            }

            return btn;
        };

        wrapper.appendChild(createNavBtn('Previous page', currentPage - 1, currentPage === 1, 'prev'));

        const pagesWrap = document.createElement('div');
        pagesWrap.className = 'flex items-center gap-3 sm:gap-4';

        getPageNumbers(totalPages, currentPage).forEach((item) => {
            if (item === '…') {
                const span = document.createElement('span');
                span.textContent = '…';
                span.className = 'text-sm text-gray-300 select-none';
                pagesWrap.appendChild(span);
                return;
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(item);
            btn.className = `pagination-page-btn relative${item === currentPage ? ' is-active' : ''}`;
            btn.addEventListener('click', () => goToPage(item));
            pagesWrap.appendChild(btn);
        });

        wrapper.appendChild(pagesWrap);
        wrapper.appendChild(createNavBtn('Next page', currentPage + 1, currentPage === totalPages, 'next'));
        pagination.appendChild(wrapper);
    };

    const render = () => {
        const filtered = getFilteredCards();
        const total = filtered.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        cards.forEach((card) => card.classList.add('hidden'));

        if (total === 0) {
            grid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            pagination.innerHTML = '';
            resultCount.textContent = 'No courses found';
            return;
        }

        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');

        const start = (currentPage - 1) * perPage;
        filtered.slice(start, start + perPage).forEach((card) => card.classList.remove('hidden'));

        const showingStart = start + 1;
        const showingEnd = Math.min(start + perPage, total);
        resultCount.textContent = searchInput.value.trim()
            ? `Showing ${showingStart}–${showingEnd} of ${total} matching courses`
            : `Showing ${showingStart}–${showingEnd} of ${total} courses`;

        renderPagination(totalPages);
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            currentPage = 1;
            render();
        }, 300);
    });

    render();
}

function initCoursesCatalog() {
    const catalog = document.getElementById('courses-catalog');
    if (!catalog) return;

    const searchInput = document.getElementById('course-search-input');
    const categoryFilter = document.getElementById('course-category-filter');
    const categoryCombobox = document.getElementById('course-category-combobox');
    const categoryTrigger = document.getElementById('course-category-trigger');
    const categoryDropdown = document.getElementById('course-category-dropdown');
    const categorySearch = document.getElementById('course-category-search');
    const categoryLabel = document.getElementById('course-category-label');
    const categoryOptions = document.getElementById('course-category-options');
    const priceMinInput = document.getElementById('price-min');
    const priceMaxInput = document.getElementById('price-max');
    const priceMinNum = document.getElementById('price-min-num');
    const priceMaxNum = document.getElementById('price-max-num');
    const priceRangeFill = document.getElementById('price-range-fill');
    const resetBtn = document.getElementById('course-reset-filters');
    const resultCount = document.getElementById('courses-result-count');
    const emptyState = document.getElementById('courses-empty');
    const pagination = document.getElementById('courses-pagination');
    const grid = document.getElementById('courses-grid');
    const cards = Array.from(catalog.querySelectorAll('.course-card'));
    const perPage = parseInt(catalog.dataset.perPage, 10) || 6;
    const absolutePriceMin = 0;
    const defaultPriceMin = absolutePriceMin;
    const defaultPriceMax = parseInt(catalog.dataset.priceMax, 10) || 999;
    const sliderRangeMax = defaultPriceMax;

    let currentPage = 1;
    let filteredCards = [...cards];
    let searchDebounceTimer = null;
    let priceDebounceTimer = null;

    const clampMinPrice = (value, fallback) => {
        const num = parseInt(value, 10);
        if (Number.isNaN(num)) return fallback;
        return Math.max(absolutePriceMin, num);
    };

    const clampMaxPrice = (value, fallback) => {
        const num = parseInt(value, 10);
        if (Number.isNaN(num)) return fallback;
        return Math.max(absolutePriceMin, num);
    };

    const clampSliderValue = (value, fallback) => {
        const num = parseInt(value, 10);
        if (Number.isNaN(num)) return fallback;
        return Math.min(sliderRangeMax, Math.max(absolutePriceMin, num));
    };

    const parsePriceInput = (value) => {
        const trimmed = String(value).trim();
        if (trimmed === '') return null;
        const num = parseInt(trimmed, 10);
        return Number.isNaN(num) ? null : num;
    };

    const getEffectivePrices = () => {
        const minFromNum = parsePriceInput(priceMinNum.value);
        const maxFromNum = parsePriceInput(priceMaxNum.value);
        const minPrice = minFromNum !== null
            ? clampMinPrice(minFromNum, defaultPriceMin)
            : clampSliderValue(priceMinInput.value, defaultPriceMin);
        const maxPrice = maxFromNum !== null
            ? clampMaxPrice(maxFromNum, defaultPriceMax)
            : clampSliderValue(priceMaxInput.value, defaultPriceMax);

        return {
            minPrice: Math.min(minPrice, maxPrice),
            maxPrice: Math.max(minPrice, maxPrice),
        };
    };

    const updateRangeFill = (minVal, maxVal) => {
        const range = sliderRangeMax - absolutePriceMin || 1;
        const sliderMin = clampSliderValue(minVal, defaultPriceMin);
        const visualMax = Math.min(maxVal, sliderRangeMax);
        const left = ((sliderMin - absolutePriceMin) / range) * 100;
        const width = maxVal > sliderRangeMax
            ? 100 - left
            : ((visualMax - sliderMin) / range) * 100;

        priceRangeFill.style.left = `${left}%`;
        priceRangeFill.style.width = `${Math.max(0, width)}%`;
    };

    const syncPriceValues = (source, { clampInputs = true } = {}) => {
        let minVal = clampSliderValue(priceMinInput.value, defaultPriceMin);
        let maxVal = clampMaxPrice(priceMaxNum.value, defaultPriceMax);

        if (source === 'min-num') {
            const parsed = parsePriceInput(priceMinNum.value);
            if (parsed === null) {
                if (!clampInputs) return;
                minVal = defaultPriceMin;
            } else {
                minVal = clampInputs ? clampMinPrice(parsed, defaultPriceMin) : parsed;
            }
            maxVal = clampMaxPrice(priceMaxNum.value, defaultPriceMax);
        }

        if (source === 'max-num') {
            const parsed = parsePriceInput(priceMaxNum.value);
            if (parsed === null) {
                if (!clampInputs) return;
                maxVal = defaultPriceMax;
            } else {
                maxVal = clampInputs ? clampMaxPrice(parsed, defaultPriceMax) : parsed;
            }
            minVal = clampMinPrice(priceMinNum.value, defaultPriceMin);
        }

        if (source === 'min-range') {
            minVal = clampSliderValue(priceMinInput.value, defaultPriceMin);
            maxVal = clampMaxPrice(priceMaxNum.value, defaultPriceMax);
        }

        if (source === 'max-range') {
            maxVal = clampSliderValue(priceMaxInput.value, defaultPriceMax);
            minVal = clampMinPrice(priceMinNum.value, defaultPriceMin);
        }

        if (clampInputs) {
            minVal = clampMinPrice(minVal, defaultPriceMin);
            maxVal = clampMaxPrice(maxVal, defaultPriceMax);
        }

        if (minVal > maxVal) {
            if (source === 'min-num' || source === 'min-range') {
                maxVal = minVal;
            } else {
                minVal = maxVal;
            }
        }

        priceMinInput.value = clampSliderValue(minVal, defaultPriceMin);
        priceMaxInput.value = clampSliderValue(maxVal, defaultPriceMax);

        if (clampInputs || source.includes('range')) {
            priceMinNum.value = minVal;
            priceMaxNum.value = maxVal;
        }

        updateRangeFill(minVal, maxVal);
    };

    const updatePriceRangeUI = (source, options = {}) => {
        syncPriceValues(source, options);
    };

    const getFilteredCards = () => {
        const query = searchInput.value.trim().toLowerCase();
        const category = categoryFilter.value;
        const { minPrice, maxPrice } = getEffectivePrices();

        return cards.filter((card) => {
            const title = card.dataset.title || '';
            const instructor = card.dataset.instructor || '';
            const cardCategory = card.dataset.category || '';
            const cardPrice = parseFloat(card.dataset.price) || 0;

            const matchesSearch = !query || title.includes(query) || instructor.includes(query);
            const matchesCategory = !category || cardCategory === category;
            const matchesPriceRange = cardPrice >= minPrice && cardPrice <= maxPrice;

            return matchesSearch && matchesCategory && matchesPriceRange;
        });
    };

    const hasActiveFilters = () => {
        const { minPrice, maxPrice } = getEffectivePrices();
        const priceChanged = minPrice !== defaultPriceMin || maxPrice !== defaultPriceMax;

        return searchInput.value.trim() !== '' || categoryFilter.value !== '' || priceChanged;
    };

    const updateResultCount = (total) => {
        const start = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, total);
        resultCount.textContent = total === 0
            ? 'Showing 0 courses'
            : `Showing ${start}–${end} of ${total} courses`;
        resetBtn.classList.toggle('hidden', !hasActiveFilters());
    };

    const getPageNumbers = (totalPages, current) => {
        if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, i) => i + 1);
        }

        const pages = [1];
        if (current > 3) pages.push('…');

        const start = Math.max(2, current - 1);
        const end = Math.min(totalPages - 1, current + 1);
        for (let i = start; i <= end; i++) pages.push(i);

        if (current < totalPages - 2) pages.push('…');
        pages.push(totalPages);

        return pages;
    };

    const renderPagination = (totalPages) => {
        pagination.innerHTML = '';

        if (totalPages <= 1) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'inline-flex items-center gap-5 sm:gap-6';

        const goToPage = (page) => {
            currentPage = page;
            render();
            catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        const createNavBtn = (label, page, disabled, direction) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.disabled = disabled;
            btn.setAttribute('aria-label', label);
            btn.className = 'pagination-nav-btn';

            if (direction === 'prev') {
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"/></svg><span>Prev</span>';
            } else {
                btn.innerHTML = '<span>Next</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"/></svg>';
            }

            if (!disabled) {
                btn.addEventListener('click', () => goToPage(page));
            }

            return btn;
        };

        wrapper.appendChild(createNavBtn('Previous page', currentPage - 1, currentPage === 1, 'prev'));

        const pagesWrap = document.createElement('div');
        pagesWrap.className = 'flex items-center gap-3 sm:gap-4';

        getPageNumbers(totalPages, currentPage).forEach((item) => {
            if (item === '…') {
                const span = document.createElement('span');
                span.textContent = '…';
                span.className = 'text-sm text-gray-300 select-none';
                pagesWrap.appendChild(span);
                return;
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(item);
            btn.className = `pagination-page-btn relative${item === currentPage ? ' is-active' : ''}`;
            btn.addEventListener('click', () => goToPage(item));
            pagesWrap.appendChild(btn);
        });

        wrapper.appendChild(pagesWrap);
        wrapper.appendChild(createNavBtn('Next page', currentPage + 1, currentPage === totalPages, 'next'));
        pagination.appendChild(wrapper);
    };

    const setCategoryValue = (value, label) => {
        categoryFilter.value = value;
        categoryLabel.textContent = label;
        categoryOptions.querySelectorAll('.category-option').forEach((opt) => {
            opt.classList.toggle('is-selected', opt.dataset.value === value);
        });
    };

    const closeCategoryDropdown = () => {
        categoryCombobox.classList.remove('is-open');
        categoryDropdown.classList.add('hidden');
        categoryTrigger.setAttribute('aria-expanded', 'false');
    };

    const openCategoryDropdown = () => {
        categoryCombobox.classList.add('is-open');
        categoryDropdown.classList.remove('hidden');
        categoryTrigger.setAttribute('aria-expanded', 'true');
        categorySearch.value = '';
        filterCategoryOptions('');
        categorySearch.focus();
    };

    const filterCategoryOptions = (query) => {
        const normalized = query.trim().toLowerCase();
        categoryOptions.querySelectorAll('.category-option').forEach((opt) => {
            const text = opt.textContent.trim().toLowerCase();
            const matches = !normalized || text.includes(normalized);
            opt.classList.toggle('is-hidden', !matches);
        });
    };

    const render = () => {
        filteredCards = getFilteredCards();
        const total = filteredCards.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        cards.forEach((card) => card.classList.add('hidden'));

        if (total === 0) {
            grid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            pagination.innerHTML = '';
            updateResultCount(0);
            return;
        }

        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');

        const start = (currentPage - 1) * perPage;
        const pageCards = filteredCards.slice(start, start + perPage);
        pageCards.forEach((card) => card.classList.remove('hidden'));

        updateResultCount(total);
        renderPagination(totalPages);
    };

    const applyFilters = () => {
        currentPage = 1;
        render();
    };

    const debouncedApplyFilters = (delay = 300) => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(applyFilters, delay);
    };

    const debouncedPriceFilter = () => {
        clearTimeout(priceDebounceTimer);
        priceDebounceTimer = setTimeout(applyFilters, 350);
    };

    categoryTrigger.addEventListener('click', () => {
        if (categoryCombobox.classList.contains('is-open')) {
            closeCategoryDropdown();
        } else {
            openCategoryDropdown();
        }
    });

    categorySearch.addEventListener('input', (e) => {
        filterCategoryOptions(e.target.value);
    });

    categorySearch.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeCategoryDropdown();
    });

    categoryOptions.querySelectorAll('.category-option').forEach((opt) => {
        opt.addEventListener('click', () => {
            setCategoryValue(opt.dataset.value, opt.textContent.trim());
            closeCategoryDropdown();
            applyFilters();
        });
    });

    document.addEventListener('click', (e) => {
        if (!categoryCombobox.contains(e.target)) {
            closeCategoryDropdown();
        }
    });

    setCategoryValue('', 'All Categories');

    priceMinInput.addEventListener('input', () => {
        updatePriceRangeUI('min-range');
        debouncedPriceFilter();
    });
    priceMaxInput.addEventListener('input', () => {
        updatePriceRangeUI('max-range');
        debouncedPriceFilter();
    });
    priceMinNum.addEventListener('input', () => {
        updatePriceRangeUI('min-num', { clampInputs: false });
        debouncedPriceFilter();
    });
    priceMaxNum.addEventListener('input', () => {
        updatePriceRangeUI('max-num', { clampInputs: false });
        debouncedPriceFilter();
    });
    priceMinNum.addEventListener('blur', () => {
        updatePriceRangeUI('min-num', { clampInputs: true });
        applyFilters();
    });
    priceMaxNum.addEventListener('blur', () => {
        updatePriceRangeUI('max-num', { clampInputs: true });
        applyFilters();
    });
    updatePriceRangeUI('min-range');

    searchInput.addEventListener('input', () => debouncedApplyFilters());

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        setCategoryValue('', 'All Categories');
        priceMinInput.value = defaultPriceMin;
        priceMaxInput.value = defaultPriceMax;
        priceMinNum.value = defaultPriceMin;
        priceMaxNum.value = defaultPriceMax;
        updatePriceRangeUI('min-range');
        applyFilters();
    });

    render();
}
