// Валидация формы регистрации
function validateRegistrationForm() {
    let isValid = true;
    
    // Очистка предыдущих ошибок
    clearErrors('registrationForm');
    
    const name = document.getElementById('registration_name').value.trim();
    const email = document.getElementById('registration_email').value.trim();
    const phone = document.getElementById('registration_phone').value.trim();
    const password = document.getElementById('registration_password').value;
    const confirmPassword = document.getElementById('registration_confirm_password').value;
    
    // Валидация имени
    if (name.length < 2) {
        showError('reg_name_group', 'reg_name_error', 'Имя должно содержать минимум 2 символа');
        isValid = false;
    }
    
    // Валидация email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('reg_email_group', 'reg_email_error', 'Введите корректный email');
        isValid = false;
    }
    
    // Валидация телефона
    const phoneRegex = /^\+?\d{10,15}$/;
    if (!phoneRegex.test(phone)) {
        showError('reg_phone_group', 'reg_phone_error', 'Введите корректный номер телефона (минимум 10 цифр)');
        isValid = false;
    }
    
    // Валидация пароля
    if (password.length < 6) {
        showError('reg_password_group', 'reg_password_error', 'Пароль должен содержать минимум 6 символов');
        isValid = false;
    }
    
    // Проверка совпадения паролей
    if (password !== confirmPassword) {
        showError('reg_confirm_group', 'reg_confirm_error', 'Пароли должны совпадать');
        isValid = false;
    }
    
    return isValid;
}

// Валидация формы обратной связи
function validateContactForm() {
    let isValid = true;
    
    // Очистка предыдущих ошибок
    clearErrors('contactForm');
    
    const email = document.getElementById('contact_email').value.trim();
    const message = document.getElementById('contact_message').value.trim();
    
    // Валидация email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('contact_email_group', 'contact_email_error', 'Введите корректный email');
        isValid = false;
    }
    
    // Валидация сообщения
    if (message.length < 10) {
        showError('contact_message_group', 'contact_message_error', 'Сообщение должно содержать минимум 10 символов');
        isValid = false;
    }
    
    return isValid;
}

// Показать ошибку
function showError(groupId, errorId, message) {
    const group = document.getElementById(groupId);
    const error = document.getElementById(errorId);
    
    group.classList.add('error');
    error.textContent = message;
}

// Очистить все ошибки
function clearErrors(formId) {
    const form = document.getElementById(formId);
    const groups = form.querySelectorAll('.form-group');
    
    groups.forEach(group => {
        group.classList.remove('error');
    });
    
    const errors = form.querySelectorAll('.error');
    errors.forEach(error => {
        error.textContent = '';
    });
}

// Показать уведомление
function showAlert(containerId, type, message) {
    const container = document.getElementById(containerId);
    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    alert.textContent = message;
    
    container.innerHTML = '';
    container.appendChild(alert);
    
    // Автоматически скрыть через 5 секунд
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Добавить данные регистрации в список
function addRegistrationData(user) {
    const display = document.getElementById('registrationData');
    const list = document.getElementById('registrationDataList');
    
    display.style.display = 'block';
    
    const item = document.createElement('div');
    item.className = 'data-item';
    item.innerHTML = `
        <strong>${escapeHtml(user.name)}</strong>
        <p>${escapeHtml(user.email)}</p>
        <p>${escapeHtml(user.phone)}</p>
    `;
    
    list.prepend(item);
}

// Добавить данные сообщения в список
function addContactData(message) {
    const display = document.getElementById('contactData');
    const list = document.getElementById('contactDataList');
    
    display.style.display = 'block';
    
    const item = document.createElement('div');
    item.className = 'data-item';
    item.innerHTML = `
        <strong>${escapeHtml(message.displayName)}</strong>
        <p>${escapeHtml(message.message)}</p>
    `;
    
    list.prepend(item);
}

// Экранирование HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Обработка отправки формы регистрации
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateRegistrationForm()) {
        return;
    }
    
    const formData = {
        name: document.getElementById('registration_name').value.trim(),
        email: document.getElementById('registration_email').value.trim(),
        phone: document.getElementById('registration_phone').value.trim(),
        password: document.getElementById('registration_password').value,
        confirmPassword: document.getElementById('registration_confirm_password').value
    };
    
    fetch('/api/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('registrationAlerts', 'success', 'Регистрация успешна!');
            addRegistrationData(data.user);
            document.getElementById('registrationForm').reset();
        } else {
            showAlert('registrationAlerts', 'error', 'Ошибка валидации на сервере');
            // Показываем ошибки с сервера
            for (const [field, message] of Object.entries(data.errors)) {
                let groupId, errorId;
                if (field === 'name') {
                    groupId = 'reg_name_group';
                    errorId = 'reg_name_error';
                } else if (field === 'email') {
                    groupId = 'reg_email_group';
                    errorId = 'reg_email_error';
                } else if (field === 'phone') {
                    groupId = 'reg_phone_group';
                    errorId = 'reg_phone_error';
                } else if (field === 'password') {
                    groupId = 'reg_password_group';
                    errorId = 'reg_password_error';
                } else if (field === 'confirmPassword') {
                    groupId = 'reg_confirm_group';
                    errorId = 'reg_confirm_error';
                }
                if (groupId && errorId) {
                    showError(groupId, errorId, message);
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('registrationAlerts', 'error', 'Произошла ошибка при отправке');
    });
});

// Обработка отправки формы обратной связи
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateContactForm()) {
        return;
    }
    
    const formData = {
        email: document.getElementById('contact_email').value.trim(),
        message: document.getElementById('contact_message').value.trim()
    };
    
    fetch('/api/contact', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('contactAlerts', 'success', 'Сообщение отправлено!');
            addContactData(data.message);
            document.getElementById('contactForm').reset();
        } else {
            showAlert('contactAlerts', 'error', 'Ошибка валидации на сервере');
            // Показываем ошибки с сервера
            for (const [field, message] of Object.entries(data.errors)) {
                let groupId, errorId;
                if (field === 'email') {
                    groupId = 'contact_email_group';
                    errorId = 'contact_email_error';
                } else if (field === 'message') {
                    groupId = 'contact_message_group';
                    errorId = 'contact_message_error';
                }
                if (groupId && errorId) {
                    showError(groupId, errorId, message);
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('contactAlerts', 'error', 'Произошла ошибка при отправке');
    });
});

// Добавляем realtime валидацию при вводе
document.querySelectorAll('#registrationForm input').forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value.trim() !== '') {
            validateRegistrationForm();
        }
    });
});

document.querySelectorAll('#contactForm input, #contactForm textarea').forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value.trim() !== '') {
            validateContactForm();
        }
    });
});