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
});

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
    const defaultPriceMin = parseInt(catalog.dataset.priceMin, 10) || 0;
    const defaultPriceMax = parseInt(catalog.dataset.priceMax, 10) || 999;

    let currentPage = 1;
    let filteredCards = [...cards];
    let searchDebounceTimer = null;
    let priceDebounceTimer = null;

    const clampPrice = (value, fallback) => {
        const num = parseInt(value, 10);
        if (Number.isNaN(num)) return fallback;
        return Math.min(defaultPriceMax, Math.max(defaultPriceMin, num));
    };

    const syncPriceValues = (source) => {
        let minVal = clampPrice(priceMinInput.value, defaultPriceMin);
        let maxVal = clampPrice(priceMaxInput.value, defaultPriceMax);

        if (source === 'min-num') minVal = clampPrice(priceMinNum.value, defaultPriceMin);
        if (source === 'max-num') maxVal = clampPrice(priceMaxNum.value, defaultPriceMax);
        if (source === 'min-range') minVal = clampPrice(priceMinInput.value, defaultPriceMin);
        if (source === 'max-range') maxVal = clampPrice(priceMaxInput.value, defaultPriceMax);

        if (minVal > maxVal) {
            if (source === 'min-num' || source === 'min-range') {
                maxVal = minVal;
            } else {
                minVal = maxVal;
            }
        }

        priceMinInput.value = minVal;
        priceMaxInput.value = maxVal;
        priceMinNum.value = minVal;
        priceMaxNum.value = maxVal;

        const range = defaultPriceMax - defaultPriceMin || 1;
        const left = ((minVal - defaultPriceMin) / range) * 100;
        const width = ((maxVal - minVal) / range) * 100;

        priceRangeFill.style.left = `${left}%`;
        priceRangeFill.style.width = `${width}%`;
    };

    const updatePriceRangeUI = (source) => {
        syncPriceValues(source);
    };

    const getFilteredCards = () => {
        const query = searchInput.value.trim().toLowerCase();
        const category = categoryFilter.value;
        const minPrice = parseInt(priceMinInput.value, 10);
        const maxPrice = parseInt(priceMaxInput.value, 10);

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
        const minPrice = parseInt(priceMinInput.value, 10);
        const maxPrice = parseInt(priceMaxInput.value, 10);
        const priceChanged = minPrice > defaultPriceMin || maxPrice < defaultPriceMax;

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
        updatePriceRangeUI('min-num');
        debouncedPriceFilter();
    });
    priceMaxNum.addEventListener('input', () => {
        updatePriceRangeUI('max-num');
        debouncedPriceFilter();
    });
    priceMinNum.addEventListener('blur', () => {
        updatePriceRangeUI('min-num');
        applyFilters();
    });
    priceMaxNum.addEventListener('blur', () => {
        updatePriceRangeUI('max-num');
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
