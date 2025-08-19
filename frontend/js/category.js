let selectedCategoryId = 0; // 0 表示「全部」

async function loadCategories() {
    try {
        const res = await fetch(`${API_BASE}/categories`, {
            headers: token ? { Authorization: `Bearer ${token}` } : {}
        });
        if (!res.ok) throw new Error('載入分類失敗');
        const categories = await res.json();

        renderCategoryList(categories);
    } catch (err) {
        console.error(err);
        document.getElementById('categoryList').innerHTML = '<li class="nav-item">分類載入失敗</li>';
    }
}

function renderCategoryList(categories) {
    const ul = document.getElementById('categoryList');
    ul.innerHTML = '';

    // 加入「全部」分類
    const allLi = document.createElement('li');
    allLi.className = 'nav-item';
    allLi.innerHTML = `<a href="#" class="nav-link ${selectedCategoryId === 0 ? 'active' : ''}" data-id="0">全部</a>`;
    allLi.querySelector('a').addEventListener('click', (e) => {
        e.preventDefault();
        selectedCategoryId = 0;
        setActiveCategory();
        loadProducts(); // 載入所有商品
    });
    ul.appendChild(allLi);

    // 動態生成分類
    categories.forEach(cat => {
        const li = document.createElement('li');
        li.className = 'nav-item';
        li.innerHTML = `<a href="#" class="nav-link ${selectedCategoryId === cat.id ? 'active' : ''}" data-id="${cat.id}">${cat.name}</a>`;
        li.querySelector('a').addEventListener('click', (e) => {
            e.preventDefault();
            selectedCategoryId = cat.id;
            setActiveCategory();
            loadProducts(); // 載入對應分類商品
        });
        ul.appendChild(li);
    });
}

// 設定 active class
function setActiveCategory() {
    document.querySelectorAll('#categoryList .nav-link').forEach(link => {
        const id = parseInt(link.getAttribute('data-id'));
        if (id === selectedCategoryId) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}
