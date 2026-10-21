async function login(event) {
  event.preventDefault();

  const emailValue = email.value;
  const passwordValue = password.value;

  let result = await api.post('auth/login', {
    email: emailValue,
    password: passwordValue
  });

  if (result.ok && result.data.requires_restore) {
    const restoreAccount = confirm(result.data.message);

    if (!restoreAccount) {
      loginMessage.textContent = 'لم يتم إلغاء طلب حذف الحساب';
      return;
    }

    result = await api.post('auth/login', {
      email: emailValue,
      password: passwordValue,
      restore_account: true
    });
  }

  loginMessage.textContent = result.ok ? result.data.message : result.error;

  if (result.ok) {
    loginForm.reset();
    setUser(result.data.user);
    location.href = APP_CONFIG.APP_DIR + 'dashboard';
  }
}
