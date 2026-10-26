/**
 * Practical 5: Frontend Form Validation & Password Strength Meter
 * StudentHub Portal - CHARUSAT CSPIT
 * Developer: Dhruvi Mokani (25CS047)
 */

const form = document.getElementById("registrationForm");

const nameInput = document.getElementById("name");
const emailInput = document.getElementById("email");
const mobileInput = document.getElementById("mobile");
const passwordInput = document.getElementById("password");
const confirmPasswordInput = document.getElementById("confirmPassword");
const courseInput = document.getElementById("course");
const yearInput = document.getElementById("year");
const termsInput = document.getElementById("terms");

const strengthBar = document.getElementById("strengthBar");
const strengthText = document.getElementById("strengthText");
const successMessage = document.getElementById("successMessage");

const nameRegex = /^[A-Za-z ]{3,50}$/;
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const mobileRegex = /^[6-9][0-9]{9}$/;

function showError(id, message) {
    const el = document.getElementById(id);
    if (el) el.textContent = message;
}

function clearError(id) {
    const el = document.getElementById(id);
    if (el) el.textContent = "";
}

function validateName() {
    if (!nameInput) return true;
    const val = nameInput.value.trim();
    if (val === "") {
        showError("nameError", "Full Name is required.");
        return false;
    }
    if (!nameRegex.test(val)) {
        showError("nameError", "Enter a valid name (3-50 letters only, no numbers/symbols).");
        return false;
    }
    clearError("nameError");
    return true;
}

function validateEmail() {
    if (!emailInput) return true;
    const val = emailInput.value.trim();
    if (val === "") {
        showError("emailError", "Email address is required.");
        return false;
    }
    if (!emailRegex.test(val)) {
        showError("emailError", "Enter a valid email address (e.g. student@charusat.edu.in).");
        return false;
    }
    clearError("emailError");
    return true;
}

function validateMobile() {
    if (!mobileInput) return true;
    const val = mobileInput.value.trim();
    if (val === "") {
        showError("mobileError", "Mobile number is required.");
        return false;
    }
    if (!mobileRegex.test(val)) {
        showError("mobileError", "Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.");
        return false;
    }
    clearError("mobileError");
    return true;
}

function checkPasswordStrength() {
    if (!passwordInput) return;
    const password = passwordInput.value;
    let strength = 0;

    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    if (!strengthBar || !strengthText) return;

    if (password.length === 0) {
        strengthBar.style.width = "0%";
        strengthBar.style.background = "#E1EDF2";
        strengthText.textContent = "Password strength: Not entered";
    } else if (strength <= 2) {
        strengthBar.style.width = "33%";
        strengthBar.style.background = "#E02424";
        strengthText.textContent = "Password strength: Weak (min 8 chars, mixed case, number & symbol)";
    } else if (strength <= 4) {
        strengthBar.style.width = "66%";
        strengthBar.style.background = "#E3A008";
        strengthText.textContent = "Password strength: Medium (good, consider adding a symbol)";
    } else {
        strengthBar.style.width = "100%";
        strengthBar.style.background = "#0E9F6E";
        strengthText.textContent = "Password strength: Strong (secure)";
    }
}

function validatePassword() {
    if (!passwordInput) return true;
    const val = passwordInput.value;
    if (val === "") {
        showError("passwordError", "Password is required.");
        return false;
    }

    const hasLower = /[a-z]/.test(val);
    const hasUpper = /[A-Z]/.test(val);
    const hasDigit = /[0-9]/.test(val);
    const hasSpecial = /[^A-Za-z0-9]/.test(val);
    const hasLength = val.length >= 8;

    if (!hasLength || !hasLower || !hasUpper || !hasDigit || !hasSpecial) {
        showError(
            "passwordError",
            "Password must contain 8+ characters, uppercase, lowercase, number and special character."
        );
        return false;
    }

    clearError("passwordError");
    return true;
}

function validateConfirmPassword() {
    if (!confirmPasswordInput || !passwordInput) return true;
    if (confirmPasswordInput.value === "") {
        showError("confirmPasswordError", "Please confirm your password.");
        return false;
    }
    if (confirmPasswordInput.value !== passwordInput.value) {
        showError("confirmPasswordError", "Passwords do not match.");
        return false;
    }
    clearError("confirmPasswordError");
    return true;
}

function validateCourse() {
    if (!courseInput) return true;
    if (courseInput.value === "") {
        showError("courseError", "Please select your academic course.");
        return false;
    }
    clearError("courseError");
    return true;
}

function validateYear() {
    if (!yearInput) return true;
    if (yearInput.value === "") {
        showError("yearError", "Please select your study year.");
        return false;
    }
    clearError("yearError");
    return true;
}

function validateGender() {
    const gender = document.querySelector('input[name="gender"]:checked');
    if (!gender) {
        showError("genderError", "Please select your gender.");
        return false;
    }
    clearError("genderError");
    return true;
}

function validateTerms() {
    if (!termsInput) return true;
    if (!termsInput.checked) {
        showError("termsError", "You must accept the Terms and Conditions.");
        return false;
    }
    clearError("termsError");
    return true;
}

// Live validation events
if (nameInput) nameInput.addEventListener("input", validateName);
if (emailInput) emailInput.addEventListener("input", validateEmail);
if (mobileInput) mobileInput.addEventListener("input", validateMobile);

if (passwordInput) {
    passwordInput.addEventListener("input", function () {
        checkPasswordStrength();
        validatePassword();
    });
}

if (confirmPasswordInput) confirmPasswordInput.addEventListener("input", validateConfirmPassword);
if (courseInput) courseInput.addEventListener("change", validateCourse);
if (yearInput) yearInput.addEventListener("change", validateYear);

document.querySelectorAll('input[name="gender"]').forEach(function (radio) {
    radio.addEventListener("change", validateGender);
});

if (termsInput) termsInput.addEventListener("change", validateTerms);

// Form submission handling
if (form) {
    form.addEventListener("submit", function (event) {
        const v1 = validateName();
        const v2 = validateEmail();
        const v3 = validateMobile();
        const v4 = validatePassword();
        const v5 = validateConfirmPassword();
        const v6 = validateCourse();
        const v7 = validateYear();
        const v8 = validateGender();
        const v9 = validateTerms();

        const isValid = v1 && v2 && v3 && v4 && v5 && v6 && v7 && v8 && v9;

        if (!isValid) {
            event.preventDefault();
            // Focus the first invalid field
            if (!v1 && nameInput) nameInput.focus();
            else if (!v2 && emailInput) emailInput.focus();
            else if (!v3 && mobileInput) mobileInput.focus();
            else if (!v4 && passwordInput) passwordInput.focus();
            else if (!v5 && confirmPasswordInput) confirmPasswordInput.focus();
            else if (!v6 && courseInput) courseInput.focus();
            else if (!v7 && yearInput) yearInput.focus();
            else if (!v9 && termsInput) termsInput.focus();
            return false;
        }

        // If form has method="POST" (registration.php), allow natural browser POST submission
        const isServerPost = form.getAttribute("method") && form.getAttribute("method").toUpperCase() === "POST";
        if (isServerPost) {
            if (successMessage) {
                successMessage.textContent = "Validating details and submitting registration...";
            }
            return true;
        }

        // Fallback for static demo (registration.html)
        event.preventDefault();
        if (successMessage) {
            successMessage.textContent = "Registration successful! Redirecting to login...";
        }
        form.reset();
        if (strengthBar) strengthBar.style.width = "0%";
        if (strengthText) strengthText.textContent = "Password strength: Not entered";

        setTimeout(function () {
            window.location.href = "login.php";
        }, 1200);
    });
}
