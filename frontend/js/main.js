window.addEventListener('user-logged-in', async () => {
    try {
        await loadCategories();
        await loadProducts();

        // 初始化購物車
        if (typeof renderCart === 'function') await renderCart();
        if (typeof updateCartCount === 'function') updateCartCount();
        if (typeof updateOrderCount === 'function') updateOrderCount();
    } catch (err) {
        console.error('登入後載入資料失敗', err);
    }
});

window.addEventListener('user-logged-out', () => {
    document.getElementById('productList').innerHTML = '';
    document.getElementById('categoryList').innerHTML = '';
    document.getElementById('cartList').innerHTML = '';
});
