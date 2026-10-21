async function forgotPassword(event) {
  event.preventDefault();

  const email = event.target.email.value.trim();

  const result = await api.post('auth/forgot-password', { email });

  if (!result.ok) {
    forgotPasswordMessage.textContent = result.error;
    return;
  }

  sessionStorage.setItem('reset_email', email);

  alert('رمز التحقق: ' + result.data.reset_code);

  location.href = APP_CONFIG.APP_DIR + 'auth/verify-reset-code';
}