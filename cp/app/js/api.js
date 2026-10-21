const ADMIN_CONFIG = {
  ADMIN_URL: '/hafidah/cp/app/',
  APP_NAME: 'حافظة',
  BASE_URL: '/hafidah/cp/',
  API_URL: '/hafidah/cp/api/',
  UPLOADS_URL: '/hafidah/api/uploads/',
  AUTH_LOGIN_URL: '/hafidah/cp/auth/login',
  APP_LOGIN_URL: '/hafidah/app/auth/login'
};

const api = {
  endpoint: ADMIN_CONFIG.API_URL,

  url(path) {
    return this.endpoint + path.replace(/^\//, '');
  },

  async response(res, redirectOn401 = true) {
    const result = await res.json();

    if (res.status === 401) {
      if (window.app) window.app.user = null;

      if (redirectOn401 && typeof getPagePath === 'function' && getPagePath().indexOf('auth/') !== 0) {
        window.location.replace(ADMIN_CONFIG.AUTH_LOGIN_URL);
      }
    }

    return result;
  },

  async get(path, redirectOn401 = true) {
    const res = await fetch(this.url(path), { credentials: 'same-origin' });
    return this.response(res, redirectOn401);
  },

  async post(path, data = {}, cb = false) {
    const res = await fetch(this.url(path), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
      credentials: 'same-origin'
    });

    const ret = await this.response(res);

    if (cb) cb(ret);

    return ret;
  },

  async upload(path, formData) {
    const res = await fetch(this.url(path), {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });

    return this.response(res);
  }
};
