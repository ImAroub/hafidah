async function verifyResetCode(event) {
  event.preventDefault();

  const email = sessionStorage.getItem('reset_email');
  const resetCode = event.target.reset_code.value;

  const result = await api.post('auth/verify-reset-code', {
    email: email,
    reset_code: resetCode
  });

  if (!result.ok) {
    verifyCodeMessage.textContent = result.error;
    return;
  }

  sessionStorage.setItem('reset_code', resetCode);

  location.href = APP_CONFIG.APP_DIR + 'auth/reset-password';
}