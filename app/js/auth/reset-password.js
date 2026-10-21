async function resetPassword(event) {
  event.preventDefault();

  const result = await api.post('auth/reset-password', {
    email: sessionStorage.getItem('reset_email'),
    reset_code: sessionStorage.getItem('reset_code'),
    password: event.target.password.value,
    password_confirmation: event.target.password_confirmation.value
  });

  if (!result.ok) {
    resetPasswordMessage.textContent = result.error;
    return;
  }

  sessionStorage.removeItem('reset_email');
  sessionStorage.removeItem('reset_code');

  location.href = APP_CONFIG.APP_DIR + 'auth/login';
}