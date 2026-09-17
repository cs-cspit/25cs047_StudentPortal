
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
const passwordRegex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;


function showError(id, message) {
    document.getElementById(id).textContent = message;
}

function clearError(id) {
    document.getElementById(id).textContent = "";
}

function validateName() {
    if (nameInput.value.trim() === "") {
        showError("nameError", "Name is required.");
        return false;
    }

    if (!nameRegex.test(nameInput.value.trim())) {
        showError("nameError", "Enter a valid name using letters only.");
        return false;
    }

    clearError("nameError");
    return true;
}

function validateEmail() {
    if (emailInput.value.trim() === "") {
        showError("emailError", "Email is required.");
        return false;
    }

    if (!emailRegex.test(emailInput.value.trim())) {
        showError("emailError", "Enter a valid email address.");
        return false;
    }

    clearError("emailError");
    return true;
}

function validateMobile() {
    if (mobileInput.value.trim() === "") {
        showError("mobileError", "Mobile number is required.");
        return false;
    }

    if (!mobileRegex.test(mobileInput.value.trim())) {
        showError(
            "mobileError",
            "Enter a valid 10-digit mobile number starting with 6-9."
        );
        return false;
    }

    clearError("mobileError");
    return true;
}

function checkPasswordStrength() {

    const password = passwordInput.value;

    let strength = 0;

    if (password.length >= 8) {
        strength++;
    }

    if (/[a-z]/.test(password)) {
        strength++;
    }

    if (/[A-Z]/.test(password)) {
        strength++;
    }

    if (/[0-9]/.test(password)) {
        strength++;
    }

    if (/[@$!%*?&]/.test(password)) {
        strength++;
    }

    if (password.length === 0) {
        strengthBar.style.width = "0%";
        strengthText.textContent = "Password strength: Not entered";
    }
    else if (strength <= 2) {
        strengthBar.style.width = "33%";
        strengthText.textContent = "Password strength: Weak";
    }
    else if (strength <= 4) {
        strengthBar.style.width = "66%";
        strengthText.textContent = "Password strength: Medium";
    }
    else {
        strengthBar.style.width = "100%";
        strengthText.textContent = "Password strength: Strong";
    }
}

function validatePassword() {

    if (passwordInput.value === "") {
        showError("passwordError", "Password is required.");
        return false;
    }

    if (!passwordRegex.test(passwordInput.value)) {
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

    if (courseInput.value === "") {
        showError("courseError", "Please select a course.");
        return false;
    }

    clearError("courseError");
    return true;
}

function validateYear() {

    if (yearInput.value === "") {
        showError("yearError", "Please select your year.");
        return false;
    }

    clearError("yearError");
    return true;
}

function validateGender() {

    const gender = document.querySelector(
        'input[name="gender"]:checked'
    );

    if (!gender) {
        showError("genderError", "Please select your gender.");
        return false;
    }

    clearError("genderError");
    return true;
}

function validateTerms() {

    if (!termsInput.checked) {
        showError(
            "termsError",
            "You must accept the Terms and Conditions."
        );
        return false;
    }

    clearError("termsError");
    return true;
}

nameInput.addEventListener("keyup", validateName);
emailInput.addEventListener("keyup", validateEmail);
mobileInput.addEventListener("keyup", validateMobile);

passwordInput.addEventListener("keyup", function () {
    checkPasswordStrength();
    validatePassword();
});

confirmPasswordInput.addEventListener("keyup", validateConfirmPassword);

courseInput.addEventListener("change", validateCourse);
yearInput.addEventListener("change", validateYear);

document.querySelectorAll('input[name="gender"]').forEach(function (radio) {
    radio.addEventListener("change", validateGender);
});

termsInput.addEventListener("change", validateTerms);

form.addEventListener("submit", function (event) {

    event.preventDefault();

    const isValid =
        validateName() &&
        validateEmail() &&
        validateMobile() &&
        validatePassword() &&
        validateConfirmPassword() &&
        validateCourse() &&
        validateYear() &&
        validateGender() &&
        validateTerms();

   if(isValid){
successMessage.textContent="Registration successful! Redirecting to login...";

form.reset();

strengthBar.style.width="0%";
strengthText.textContent="Password strength: Not entered";

setTimeout(function(){
window.location.href="login.html";
},1500);
}
});