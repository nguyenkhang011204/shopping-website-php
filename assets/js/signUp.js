/**
 * Sign Up Form Validation
 * Handles client-side validation for the registration form
 */

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("signupForm");
  if (!form) return;

  const passwordInput = document.getElementById("password");
  const passwordConfirmInput = document.getElementById("password_confirm");
  const emailInput = form.querySelector('input[name="email"]');
  const fullNameInput = form.querySelector('input[name="full_name"]');
  const phoneInput = form.querySelector('input[name="phone"]');

  // Real-time password match validation
  passwordConfirmInput.addEventListener("change", function () {
    validatePasswordMatch();
  });

  passwordInput.addEventListener("change", function () {
    validatePasswordMatch();
  });

  // Form submission
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    if (validateForm()) {
      form.submit();
    }
  });

  /**
   * Validate that passwords match
   */
  function validatePasswordMatch() {
    if (passwordInput.value && passwordConfirmInput.value) {
      if (passwordInput.value !== passwordConfirmInput.value) {
        passwordConfirmInput.classList.add("error");
        showError(passwordConfirmInput, "Mật khẩu không khớp");
        return false;
      } else {
        passwordConfirmInput.classList.remove("error");
        clearError(passwordConfirmInput);
        return true;
      }
    }
    return true;
  }

  /**
   * Validate entire form
   */
  function validateForm() {
    let isValid = true;

    // Validate full name
    if (!fullNameInput.value.trim()) {
      showError(fullNameInput, "Vui lòng nhập họ và tên");
      isValid = false;
    } else if (fullNameInput.value.length < 3) {
      showError(fullNameInput, "Họ và tên phải có ít nhất 3 ký tự");
      isValid = false;
    } else {
      clearError(fullNameInput);
    }

    // Validate email
    if (!emailInput.value.trim()) {
      showError(emailInput, "Vui lòng nhập email");
      isValid = false;
    } else if (!isValidEmail(emailInput.value)) {
      showError(emailInput, "Email không hợp lệ");
      isValid = false;
    } else {
      clearError(emailInput);
    }

    // Validate phone (optional but if provided, must be valid)
    if (phoneInput.value) {
      if (!/^[0-9]{10,20}$/.test(phoneInput.value)) {
        showError(phoneInput, "Số điện thoại phải từ 10-20 chữ số");
        isValid = false;
      } else {
        clearError(phoneInput);
      }
    }

    // Validate password
    if (!passwordInput.value) {
      showError(passwordInput, "Vui lòng nhập mật khẩu");
      isValid = false;
    } else if (passwordInput.value.length < 6) {
      showError(passwordInput, "Mật khẩu phải có ít nhất 6 ký tự");
      isValid = false;
    } else {
      clearError(passwordInput);
    }

    // Validate password confirmation
    if (!passwordConfirmInput.value) {
      showError(passwordConfirmInput, "Vui lòng xác nhận mật khẩu");
      isValid = false;
    } else if (!validatePasswordMatch()) {
      isValid = false;
    }

    // Validate terms checkbox
    const termsCheckbox = form.querySelector('input[name="terms"]');
    if (!termsCheckbox.checked) {
      showError(termsCheckbox, "Vui lòng đồng ý với điều khoản dịch vụ");
      isValid = false;
    }

    return isValid;
  }

  /**
   * Check if email format is valid
   */
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  /**
   * Show error message for an input field
   */
  function showError(input, message) {
    const inputGroup =
      input.closest(".input-group") || input.closest(".checkbox-group");
    if (!inputGroup) return;

    // Remove existing error if any
    const existingError = inputGroup.querySelector(".error-message");
    if (existingError) {
      existingError.remove();
    }

    // Add error class
    inputGroup.classList.add("has-error");

    // Create and append error message
    const errorMsg = document.createElement("span");
    errorMsg.className = "error-message";
    errorMsg.textContent = message;
    inputGroup.appendChild(errorMsg);
  }

  /**
   * Clear error message from an input field
   */
  function clearError(input) {
    const inputGroup =
      input.closest(".input-group") || input.closest(".checkbox-group");
    if (!inputGroup) return;

    inputGroup.classList.remove("has-error");
    const errorMsg = inputGroup.querySelector(".error-message");
    if (errorMsg) {
      errorMsg.remove();
    }
  }
});
