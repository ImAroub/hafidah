async function register(event) {
  event.preventDefault();

  const form = event.target;

  const result = await api.post('auth/register', {
    name: form.name.value,
    email: form.email.value,
    password: form.password.value,
    password_confirmation: form.password_confirmation.value
  });

  if (result.ok) {
    location.href = APP_CONFIG.APP_DIR + 'auth/login';
    return;
  }

  document.getElementById('registerMessage').textContent = result.error;
}