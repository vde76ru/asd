import "../css/main.css";
import "../css/shop.css";

// Импортируем существующие модули
import { changeItemsPerPage, changePage, handlePageInputKeydown } from './pagination.js';
import { filterByBrandOrSeries, applyFilters, clearAllFilters } from './filters.js';
import { loadAvailability } from './availability.js';
import { addToCart, clearCart, removeFromCart, fetchCart } from './cart.js';
import { showToast } from './utils.js';
import { renderProductsTable, copyText } from './renderProducts.js';
import { createSpecification } from './specification.js';
import { productService } from './services/ProductService.js';

// ВАЖНО: Импортируем новый менеджер вместо старого
import { productsManager } from './ProductsManager.js';

// Экспорт функций в window для обратной совместимости
window.renderProductsTable = renderProductsTable;
window.copyText = copyText;
window.createSpecification = createSpecification;
window.loadAvailability = loadAvailability;
window.addToCart = addToCart;
window.clearCart = clearCart;
window.removeFromCart = removeFromCart;
window.fetchCart = fetchCart;
window.filterByBrandOrSeries = filterByBrandOrSeries;
window.applyFilters = applyFilters;
window.clearAllFilters = clearAllFilters;

// Поиск с автодополнением
class SearchManager {
    constructor() {
        this.searchInput = null;
        this.globalSearchInput = null;
        this.autocompleteContainer = null;
        this.searchTimeout = null;
        this.selectedIndex = -1;
        this.isSearching = false;
    }

    init() {
        this.searchInput = document.getElementById('searchInput');
        if (this.searchInput) {
            this.setupSearch(this.searchInput);
        }

        this.globalSearchInput = document.getElementById('globalSearch');
        if (this.globalSearchInput) {
            this.setupGlobalSearch(this.globalSearchInput);
        }
    }

    setupSearch(input) {
        // Автодополнение обрабатывается через productsManager
        // Здесь только UI для автокомплита
    }

    setupGlobalSearch(input) {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && input.value.trim()) {
                window.location.href = `/shop?search=${encodeURIComponent(input.value.trim())}`;
            }
        });
    }
}

const searchManager = new SearchManager();

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация поиска
    searchManager.init();
    
    // Город
    const citySelect = document.getElementById('citySelect');
    if (citySelect) {
        citySelect.value = localStorage.getItem('selected_city_id') || '1';
        citySelect.addEventListener('change', () => {
            localStorage.setItem('selected_city_id', citySelect.value);
            // Перезагружаем товары при смене города
            if (window.productsData && window.productsData.length > 0) {
                productsManager.fetchProducts();
            }
        });
    }
    
    // Количество товаров на странице
    ['itemsPerPageSelect', 'itemsPerPageSelectBottom'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.value = window.itemsPerPage;
            el.addEventListener('change', changeItemsPerPage);
        }
    });
    
    // Ввод номера страницы
    ['pageInput', 'pageInputBottom'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', changePage);
            el.addEventListener('keydown', handlePageInputKeydown);
        }
    });
    
    // Обработчики кликов
    document.body.addEventListener('click', handleBodyClick);
    
    // Кнопки пагинации
    document.querySelectorAll('.prev-btn').forEach(btn => {
        btn.addEventListener('click', evt => {
            evt.preventDefault();
            productsManager.loadPage(Math.max(1, window.currentPage - 1));
        });
    });
    
    document.querySelectorAll('.next-btn').forEach(btn => {
        btn.addEventListener('click', evt => {
            evt.preventDefault();
            const totalPages = Math.ceil(window.totalProducts / window.itemsPerPage);
            productsManager.loadPage(Math.min(totalPages, window.currentPage + 1));
        });
    });
    
    // Загрузка товаров если мы на странице каталога
    if (document.querySelector('.product-table')) {
        productsManager.loadPage(window.currentPage || 1);
    }
    
    // Загрузка корзины
    if (document.querySelector('.cart-container') || document.getElementById('cartBadge')) {
        fetchCart().catch(console.error);
    }
});

// Обработчик кликов по body
function handleBodyClick(e) {
    const target = e.target;
    
    // Добавить в корзину
    if (target.closest('.add-to-cart-btn')) {
        const btn = target.closest('.add-to-cart-btn');
        const productId = btn.dataset.productId;
        const quantityInput = btn.closest('tr')?.querySelector('.quantity-input');
        const quantity = parseInt(quantityInput?.value || '1', 10);
        addToCart(productId, quantity);
        return;
    }
    
    // Удалить из корзины
    if (target.closest('.remove-from-cart-btn')) {
        const btn = target.closest('.remove-from-cart-btn');
        removeFromCart(btn.dataset.productId);
        return;
    }
    
    // Очистить корзину
    if (target.matches('#clearCartBtn')) {
        if (confirm('Очистить корзину?')) {
            clearCart();
        }
        return;
    }
    
    // Создать спецификацию
    if (target.closest('.create-specification-btn, #createSpecLink')) {
        e.preventDefault();
        createSpecification();
        return;
    }
    
    // Сортировка
    const sortableHeader = target.closest('th.sortable');
    if (sortableHeader && sortableHeader.dataset.column) {
        productsManager.sortProducts(sortableHeader.dataset.column);
        return;
    }
    
    // Фильтры по бренду/серии
    if (target.closest('.brand-name, .series-name')) {
        const element = target.closest('.brand-name, .series-name');
        const filterType = element.classList.contains('brand-name') ? 'brand_name' : 'series_name';
        const value = element.textContent.trim();
        filterByBrandOrSeries(filterType, value);
        return;
    }
}