/**
 * Dynamic Search for OpenEMR edit_globals.php
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2025-2026 MD Support <mdsupport@users.sf.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// mdadd helpers
Element.prototype.swap = function(removeClasses, addClasses) {
    this.classList.remove(...removeClasses);
    this.classList.add(...addClasses);
    return this; // allow chaining
};

(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        searchInputId: 'srch_desc',
        navSelector: 'ul.tabNav li',
        tabSelector: 'div.tabContainer > div.tab',
        searchableSelector: 'div.oe-input, .form-control',
        debounceDelay: 300,
        highlightClass: 'bg-info',  // Bootstrap class for highlighting
        minSearchLength: 3
    };
    
    // State
    let searchMap = new Map();
    let allNavs = [];
    let allTabs = [];
    let debounceTimer = null;
    let currentSearchTerm = '';
    
    /**
     * Initialize on DOM ready
     */
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setup);
        } else {
            setup();
        }
    }
    
    /**
     * Setup search functionality
     */
    function setup() {
        const searchInput = document.getElementById(CONFIG.searchInputId);
        if (!searchInput) {
            // console.warn('Search input not found');
            return;
        }
        
        // Build global nav and tab arrays
        allNavs = Array.from(document.querySelectorAll(CONFIG.navSelector));
        allTabs = Array.from(document.querySelectorAll(CONFIG.tabSelector));
        
        if (allNavs.length === 0 || allTabs.length === 0) {
            // console.warn('No nav items or tabs found');
            return;
        }
        
        // Build search map
        buildSearchMap();
        
        // Attach event listeners
        attachEventListeners(searchInput);
        
        // console.log('Dynamic search initialized:', searchMap.size, 'unique search terms');
        
        // Some fixes to header
        let doc = document.getElementById('container_div');
        doc.swap(['container', 'mt-2'], ['container-fluid', 'mt-0']);
        doc.querySelector('#pageHeadingNav').remove();
        doc.querySelector('.h1').swap(['h1'],['h5','fw-bold']);
        doc.querySelector('.my-2').swap(['my-2'],['my-0']);
        doc.querySelector('ul.navbar-nav.mr-auto').swap(['mr-auto'], ['me-3']);
        
        const navActions = Object.assign(document.createElement("div"), { 
            id: "navbar-actions", 
            className: "d-flex align-items-center ml-auto" }
        );

        doc.querySelector('div.collapse').appendChild(navActions);

        // Move Save button BEFORE collapse (next to brand)
        const saveBtn = document.querySelector("button[name='form_save']");
        const brand = document.querySelector(".navbar-brand");
        if (saveBtn && brand) {
            saveBtn.classList.remove("oe-pull-toward");
            saveBtn.classList.add("ml-3");
            brand.insertAdjacentElement("afterend", saveBtn);
        }

        // 4. Move Search input-group
        const searchGroup = doc.querySelector("div.clearfix div.col-sm-4");
        if (searchGroup) {
            searchGroup.swap(['col-sm-4', 'oe-pull-away'],['flex-shrink-1', "ml-auto"]); // push to right
            searchGroup.style.minWidth = "200px";
            navActions.appendChild(searchGroup);
        }
    }

    /**
     * Build search map: searchText -> {navs: [indices], items: [elements]}
     */
    function buildSearchMap() {
        allTabs.forEach((tab, tabIndex) => {
            const formGroups = tab.querySelectorAll('div.form-group');
            
            formGroups.forEach(formGroup => {
                // Get all searchable elements within this form-group
                const searchableElements = formGroup.querySelectorAll(CONFIG.searchableSelector);
                let searchableTexts = [];
                
                searchableElements.forEach(el => {
                    // Get title attribute if present
                    if (el.title) {
                        searchableTexts.push(el.title);
                    }
                    
                    // Get visible text based on element type
                    if (el.tagName === 'SELECT') {
                        const selected = el.options[el.selectedIndex];
                        if (selected && selected.text) {
                            searchableTexts.push(selected.text);
                        }
                    } else if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                        if (el.value) {
                            searchableTexts.push(el.value);
                        }
                    } else {
                        // For div.oe-input or other elements, get text content
                        const text = el.textContent.trim();
                        if (text) {
                            searchableTexts.push(text);
                        }
                    }
                });
                
                // Combine all searchable text for this form-group
                const combinedText = searchableTexts.join(' ').toLowerCase().trim();
                
                if (combinedText) {
                    // Add to map
                    if (!searchMap.has(combinedText)) {
                        searchMap.set(combinedText, {
                            navs: [],
                            items: []
                        });
                    }
                    
                    const entry = searchMap.get(combinedText);
                    
                    // Add nav index if not already present
                    if (!entry.navs.includes(tabIndex)) {
                        entry.navs.push(tabIndex);
                    }
                    
                    // Add form-group element
                    entry.items.push(formGroup);
                }
            });
        });
    }
    
    /**
     * Attach event listeners
     */
    function attachEventListeners(searchInput) {
        // Search input
        searchInput.addEventListener('input', function(e) {
            const value = e.target.value.trim();
            
            // Visual feedback if below minimum length
            if (value.length > 0 && value.length < CONFIG.minSearchLength) {
                searchInput.style.borderColor = '#ffc107';
            } else {
                searchInput.style.borderColor = '';
            }
            
            handleSearchInput(e.target.value);
        });
        
        // Prevent form submission on Enter
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });
        
        // Nav clicks - for highlighting when tab becomes current
        allNavs.forEach((nav, index) => {
            nav.addEventListener('click', function() {
                if (currentSearchTerm) {
                    // Small delay to allow tab switch
                    setTimeout(() => {
                        highlightMatchesInCurrentTab();
                    }, 100);
                }
            });
        });
        
        // Watch for tab changes using MutationObserver
        const tabContainer = document.querySelector('div.tabContainer');
        if (tabContainer) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const target = mutation.target;
                        if (target.classList.contains('tab') && 
                            target.classList.contains('current') && 
                            currentSearchTerm) {
                            setTimeout(() => {
                                highlightMatchesInCurrentTab();
                            }, 100);
                        }
                    }
                });
            });
            
            // Observe all tab divs for class changes
            allTabs.forEach(tab => {
                observer.observe(tab, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            });
        }
    }
    
    /**
     * Handle search input with debouncing
     */
    function handleSearchInput(value) {
        // Debounce search
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(value);
        }, CONFIG.debounceDelay);
    }
    
    /**
     * Perform the search
     */
    function performSearch(searchTerm) {
        currentSearchTerm = searchTerm.toLowerCase().trim();
        
        // Clear previous highlights
        clearHighlights();
        
        // Check minimum length
        if (currentSearchTerm.length < CONFIG.minSearchLength) {
            // Show all nav items
            allNavs.forEach(nav => {
                nav.style.display = '';
            });
            showResultsCount(0);
            return;
        }
        
        // First, hide all navs
        allNavs.forEach(nav => {
            nav.style.display = 'none';
        });
        
        let matchingNavIndices = new Set();
        
        // Search through map keys
        searchMap.forEach((refs, text) => {
            if (text.includes(currentSearchTerm)) {
                // Show navs for matching entries
                refs.navs.forEach(navIndex => {
                    matchingNavIndices.add(navIndex);
                    allNavs[navIndex].style.display = '';
                });
            }
        });
        
        // Show results count
        showResultsCount(matchingNavIndices.size);
        
        // If currently on a visible tab, highlight matches
        highlightMatchesInCurrentTab();
    }
    
    /**
     * Highlight matching items in the current tab
     */
    function highlightMatchesInCurrentTab() {
        if (!currentSearchTerm || currentSearchTerm.length < CONFIG.minSearchLength) return;
        
        // Find current tab
        const currentTab = document.querySelector('div.tab.current');
        if (!currentTab) return;
        
        clearHighlights();
        
        let firstMatch = null;
        
        // Search map for items in current tab
        searchMap.forEach((refs, text) => {
            if (text.includes(currentSearchTerm)) {
                refs.items.forEach(item => {
                    // Check if this item is in the current tab
                    if (currentTab.contains(item)) {
                        item.classList.add(CONFIG.highlightClass);
                        if (!firstMatch) {
                            firstMatch = item;
                        }
                    }
                });
            }
        });
        
        // Scroll first match into view
        if (firstMatch) {
            setTimeout(() => {
                firstMatch.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }, 150);
        }
    }
    
    /**
     * Clear highlights
     */
    function clearHighlights() {
        document.querySelectorAll('.' + CONFIG.highlightClass).forEach(el => {
            el.classList.remove(CONFIG.highlightClass);
        });
    }
    
    /**
     * Show search results count
     */
    function showResultsCount(count) {
        let countDiv = document.getElementById('searchBadge');
        
        if (currentSearchTerm) {
            countDiv.classList.remove('d-none');
            if (currentSearchTerm.length < CONFIG.minSearchLength) {
                countDiv.textContent = `+${CONFIG.minSearchLength - currentSearchTerm.length}`;
                countDiv.classList.remove('bg-success');
                countDiv.classList.add('bg-warning');
            } else {
                countDiv.textContent = count;
                countDiv.classList.remove('bg-warning');
                countDiv.classList.add((count==0 ? 'bg-warning' : 'bg-success'));
            }
        } else {
            countDiv.classList.add('d-none');
        }
    }
    
    // Initialize
    init();
    
})();