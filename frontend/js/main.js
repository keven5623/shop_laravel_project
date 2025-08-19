// 等待使用者登入後才載入資料
window.addEventListener('user-logged-in', async () => {
    try {
        await loadCategories();
        await loadProducts();
    } catch (err) {
        console.error('登入後載入資料失敗', err);
    }
});

window.addEventListener('user-logged-out', () => {
    document.getElementById('productList').innerHTML = '';
    document.getElementById('categoryList').innerHTML = '';
    document.getElementById('cartList').innerHTML = '';
});
