<section class="profile-page">
  <div class="profile-card">

    <div class="page-heading">
      <h1>الملف الشخصي</h1>
      <a href="{{APP_DIR}}dashboard">العودة للرئيسية</a>
    </div>

    <form id="profileForm" class="profile-form" method="post" onsubmit="updateProfile(event)">

      <div class="form-group">
        <label for="profileName">الاسم</label>
        <input type="text" id="profileName" name="name" required>
      </div>

      <div class="form-group">
        <label for="profileEmail">البريد الإلكتروني</label>
        <input type="email" id="profileEmail" name="email" required>
      </div>

      <div class="form-group">
        <label for="profilePassword">كلمة المرور الجديدة</label>
        <input type="password" id="profilePassword" name="password" placeholder="اتركها فارغة إذا لا تريد تغييرها">
      </div>

      <div class="form-group">
        <label for="profilePasswordConfirmation">تأكيد كلمة المرور الجديدة</label>
        <input type="password" id="profilePasswordConfirmation" name="password_confirmation" placeholder="أعد كتابة كلمة المرور الجديدة">
      </div>

      <p id="profileMessage" class="form-message"></p>

      <div class="profile-actions">
        <button type="submit" class="profile-save-button">حفظ التعديلات</button>
        <button type="button" class="profile-logout-button" onclick="logout()">تسجيل الخروج</button>
        <button type="button" class="profile-delete-button" onclick="deleteAccount()">حذف الحساب</button>
      </div>

    </form>

  </div>
</section>