/**
 * Sign In Form Validation
 * Handles client-side validation for the login form
 */

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("signinForm");
  if (!form) return;

  const emailInput = document.getElementById("emailInput");
  const passwordInput = document.getElementById("passwordInput");
  const rememberMe = document.getElementById("rememberMe");

  // Form submission
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    if (validateForm()) {
      form.submit();
    }
  });

  /**
   * Validate entire form
   */
  function validateForm() {
    let isValid = true;

    // Validate email
    if (!emailInput.value.trim()) {
      showError(emailInput, "Vui lòng nhập email");
      isValid = false;
    } else {
      clearError(emailInput);
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

    return isValid;
  }

  /**
   * Show error message for an input field
   */
  function showError(input, message) {
    const inputGroup = input.closest(".input-group");
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
    const inputGroup = input.closest(".input-group");
    if (!inputGroup) return;

    inputGroup.classList.remove("has-error");
    const errorMsg = inputGroup.querySelector(".error-message");
    if (errorMsg) {
      errorMsg.remove();
    }
  }
});
