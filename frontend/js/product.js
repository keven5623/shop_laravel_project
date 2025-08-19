async function loadProducts() {
    if (!token) return;
    try {
        let url = `${API_BASE}/products`;
        if (selectedCategoryId) url += `?category_id=${selectedCategoryId}`;
        const res = await fetch(url, { headers: { Authorization: `Bearer ${token}` } });
        if (!res.ok) throw new Error();
        const products = await res.json();
        const container = document.getElementById('productList');
        container.innerHTML = '';

        products.forEach(p => {
            const div = document.createElement('div');
            div.className = 'col-12 col-md-6 col-lg-4';
            div.innerHTML = `
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${p.name}</h6>
                        <p class="card-text text-truncate">${p.description || ''}</p>
                        <p>價格：<span class="text-danger fw-bold">$${p.price}</span></p>
                        <button class="btn btn-primary mt-auto" ${p.stock===0?'disabled':''}>加入購物車</button>
                    </div>
                </div>`;
            container.appendChild(div);

            div.querySelector('button').addEventListener('click', () => addToCart(p.id,1));
        });
    } catch(err){ console.error(err); }
}
