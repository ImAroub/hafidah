window.app.pages.profile = window.app.pages.profile || {};

window.app.pages.profile.index = function() {
  if (!app.user) return;

  profileName.value = app.user.name || '';
  profileEmail.value = app.user.email || '';
};

async function updateProfile(event) {
  event.preventDefault();

  const result = await api.post('auth/update-profile', {
    name: profileName.value,
    email: profileEmail.value,
    password: profilePassword.value,
    password_confirmation: profilePasswordConfirmation.value
  });

  if (!result.ok) {
    profileMessage.textContent = result.error || 'تعذر حفظ التعديلات';
    return;
  }

  setUser(result.data.user);

  const userName = document.getElementById('userName');

  if (userName) {
    userName.textContent = result.data.user.name;
  }

  profilePassword.value = '';
  profilePasswordConfirmation.value = '';
  profileMessage.textContent = result.data.message || 'تم حفظ التعديلات بنجاح';
}

async function deleteAccount() {
  const confirmed = confirm('هل أنت متأكد من طلب حذف الحساب؟ سيتم تسجيل خروجك الآن والاحتفاظ ببياناتك لمدة 30 يوم. يمكنك استعادة الحساب خلال هذه المدة، وبعدها سيتم حذف جميع البيانات نهائيًا.');

  if (!confirmed) return;

  const result = await api.post('auth/delete-account');

  if (!result.ok) {
    profileMessage.textContent = result.error || 'تعذر طلب حذف الحساب';
    return;
  }

  app.user = null;
  alert(result.data.message);
  window.location.replace(APP_CONFIG.APP_DIR + 'auth/login');
}