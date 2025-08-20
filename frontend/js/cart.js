// =====================
// 全域變數
// =====================
let cartModal = null;
let orderSuccessModal = null;
let orderModal = null;

// =====================
// DOM 元素初始化
// =====================
document.addEventListener('DOMContentLoaded', () => {
    cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
    orderSuccessModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
    orderModal = new bootstrap.Modal(document.getElementById('orderModal'));

    document.getElementById('openCartBtn')?.addEventListener('click', async () => {
        if (!window.token) { alert('請先登入'); return; }
        await renderCart();
        cartModal.show();
    });

    document.getElementById('checkoutBtn')?.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        await checkout();
    });

    document.getElementById('openOrdersBtn')?.addEventListener('click', async () => {
        await renderOrders();
        orderModal.show();
    });
});

// =====================
// 使用者登入後初始化 Echo 監聽
// =====================
window.addEventListener('user-logged-in', async () => {
    window.myId = window.authUserId;

    if (window.myId) {
        window.Echo.private(`orders.${window.myId}`)
            .listen('.OrderProcessed', e => {
                console.log('OrderProcessed event', e);

                // 顯示訂單完成訊息
                const html = `<p>你的訂單 <strong>${e.order_id}</strong> 已完成！</p>`;
                document.getElementById('orderSuccessContent').innerHTML = html;
                orderSuccessModal.show();

                // 更新 badge
                updateCartCount();
                updateOrderCount();

                // 延遲更新訂單列表，避免覆蓋 modal
                orderSuccessModal._element.addEventListener('hidden.bs.modal', () => {
                    renderOrders();
                }, { once: true });
            });
    }
});

// =====================
// 取得購物車
// =====================
async function getCart() {
    if (!window.token) return [];
    try {
        const res = await fetch(`${API_BASE}/cart`, { headers: { Authorization: `Bearer ${window.token}` } });
        if (!res.ok) return [];
        const data = await res.json();
        return data.items || [];
    } catch { return []; }
}

// =====================
// 渲染購物車
// =====================
async function renderCart() {
    const cart = await getCart();
    const container = document.getElementById('cartList');
    container.innerHTML = '';
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (!cart.length) { 
        checkoutBtn.disabled = true; 
        container.innerHTML = '<p>購物車是空的</p>'; 
        return; 
    }
    checkoutBtn.disabled = false;

    const table = document.createElement('table');
    table.className = 'table table-sm';
    table.innerHTML = `<thead><tr><th>商品</th><th>價格</th><th>數量</th><th>小計</th><th></th></tr></thead>`;
    const tbody = document.createElement('tbody');

    cart.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${item.product.name}</td>
                        <td>$${item.product.price}</td>
                        <td>${item.quantity}</td>
                        <td>$${(item.product.price*item.quantity).toFixed(2)}</td>
                        <td><button class="btn btn-sm btn-danger">移除</button></td>`;
        tr.querySelector('button')?.addEventListener('click', async () => {
            await removeFromCart(item.id);
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);

    updateCartCount();
}

// =====================
// 加入購物車
// =====================
async function addToCart(productId, quantity = 1) {
    if (!window.token) { alert('請先登入'); return; }
    try {
        const res = await fetch(`${API_BASE}/cart/add`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'Authorization': `Bearer ${window.token}` 
            },
            body: JSON.stringify({ product_id: productId, quantity })
        });
        if (!res.ok) {
            const data = await res.json();
            return alert(data.message || '加入購物車失敗');
        }

        alert('商品已加入購物車！');
        if (cartModal?._isShown) await renderCart();
        await updateCartCount();
    } catch (err) {
        console.error(err);
        alert('加入購物車失敗，請稍後再試');
    }
}

// =====================
// 移除購物車
// =====================
async function removeFromCart(itemId) {
    if (!window.token) { alert('請先登入'); return; }
    try {
        const res = await fetch(`${API_BASE}/cart/remove`, {
            method: 'POST',
            headers: { 
                'Content-Type':'application/json',
                'Authorization': `Bearer ${window.token}` 
            },
            body: JSON.stringify({ item_id:itemId })
        });
        if (!res.ok) return alert('移除失敗');
        if (cartModal?._isShown) await renderCart();
        await updateCartCount();
    } catch { alert('移除失敗'); }
}

// =====================
// 更新購物車 badge
// =====================
async function updateCartCount() {
    const cart = await getCart();
    const cartCountEl = document.getElementById('cartCount');
    if (cartCountEl) cartCountEl.textContent = cart.length;
}

// =====================
// 結帳
// =====================
async function checkout() {
    const cart = await getCart();
    if (!cart.length) { alert('購物車是空的'); return; }

    try {
        const res = await fetch(`${API_BASE}/orders`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${window.token}` 
            },
            body: JSON.stringify({ items: cart.map(i => ({ product_id: i.product.id, quantity: i.quantity })) })
        });

        if (!res.ok) {
            const errData = await res.json();
            return alert(`結帳失敗: ${errData.message || '未知錯誤'}`);
        }

        const data = await res.json();

        // 顯示訂單完成 modal
        let html = `<p>訂單編號: <strong>${data.order_id}</strong></p>`;
        html += `<table class="table table-sm"><thead><tr><th>商品</th><th>數量</th><th>小計</th></tr></thead><tbody>`;
        let total = 0;
        data.items.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            html += `<tr>
                        <td>${item.product.name}</td>
                        <td>${item.quantity}</td>
                        <td>$${formatPrice(subtotal)}</td>
                     </tr>`;
        });
        html += `</tbody></table>`;
        html += `<p class="fw-bold">總金額: $${formatPrice(total)}</p>`;
        document.getElementById('orderSuccessContent').innerHTML = html;
        orderSuccessModal.show();

        // ✅ 只更新 badge，不重繪購物車 DOM
        await updateCartCount();
        await updateOrderCount();

    } catch (err) {
        console.error(err);
        alert('結帳錯誤');
    }
}

// =====================
// 格式化價格
// =====================
function formatPrice(num) {
    return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// =====================
// 訂單相關
// =====================
async function getOrders() {
    if (!window.token) return [];
    try {
        const res = await fetch(`${API_BASE}/orders`, { headers: { 'Authorization': `Bearer ${window.token}` } });
        if (!res.ok) return [];
        const data = await res.json();
        return data.orders || data; // 支援 array 或 { orders: [...] }
    } catch { return []; }
}

async function updateOrderCount() {
    const orders = await getOrders();
    const orderCountEl = document.getElementById('orderCount');
    if (orderCountEl) orderCountEl.textContent = orders.length;
}

async function renderOrders() {
    const orders = await getOrders();
    const container = document.getElementById('orderListContent');
    if (!orders.length) { container.innerHTML = '<p>沒有訂單紀錄</p>'; return; }

    let html = '';
    orders.forEach(order => {
        html += `<div class="mb-3 p-2 border rounded">
                    <h6>訂單編號: ${order.id} / 狀態: ${order.status}</h6>
                    <ul>`;
        order.items.forEach(item => {
            html += `<li>${item.product.name} x ${item.quantity} = $${item.price * item.quantity}</li>`;
        });
        html += `</ul><p>總金額: $${order.total}</p></div>`;
    });
    container.innerHTML = html;
}
