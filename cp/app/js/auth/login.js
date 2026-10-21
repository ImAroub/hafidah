app.pages.auth = app.pages.auth || {};

app.pages.auth.login = function() {
  const form = document.getElementById('adminLoginForm');
  const message = document.getElementById('adminLoginMessage');

  form.addEventListener('submit', async function(event) {
    event.preventDefault();

    message.textContent = 'جاري تسجيل الدخول...';

    const email = document.getElementById('adminEmail').value.trim();
    const password = document.getElementById('adminPassword').value;

    const result = await api.post('auth/login', {
      email: email,
      password: password
    });

    if (!result.ok) {
      message.textContent = result.error || 'تعذر تسجيل الدخول';
      return;
    }

    if (!result.data.user || result.data.user.role !== 'admin') {
      message.textContent = 'هذا الحساب ليس حساب أدمن';
      return;
    }

    app.user = result.data.user;

    goTo('dashboard');
  });
};