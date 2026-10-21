const loaded = {};

function setUser(user) {
  app.user = user;
}

async function restoreSession(){
  if (app.user) return;
  try {
    var res = await api.get('/auth/me');
    if (res.ok) {
      app.user = res.data.user;
    }
  } catch (_) {}
}

async function loadScript(src) {
  if (loaded[src]) return;
  return new Promise(function(resolve) {
    var s = document.createElement('script');
    s.src = APP_CONFIG.APP_URL + src;
    s.onload = function() { loaded[src] = true; resolve(); };
    s.onerror = function() { resolve(); };
    document.body.appendChild(s);
  });
}

async function navigate() {
  await restoreSession();
  var pagePath = getPagePath();

  if (!app.user && pagePath.indexOf('auth/') !== 0) {
    goTo('auth/login');
    return;
  }
  if (app.user && pagePath.indexOf('auth/') === 0) {
    goTo('dashboard');
    return;
  }

  var parts = pagePath.split('/');
  var mod = parts[0];
  var rest = parts.slice(1);
  var page = rest.length ? rest[rest.length - 1] : 'index';
  var tpl   = 'tpl/' + mod + '/' + page + '.tpl';
  var pageJs = 'js/' + mod + '/' + page + '.js';

  var pageHtml = await engine.page(tpl);

  if (mod !== 'auth') {
    var layoutHtml = await engine.load('tpl/layout.tpl');

    $('#app').html(
      engine.render(layoutHtml, {
        content: pageHtml,
        userName: app.user ? app.user.name : ''
      })
    );

    $('.main-nav a').removeClass('active');

    $('.main-nav a[data-page="' + mod + '"]').addClass('active');
  } else {
  $('#app').html(pageHtml);
}

  await loadScript(pageJs);

  if (typeof app.pages[mod] === 'object') {
    var fn = app.pages[mod][page];
    if (typeof fn === 'function') fn();
  }
}

$(document).on('click', 'a', function (event) {
    const href = $(this).attr('href') || '';

    if (!href.startsWith(APP_CONFIG.APP_DIR) && !href.startsWith('?')) return;

    event.preventDefault();

    if (href.startsWith('?')) {
      const query = new URLSearchParams(href.slice(1));
      const params = Object.fromEntries(query.entries());

      const path = params._p || 'auth/login';
      delete params._p;

      goTo(path, params);
      return;
    }

    const url = new URL(href, window.location.origin);
    const path = url.pathname.split('/app/')[1] || 'auth/login';
    const params = Object.fromEntries(url.searchParams.entries());

    goTo(path, params);
});

window.addEventListener('popstate', navigate);
window.addEventListener('load', function() {
  navigate();
});