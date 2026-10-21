<section class="auth-page">

  <div class="auth-card">

    <h1>التحقق من الرمز</h1>

    <form id="verifyCodeForm" method="post" onsubmit="verifyResetCode(event)">

      <div class="form-group">
        <label for="resetCode">رمز التحقق</label>
        <input type="text" id="resetCode" name="reset_code" placeholder="أدخل الرمز المكوّن من 6 أرقام" maxlength="6" required >
      </div>

      <p id="verifyCodeMessage" class="form-message"></p>

      <button type="submit" class="auth-button">  التحقق من الرمز </button>

    </form>

    <p class="auth-switch"> لم يصلك الرمز؟ <a href="{{APP_DIR}}auth/forgot-password">إعادة الإرسال</a> </p>

  </div>

</section>