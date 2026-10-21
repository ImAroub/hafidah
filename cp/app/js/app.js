window.app = window.app || {};
window.app.pages = window.app.pages || {};


function getQuery() {
  const params = new URLSearchParams(window.location.search);
  return Object.fromEntries(params.entries());
}

function goTo(path, params = {}) {
  const query = new URLSearchParams(params);
  let url = ADMIN_CONFIG.BASE_URL + path;

  if (query.toString()) {
    url += '?' + query.toString();
  }

  history.pushState(null, '', url);

  if (typeof navigate === 'function') {
    navigate();
  }
}

function getPagePath() {
  const path = window.location.pathname.split('/cp/')[1];
  return path || 'dashboard/index';
}

const engine = {
  async load(path) {
    const res = await fetch(ADMIN_CONFIG.ADMIN_URL + path + '?v=' + Date.now());
    return res.text();
  },

  render(tpl, data = {}) {
    return tpl.replace(/\{\{(\w+)\}\}/g, function(_, key) {
      return data[key] ?? ADMIN_CONFIG[key] ?? '';
    });
  },

  async page(path, data = {}) {
    return this.render(await this.load(path), data);
  }
};

function formatDate(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('ar-SA');
}

function formatMoney(value) {
  if (value === null || value === undefined || value === '') return 'غير محددة';
  return Number(value).toFixed(2) + ' ريال';
}

function toggleMobileMenu() {
  const topbar = document.querySelector('.topbar');

  if (topbar) {
    topbar.classList.toggle('menu-open');
  }
}

async function logout() {
  await api.post('auth/logout');
  app.user = null;
  window.location.replace(ADMIN_CONFIG.AUTH_LOGIN_URL);
}