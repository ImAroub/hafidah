window.app = window.app || {};
window.app.pages = window.app.pages || {};

const APP_CONFIG = {
  APP_DIR: '/hafidah/app/',
  APP_URL: '/hafidah/app/',
  APP_NAME: 'hafidah',
};
function getQuery() {
  const params = new URLSearchParams(window.location.search);
  return Object.fromEntries(params.entries());
}

function goTo(path, params = {}) {
  const query = new URLSearchParams({
    ...params,
    _p: path
  });

  history.pushState(null, '', '?' + query.toString());

  if (typeof navigate === 'function') {
    navigate();
  }
}

function getPagePath() {
  const q = getQuery();
  const path = location.pathname.split('/app/', 2)[1];
  return q._p || path || 'auth/login';
}

const engine = {
  cache: {},

  async load(path) {
    const res = await fetch(APP_CONFIG.APP_URL + path + '?v=' + Date.now());
    const html = await res.text();
    return html;
  },

  render(tpl, data = {}) {
    return tpl.replace(/\{\{(\w+)\}\}/g, (_, key) => data[key] ?? APP_CONFIG[key] ?? '');
  },

  async page(tplPath, data = {}) {
    const html = await this.load(tplPath);
    return this.render(html, data);
  },
};

function formatDate(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('ar-SA');
}

async function logout() {
  const result = await api.post('auth/logout');

  if (!result.ok) {
    alert(result.error || 'تعذر تسجيل الخروج');
    return;
  }

  app.user = null;

  window.location.replace(APP_CONFIG.APP_DIR + 'auth/login');
}

function toggleMobileMenu() {
  const topbar = document.querySelector('.topbar');

  if (topbar) {
    topbar.classList.toggle('menu-open');
  }
}

window.createInvoiceCard = function (invoice) {
  const card = document.createElement('a');

  card.className = 'dashboard-card is-clickable';
  card.href ='?_p=invoices/show&id=' + encodeURIComponent(invoice.id);

  const header = document.createElement('div');
  header.className = 'dashboard-card__header';

  const title = document.createElement('h3');
  title.textContent = invoice.item_name || 'منتج بدون اسم';

  const badges = document.createElement('div');
  badges.className = 'card-badges';

  const statusNames = {
    active: 'نشطة',
    maintenance: 'صيانة',
    returned: 'مسترجعة',
    replaced: 'مستبدلة',
    cancelled: 'ملغاة'
  };

  const status = document.createElement('span');

  status.className ='card-badge card-badge--' +(invoice.item_status || 'active');

  status.textContent = statusNames[invoice.item_status] || 'نشطة';

  badges.appendChild(status);

  if (Number(invoice.is_expiring_soon) === 1) {
    const expiring = document.createElement('span');

    expiring.className = 'card-badge card-badge--expiring'; 
    expiring.textContent = 'قارب الانتهاء';

    badges.appendChild(expiring);
  }

  header.appendChild(title);
  header.appendChild(badges);

  const body = document.createElement('div');
  body.className = 'dashboard-card__body';

  const category = document.createElement('p');
  category.textContent = 'التصنيف: ' + (invoice.category_name || 'غير محدد');

  const store = document.createElement('p');
  store.textContent = 'مكان الشراء: ' + (invoice.store_name || 'غير محدد');

  const purchaseDate = document.createElement('p');
  purchaseDate.textContent = 'تاريخ الشراء: ' +formatDate(invoice.purchase_date);

  body.appendChild(category);
  body.appendChild(store);
  body.appendChild(purchaseDate);

  card.appendChild(header);
  card.appendChild(body);

  return card;
};