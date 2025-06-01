<?php
/**
 * @var array $product - Основные данные товара
 * @var array $images - Изображения
 * @var array $documents - Документы
 * @var array $attributes - Атрибуты
 * @var float|null $price - Финальная цена
 * @var float|null $basePrice - Базовая цена
 * @var bool $hasSpecialPrice - Есть ли спецпредложение
 * @var int $stock - Количество на складе
 * @var array $deliveryInfo - Информация о доставке
 * @var array $related - Связанные товары
 * @var int $cityId - ID текущего города
 * @var array $productDynamic - Полные динамические данные
 */
?>
<link rel="stylesheet" href="/assets/css/product-card.css">

<div class="breadcrumbs">
    <a href="/">Главная</a> <span>/</span>
    <a href="/shop">Каталог</a> <span>/</span>
    <span><?= htmlspecialchars($product['name']) ?></span>
</div>

<div class="product-card" data-product-id="<?= (int)$product['product_id'] ?>">
    <!-- ГАЛЕРЕЯ -->
    <div class="product-card__gallery">
        <div class="gallery__main-image">
            <img id="mainProductImage" 
                 src="<?= htmlspecialchars($images[0]['url'] ?? '/images/placeholder.jpg') ?>"
                 alt="<?= htmlspecialchars($images[0]['alt_text'] ?? $product['name']) ?>"
                 loading="lazy">
        </div>
        
        <?php if (count($images) > 1): ?>
            <div class="gallery__thumbnails">
                <?php foreach ($images as $img): ?>
                    <img class="gallery__thumbnail" 
                         src="<?= htmlspecialchars($img['url']) ?>"
                         alt="<?= htmlspecialchars($img['alt_text'] ?? $product['name']) ?>"
                         onclick="document.getElementById('mainProductImage').src=this.src"
                         loading="lazy">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($documents): ?>
            <div class="gallery__docs">
                <?php foreach ($documents as $doc): ?>
                    <?php if ($doc['type'] === 'certificate'): ?>
                        <a href="<?= htmlspecialchars($doc['url']) ?>" 
                           target="_blank" 
                           title="Сертификат <?= htmlspecialchars($doc['title'] ?? '') ?>" 
                           class="gallery__doc-icon">
                            <img src="/assets/img/certificate-icon.svg" alt="Сертификат">
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ОСНОВНОЙ БЛОК СВЕДЕНИЙ -->
    <div class="product-card__main">
        <h1 class="product-card__title"><?= htmlspecialchars($product['name']) ?></h1>
        
        <div class="product-card__meta">
            <div class="meta__line">
                <span class="meta__label">Артикул:</span>
                <span class="meta__value"><?= htmlspecialchars($product['external_id']) ?></span>
            </div>
            
            <?php if (!empty($product['sku'])): ?>
                <div class="meta__line">
                    <span class="meta__label">SKU:</span>
                    <span class="meta__value"><?= htmlspecialchars($product['sku']) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($product['brand_name'])): ?>
                <div class="meta__line">
                    <span class="meta__label">Бренд:</span>
                    <span class="meta__value">
                        <a href="/shop?brand_name=<?= urlencode($product['brand_name']) ?>">
                            <?= htmlspecialchars($product['brand_name']) ?>
                        </a>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($product['series_name'])): ?>
                <div class="meta__line">
                    <span class="meta__label">Серия:</span>
                    <span class="meta__value">
                        <a href="/shop?series_name=<?= urlencode($product['series_name']) ?>">
                            <?= htmlspecialchars($product['series_name']) ?>
                        </a>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-card__shortdesc">
            <?= nl2br(htmlspecialchars($product['short_description'] ?? $product['description'] ?? '')) ?>
        </div>
    </div>

    <!-- БЛОК ПОКУПКИ -->
    <div class="product-card__buy" id="dynamicBuyBlock">
        <!-- Цена -->
        <div class="product-card__price-row">
            <span class="product-card__price-label">Цена:</span>
            <div class="product-card__price-container">
                <?php if ($price !== null): ?>
                    <?php if ($hasSpecialPrice && $basePrice): ?>
                        <span class="product-card__price-old"><?= number_format($basePrice, 2, ',', ' ') ?> ₽</span>
                    <?php endif; ?>
                    <span class="product-card__price-value" data-price="<?= $price ?>">
                        <?= number_format($price, 2, ',', ' ') ?> ₽
                    </span>
                    <?php if ($hasSpecialPrice): ?>
                        <span class="product-card__price-badge">
                            -<?= round((1 - $price / $basePrice) * 100) ?>%
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="product-card__price-value">По запросу</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Наличие -->
        <div class="product-card__stock-row">
            <span class="product-card__stock-label">В наличии:</span>
            <span class="product-card__stock-value <?= $stock > 0 ? 'in-stock' : 'out-of-stock' ?>" 
                  data-stock="<?= $stock ?>">
                <?= $stock > 0 ? $stock . ' ' . htmlspecialchars($product['unit'] ?? 'шт') : 'Нет в наличии' ?>
            </span>
        </div>
        
        <!-- Доставка -->
        <div class="product-card__delivery-row">
            <span class="product-card__delivery-label">Доставка:</span>
            <span class="product-card__delivery-value">
                <?= htmlspecialchars($deliveryInfo['text'] ?? 'Уточняйте') ?>
                <?php if (!empty($deliveryInfo['date'])): ?>
                    <small>(<?= htmlspecialchars($deliveryInfo['date']) ?>)</small>
                <?php endif; ?>
            </span>
        </div>
        
        <!-- Форма добавления в корзину -->
        <form class="product-card__cart-form" id="addToCartForm">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::token() ?>">
            
            <div class="quantity-group">
                <label>Количество:</label>
                <div class="quantity-controls">
                    <button type="button" class="quantity-btn minus" onclick="changeQuantity(-1)">−</button>
                    <input type="number" 
                           name="quantity" 
                           id="quantity" 
                           min="<?= $product['min_sale'] ?: 1 ?>" 
                           step="<?= $product['min_sale'] ?: 1 ?>"
                           value="<?= $product['min_sale'] ?: 1 ?>" 
                           class="quantity-input">
                    <button type="button" class="quantity-btn plus" onclick="changeQuantity(1)">+</button>
                </div>
                <span class="quantity-unit"><?= htmlspecialchars($product['unit'] ?? 'шт') ?></span>
            </div>
            
            <button class="btn btn-primary btn-add-to-cart" 
                    type="submit" 
                    <?= $stock <= 0 ? 'disabled' : '' ?>>
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                В корзину
            </button>
        </form>
        
        <!-- Быстрый заказ -->
        <button class="btn btn-secondary product-card__quickbuy-btn" onclick="showQuickOrderModal()">
            Купить в 1 клик
        </button>
        
        <!-- Минимальная партия -->
        <?php if (!empty($product['min_sale']) && $product['min_sale'] > 1): ?>
            <div class="product-card__min-sale">
                <span>Минимальная партия: <?= (int)$product['min_sale'] ?> <?= htmlspecialchars($product['unit'] ?? 'шт.') ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Склады с наличием -->
        <?php if (!empty($availableWarehouses)): ?>
            <div class="product-card__warehouses">
                <h4>Доступно на складах:</h4>
                <ul>
                    <?php foreach ($availableWarehouses as $warehouse): ?>
                        <li><?= htmlspecialchars($warehouse['name']) ?>: <?= $warehouse['quantity'] ?> шт</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- ХАРАКТЕРИСТИКИ -->
    <div class="product-card__features">
        <h2>Технические характеристики</h2>
        <table class="features-table">
            <?php if (!empty($product['weight'])): ?>
                <tr>
                    <td>Вес</td>
                    <td><?= htmlspecialchars($product['weight']) ?> кг</td>
                </tr>
            <?php endif; ?>
            
            <?php if (!empty($product['dimensions'])): ?>
                <tr>
                    <td>Размеры</td>
                    <td><?= htmlspecialchars($product['dimensions']) ?></td>
                </tr>
            <?php endif; ?>
            
            <?php if ($attributes): ?>
                <?php foreach ($attributes as $attr): ?>
                    <tr>
                        <td><?= htmlspecialchars($attr['name']) ?></td>
                        <td>
                            <?= htmlspecialchars($attr['value']) ?>
                            <?= !empty($attr['unit']) ? ' ' . htmlspecialchars($attr['unit']) : '' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

    <!-- ОПИСАНИЕ -->
    <div class="product-card__description">
        <h2>Описание</h2>
        <div class="description-content">
            <?= nl2br(htmlspecialchars($product['description'] ?? 'Описание отсутствует')) ?>
        </div>
        
        <?php if ($documents): ?>
            <?php foreach ($documents as $doc): ?>
                <?php if ($doc['type'] === 'manual'): ?>
                    <div class="product-card__manual">
                        <a href="<?= htmlspecialchars($doc['url']) ?>" target="_blank">
                            📄 <?= htmlspecialchars($doc['title'] ?? 'Инструкция') ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ДОКУМЕНТЫ И СЕРТИФИКАТЫ -->
    <div class="product-card__documents">
        <h2>Документы и сертификаты</h2>
        <?php if ($documents): ?>
            <div class="documents-grid">
                <?php foreach ($documents as $doc): ?>
                    <a href="<?= htmlspecialchars($doc['url']) ?>" 
                       target="_blank" 
                       class="document-item">
                        <div class="document-icon">
                            <?php if ($doc['type'] === 'certificate'): ?>
                                🏆
                            <?php elseif ($doc['type'] === 'manual'): ?>
                                📖
                            <?php else: ?>
                                📄
                            <?php endif; ?>
                        </div>
                        <div class="document-title">
                            <?= htmlspecialchars($doc['title'] ?? ucfirst($doc['type'])) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-block">Документы будут добавлены в ближайшее время</div>
        <?php endif; ?>
    </div>

    <!-- РЕКОМЕНДАЦИИ -->
    <?php if ($related): ?>
        <div class="product-card__related">
            <h2>С этим товаром покупают</h2>
            <div class="related-products-grid">
                <?php foreach ($related as $rel): ?>
                    <div class="related-product-card">
                        <a href="/shop/product?id=<?= urlencode($rel['external_id']) ?>">
                            <img src="<?= htmlspecialchars($rel['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($rel['name']) ?>"
                                 loading="lazy">
                            <h4><?= htmlspecialchars($rel['name']) ?></h4>
                            <?php if ($rel['base_price']): ?>
                                <div class="related-price"><?= number_format($rel['base_price'], 2, ',', ' ') ?> ₽</div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Скрипты для динамической загрузки -->
<script>
// Глобальные переменные
window.productData = {
    id: <?= $product['product_id'] ?>,
    minSale: <?= $product['min_sale'] ?: 1 ?>,
    unit: '<?= addslashes($product['unit'] ?? 'шт') ?>',
    currentCity: <?= $cityId ?>,
    dynamicData: <?= json_encode($productDynamic, JSON_UNESCAPED_UNICODE) ?>
};

// Изменение количества
function changeQuantity(delta) {
    const input = document.getElementById('quantity');
    const minSale = window.productData.minSale;
    let newValue = parseInt(input.value) + (delta * minSale);
    
    if (newValue < minSale) newValue = minSale;
    input.value = newValue;
}

// Добавление в корзину
document.getElementById('addToCartForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    try {
        button.disabled = true;
        button.innerHTML = '<span class="spinner"></span> Добавление...';
        
        const response = await fetch('/cart/add', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Показываем уведомление
            showNotification('Товар добавлен в корзину', 'success');
            
            // Обновляем счетчик корзины
            updateCartBadge();
            
            // Анимация кнопки
            button.innerHTML = '✓ Добавлено';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        } else {
            throw new Error(result.message || 'Ошибка добавления');
        }
    } catch (error) {
        showNotification(error.message, 'error');
        button.innerHTML = originalText;
    } finally {
        button.disabled = false;
    }
});

// Обновление данных при смене города
document.addEventListener('cityChanged', async function(e) {
    const cityId = e.detail.cityId;
    await updateProductDynamicData(cityId);
});

// Функция обновления динамических данных
async function updateProductDynamicData(cityId) {
    try {
        const response = await fetch(`/api/product/${window.productData.id}/info?city_id=${cityId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            updateDynamicUI(result.data);
            window.productData.dynamicData = result.data;
            window.productData.currentCity = cityId;
        }
    } catch (error) {
        console.error('Failed to update product data:', error);
    }
}

// Обновление UI с новыми данными
function updateDynamicUI(data) {
    // Цена
    const priceContainer = document.querySelector('.product-card__price-container');
    if (data.price && data.price.final !== null) {
        let priceHtml = '';
        
        if (data.price.has_special && data.price.base) {
            priceHtml += `<span class="product-card__price-old">${formatPrice(data.price.base)} ₽</span> `;
        }
        
        priceHtml += `<span class="product-card__price-value" data-price="${data.price.final}">
                        ${formatPrice(data.price.final)} ₽
                      </span>`;
        
        if (data.price.has_special && data.price.discount_percent) {
            priceHtml += ` <span class="product-card__price-badge">-${data.price.discount_percent}%</span>`;
        }
        
        priceContainer.innerHTML = priceHtml;
    } else {
        priceContainer.innerHTML = '<span class="product-card__price-value">По запросу</span>';
    }
    
    // Наличие
    const stockElement = document.querySelector('.product-card__stock-value');
    const stock = data.stock.quantity || 0;
    stockElement.textContent = stock > 0 
        ? `${stock} ${window.productData.unit}` 
        : 'Нет в наличии';
    stockElement.className = `product-card__stock-value ${stock > 0 ? 'in-stock' : 'out-of-stock'}`;
    stockElement.dataset.stock = stock;
    
    // Доставка
    const deliveryElement = document.querySelector('.product-card__delivery-value');
    let deliveryHtml = data.delivery.text || 'Уточняйте';
    if (data.delivery.date) {
        deliveryHtml += ` <small>(${data.delivery.date})</small>`;
    }
    deliveryElement.innerHTML = deliveryHtml;
    
    // Кнопка добавления в корзину
    const addButton = document.querySelector('.btn-add-to-cart');
    addButton.disabled = stock <= 0;
    
    // Склады
    updateWarehousesInfo(data.stock.warehouses || []);
}

// Обновление информации о складах
function updateWarehousesInfo(warehouses) {
    const container = document.querySelector('.product-card__warehouses');
    
    if (warehouses.length > 0) {
        let html = '<h4>Доступно на складах:</h4><ul>';
        warehouses.forEach(wh => {
            html += `<li>${escapeHtml(wh.name)}: ${wh.quantity} шт</li>`;
        });
        html += '</ul>';
        
        if (container) {
            container.innerHTML = html;
            container.style.display = 'block';
        }
    } else if (container) {
        container.style.display = 'none';
    }
}

// Быстрый заказ
function showQuickOrderModal() {
    // TODO: Реализовать модальное окно быстрого заказа
    showNotification('Функция в разработке', 'info');
}

// Вспомогательные функции
function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    // Используем существующую функцию showToast если она есть
    if (typeof showToast === 'function') {
        showToast(message, type === 'error');
    } else {
        alert(message);
    }
}

async function updateCartBadge() {
    try {
        const response = await fetch('/cart/json');
        const data = await response.json();
        const badge = document.getElementById('cartBadge');
        if (badge && data.cart) {
            const count = Object.keys(data.cart).length;
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        }
    } catch (error) {
        console.error('Failed to update cart badge:', error);
    }
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {
    // Слушаем изменения города из селектора в хедере
    const citySelect = document.getElementById('citySelect');
    if (citySelect) {
        citySelect.addEventListener('change', function() {
            const event = new CustomEvent('cityChanged', {
                detail: { cityId: parseInt(this.value) }
            });
            document.dispatchEvent(event);
        });
    }
});
</script>