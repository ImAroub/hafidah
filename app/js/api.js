const api = {
  endpoint: APP_CONFIG.APP_URL.replace(/\/app\/$/, '/') + 'api/',

  url(path) {
    return this.endpoint + path.replace(/^\//, '');
  },

  async response(res) {
    const result = await res.json();

    if (res.status === 401) {
      if (window.app) window.app.user = null;

      if (typeof getPagePath === 'function' && getPagePath().indexOf('auth/') !== 0) {
        window.location.replace(APP_CONFIG.APP_DIR + 'auth/login');
      }
    }

    return result;
  },

  async get(path) {
    const res = await fetch(this.url(path), { credentials: 'same-origin' });
    return this.response(res);
  },

  async post(path, data = {}, cb = false) {
    const res = await fetch(this.url(path), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data), credentials: 'same-origin' });
    const ret = await this.response(res);

    if (cb) cb(ret);

    return ret;
  },

  async upload(path, formData) {
    const res = await fetch(this.url(path), { method: 'POST', body: formData, credentials: 'same-origin' });
    return this.response(res);
  },
};