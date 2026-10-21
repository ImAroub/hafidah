<section class="auth-page">

  <div class="auth-card">

    <h1>تعيين كلمة مرور جديدة</h1>

    <form id="resetPasswordForm"  method="post" onsubmit="resetPassword(event)">

      <div class="form-group">
        <label for="password">كلمة المرور الجديدة</label>
        <input type="password" id="password" name="password" placeholder="8 أحرف على الأقل" minlength="8" required >
      </div>

      <div class="form-group">
        <label for="passwordConfirmation">تأكيد كلمة المرور</label>
        <input type="password" id="passwordConfirmation" name="password_confirmation" placeholder="أعد كتابة كلمة المرور" minlength="8" required >
      </div>

      <p id="resetPasswordMessage" class="form-message"></p>

      <button type="submit" class="auth-button"> حفظ كلمة المرور</button>

    </form>

    <p class="auth-switch"> العودة إلى <a href="?_p=auth/login">تسجيل الدخول</a> </p>

  </div>

</section>