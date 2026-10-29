// Varenne SPA
// 1. On first launch every view in views/ is fetched ONCE and injected into <main id="app">.
// 2. Navigation only toggles which <section> is visible, based on location.hash (#home, #shop, ...).
//    No page reloads and no repeated fetches.

const VIEWS = ["home", "shop", "product", "cart", "login", "register", "profile", "dashboard", "about", "contact"];
const DEFAULT_VIEW = "home";
const SHOP_STATE = { category: "All", sort: "Newest" };

const cart = DEMO_CART.map(item => ({ ...item }));
const euro = n => "€" + n.toLocaleString("en-US");

// ---------- View loading (runs once) ----------
async function loadViews() {
  const app = document.getElementById("app");
  const html = await Promise.all(
    VIEWS.map(name =>
      fetch(`views/${name}.html`).then(res => {
        if (!res.ok) throw new Error(`Could not load view "${name}" (${res.status})`);
        return res.text();
      })
    )
  );

  VIEWS.forEach((name, i) => {
    const section = document.createElement("section");
    section.id = name;
    section.className = "spa-view";
    section.hidden = true;
    section.innerHTML = html[i];
    app.appendChild(section);
  });
  document.getElementById("loader").remove();
}

// ---------- Router ----------
// Hash format: #view or #view/param (e.g. #product/3)
function route() {
  const [name, param] = location.hash.replace("#", "").split("/");
  const view = VIEWS.includes(name) ? name : DEFAULT_VIEW;

  document.querySelectorAll(".spa-view").forEach(s => (s.hidden = s.id !== view));
  document.querySelectorAll("[data-view]").forEach(a => a.classList.toggle("active", a.dataset.view === view));

  if (view === "product") renderProduct(Number(param) || 1);
  if (view === "cart") renderCart();

  // Collapse the mobile menu after navigating
  const nav = document.getElementById("mainNav");
  if (nav.classList.contains("show")) bootstrap.Collapse.getOrCreateInstance(nav).hide();

  document.title = `${view.charAt(0).toUpperCase() + view.slice(1)} | Varenne`;
  window.scrollTo({ top: 0 });
}

// ---------- Rendering helpers ----------
function productCard(p) {
  return `
    <div class="col-6 col-md-4 col-lg-3">
      <a class="product-card" href="#product/${p.id}">
        <div class="product-img" style="--tone-a:${p.toneA};--tone-b:${p.toneB}">
          ${p.tag ? `<span class="tag">${p.tag}</span>` : ""}
          <i class="bi ${p.icon}"></i>
        </div>
        <div class="name">${p.name}</div>
        <div class="price">${euro(p.price)}</div>
      </a>
    </div>`;
}

function renderGrids() {
  const featured = document.getElementById("featuredGrid");
  if (featured) featured.innerHTML = PRODUCTS.slice(0, 4).map(productCard).join("");

  const cats = document.getElementById("categoryGrid");
  if (cats) cats.innerHTML = CATEGORIES.map(c => `
    <div class="col-md-4">
      <a href="#shop" class="product-card" data-filter="${c.name}">
        <div class="product-img category-tile" style="--tone-a:${c.toneA};--tone-b:${c.toneB}">
          <i class="bi ${c.icon}"></i><span class="label">${c.name}</span>
        </div>
      </a>
    </div>`).join("");

  renderShop("All");
}

function renderShop(category = SHOP_STATE.category, sort = SHOP_STATE.sort) {
  const grid = document.getElementById("shopGrid");
  if (!grid) return;

  SHOP_STATE.category = category;
  SHOP_STATE.sort = sort;

  let list = category === "All" ? [...PRODUCTS] : PRODUCTS.filter(p => p.category === category);

  if (sort === "Price: low to high") {
    list.sort((a, b) => a.price - b.price);
  } else if (sort === "Price: high to low") {
    list.sort((a, b) => b.price - a.price);
  }

  grid.innerHTML = list.map(productCard).join("");
  document.getElementById("shopCount").textContent = `${list.length} pieces`;
  document.querySelectorAll("#shopFilters .btn").forEach(b =>
    b.classList.toggle("active", b.dataset.category === category));

  const sortSelect = document.getElementById("shopSort");
  if (sortSelect) sortSelect.value = sort;
}

function renderProduct(id) {
  const p = PRODUCTS.find(x => x.id === id) || PRODUCTS[0];
  const root = document.getElementById("product");
  root.querySelector("[data-p=img]").style.cssText = `--tone-a:${p.toneA};--tone-b:${p.toneB}`;
  root.querySelector("[data-p=icon]").className = `bi ${p.icon}`;
  root.querySelector("[data-p=category]").textContent = p.category;
  root.querySelector("[data-p=name]").textContent = p.name;
  root.querySelector("[data-p=price]").textContent = euro(p.price);
  root.querySelector("[data-p=description]").textContent = p.description;
  root.querySelector("[data-p=colors]").innerHTML =
    p.colors.map(c => `<span class="swatch" style="background:${c}"></span>`).join("");
  root.querySelector("[data-p=sizes]").innerHTML = p.sizes.map((s, i) => `
    <div class="size-option">
      <input type="radio" name="size" id="size-${i}" value="${s}" ${i === 0 ? "checked" : ""}>
      <label for="size-${i}">${s}</label>
    </div>`).join("");
  root.querySelector("[data-p=add]").dataset.id = p.id;
  root.querySelector("[data-p=related]").innerHTML =
    PRODUCTS.filter(x => x.category === p.category && x.id !== p.id).slice(0, 4).map(productCard).join("");
}

function renderCart() {
  const list = document.getElementById("cartItems");
  if (!list) return;
  if (!cart.length) {
    list.innerHTML = `<p class="text-center text-muted py-5">Your bag is empty. <a href="#shop">Discover the collection</a>.</p>`;
  } else {
    list.innerHTML = cart.map((item, i) => {
      const p = PRODUCTS.find(x => x.id === item.productId);
      return `
        <div class="d-flex gap-3 py-4 border-bottom">
          <div class="product-img flex-shrink-0" style="width:96px;--tone-a:${p.toneA};--tone-b:${p.toneB}"><i class="bi ${p.icon} fs-4"></i></div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
              <a href="#product/${p.id}" class="display-serif fs-5 text-decoration-none">${p.name}</a>
              <span>${euro(p.price * item.qty)}</span>
            </div>
            <div class="small text-muted mb-2">Size: ${item.size}</div>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-outline-dark btn-sm" data-cart="dec" data-index="${i}" aria-label="Decrease">−</button>
              <span>${item.qty}</span>
              <button class="btn btn-outline-dark btn-sm" data-cart="inc" data-index="${i}" aria-label="Increase">+</button>
              <button class="btn btn-link btn-sm text-muted ms-auto" data-cart="remove" data-index="${i}">Remove</button>
            </div>
          </div>
        </div>`;
    }).join("");
  }
  const subtotal = cart.reduce((sum, item) => sum + PRODUCTS.find(p => p.id === item.productId).price * item.qty, 0);
  const shipping = subtotal === 0 || subtotal >= 500 ? 0 : 25;
  document.getElementById("cartSubtotal").textContent = euro(subtotal);
  document.getElementById("cartShipping").textContent = shipping ? euro(shipping) : "Complimentary";
  document.getElementById("cartTotal").textContent = euro(subtotal + shipping);
  updateCartCount();
}

function renderDashboard() {
  const orders = document.getElementById("dashOrders");
  if (orders) orders.innerHTML = DEMO_ORDERS.map(o => {
    const color = { Shipped: "text-bg-dark", Processing: "text-bg-warning", Delivered: "text-bg-success", Cancelled: "text-bg-secondary" }[o.status];
    return `<tr><td>${o.id}</td><td>${o.customer}</td><td>${o.date}</td><td>${euro(o.total)}</td>
            <td><span class="badge badge-status ${color}">${o.status}</span></td></tr>`;
  }).join("");

  const products = document.getElementById("dashProducts");
  if (products) products.innerHTML = PRODUCTS.map(p => `
    <tr><td>${p.name}</td><td>${p.category}</td><td>${euro(p.price)}</td><td>${p.sizes.length * 6}</td>
      <td class="text-end">
        <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#productModal"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-outline-dark btn-sm js-demo-action"><i class="bi bi-trash"></i></button>
      </td></tr>`).join("");
}

function updateCartCount() {
  document.getElementById("cartCount").textContent = cart.reduce((n, i) => n + i.qty, 0);
}

function toast(message) {
  document.getElementById("appToastBody").textContent = message;
  bootstrap.Toast.getOrCreateInstance(document.getElementById("appToast")).show();
}

// ---------- Event delegation (works for content injected after load) ----------
function bindEvents() {
  document.addEventListener("click", e => {
    const filter = e.target.closest("[data-category]");
    if (filter) return renderShop(filter.dataset.category, SHOP_STATE.sort);

    const catTile = e.target.closest("[data-filter]");
    if (catTile) renderShop(catTile.dataset.filter, SHOP_STATE.sort);

    const add = e.target.closest("[data-p=add]");
    if (add) {
      const size = document.querySelector("#product input[name=size]:checked").value;
      const id = Number(add.dataset.id);
      const existing = cart.find(i => i.productId === id && i.size === size);
      existing ? existing.qty++ : cart.push({ productId: id, size, qty: 1 });
      updateCartCount();
      return toast("Added to your bag.");
    }

    const cartBtn = e.target.closest("[data-cart]");
    if (cartBtn) {
      const item = cart[cartBtn.dataset.index];
      if (cartBtn.dataset.cart === "inc") item.qty++;
      if (cartBtn.dataset.cart === "dec") item.qty > 1 ? item.qty-- : cart.splice(cartBtn.dataset.index, 1);
      if (cartBtn.dataset.cart === "remove") cart.splice(cartBtn.dataset.index, 1);
      return renderCart();
    }

    if (e.target.closest(".js-demo-action")) toast("This action will work once the backend is connected (Milestone 2).");
  });

  document.addEventListener("change", e => {
    if (e.target.matches("#shopSort")) {
      renderShop(SHOP_STATE.category, e.target.value);
    }
  });

  // All forms are static for now: validate with Bootstrap styles, then show a notice.
  document.addEventListener("submit", e => {
    const form = e.target;
    e.preventDefault();
    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      return;
    }
    form.classList.remove("was-validated");
    form.reset();
    toast(form.dataset.success || "Thank you. This form will be connected to the backend in Milestone 2.");
  });

  window.addEventListener("hashchange", route);
}

// ---------- Boot ----------
(async function init() {
  try {
    await loadViews();
    renderGrids();
    renderDashboard();
    updateCartCount();
    bindEvents();
    route();
  } catch (err) {
    document.getElementById("app").innerHTML = `
      <div class="container py-5 text-center">
        <h2>Views could not be loaded</h2>
        <p class="text-muted">${err.message}. Open the site through a web server (e.g. XAMPP: http://localhost/.../frontend/), not as a file:// path.</p>
      </div>`;
    console.error(err);
  }
})();
