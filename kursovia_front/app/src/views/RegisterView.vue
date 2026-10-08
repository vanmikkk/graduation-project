<template>
  <main>
    <h1>Регистрация</h1>
    <div class="main_inner">
      <div class="form">
        <div class="bg"><img src="../assets/reglogbg.png" alt=""></div>
        <input type="text" v-model="form.email" placeholder="Почта">
        <input type="text" v-model="form.FIO" placeholder="ФИО">
        <input type="password" v-model="form.password" placeholder="Пароль" >
        <input type="password" v-model="form.confirm_password" placeholder="Повтор пароля">
        <div class="reg" @click="register()">Зарегистрироваться</div>
        <a href="/login" class="login">Войти</a>
      </div>
    </div>

    <transition name="fade-slide">
      <div v-if="showNotification" class="custom-toast" :class="notificationType">
        <span class="toast-text">{{ notificationMessage }}</span>
      </div>
    </transition>
  </main>
</template>

<script>
export default {
  data() {
    return {
      form: {
        email: localStorage.getItem("email") == "undefined" || localStorage.getItem("email") == "" ? "" : localStorage.getItem("email"),
        password: "",
        confirm_password: "",
        FIO: "",
      },
      // Данные для уведомлений
      showNotification: false,
      notificationMessage: "",
      notificationType: "error", // 'error' или 'success'
      errorField: null // Для подсветки поля с ошибкой
    };
  },
  methods: {
    // Функция показа уведомления
    showToast(message, type = "error", field = null) {
      this.notificationMessage = message;
      this.notificationType = type;
      this.errorField = field; // Подсвечиваем поле, если нужно
      this.showNotification = true;

      // Скрываем через 3 секунды
      setTimeout(() => {
        this.showNotification = false;
        this.errorField = null; // Убираем подсветку
      }, 3000);
    },

    register() {
      // Валидация почты
      if (this.form.email == "") {
        this.showToast("Заполните поле почты", "error", "email");
        return;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
        this.showToast("Введите корректную почту", "error", "email");
        return;
      }

      // Валидация ФИО
      if (this.form.FIO == "") {
        this.showToast("Заполните поле ФИО", "error", "FIO");
        return;
      }

      // Валидация пароля
      if (this.form.password == "") {
        this.showToast("Заполните поле пароля", "error", "password");
        return;
      }

      if (this.form.password.length < 8) {
        this.showToast("Пароль должен состоять минимум из 8 символов", "error", "password");
        return;
      }

      // Проверка совпадения паролей
      if (this.form.password != this.form.confirm_password) {
        this.showToast("Пароли не совпадают", "error", "confirm_password");
        return;
      }

      // Отправка запроса
      let headers = new Headers();
      headers.append("Content-Type", "application/json");
      
      fetch(`${process.env.VUE_APP_API_BASE_URL}/register`, {
        method: "POST",
        headers: headers,
        body: JSON.stringify(this.form),
        credentials: 'include'
      })
      .then(response => {
        if (!response.ok) {
          throw response;
        }
      })
      .then(data => {
        this.showToast("Регистрация успешна! Перенаправление...", "success");
        setTimeout(() => {
          window.location.href = "/login";
        }, 1000);
      })
      .catch(error => {
        if (error.status === 422) {
          this.showToast("Такой пользователь уже существует", "error", "email");
        } else {
          console.error("Ошибка:", error);
          this.showToast("Произошла ошибка при регистрации. Попробуйте позже.", "error");
        }
      });
    }
  }
};
</script>

<style scoped>
  .bg,.form{border-radius:30px}main{margin-bottom:50px}.main_inner{display:flex;justify-content:center}.form{display:flex;flex-direction:column;background:#f6f8fb;padding:35px 30px;gap:10px;width:25%;align-items:center;position:relative}.bg,.bg img{position:absolute;left:0;width:100%}.bg{top:0;height:100%;overflow:hidden}.bg img{bottom:0;height:60%;object-fit:cover;object-position:bottom center;display:block}.form input{width:100%;border:1px solid #0e6bf73b;border-radius:30px;outline:0;padding:5px 20px;z-index:10}.reg{margin-top:10px;border:1px solid #0e6bf73b;border-radius:30px;padding:7px 20px}.login,.reg{z-index:10;cursor:pointer;color:#0b1c4f;text-decoration:none;transition:.1s}.login:hover,.reg:hover{transform:scale(1.02)}h1{line-height:90%;background:linear-gradient(90deg,#0b1c4f 0,#0e6cf7 100%);-webkit-background-clip:text;background-clip:text;color:transparent;margin:150px 0 0;text-align:center;font-size:64px;padding-bottom:30px}.email input{width:35%;border-radius:30px;padding:10px;border:2px solid #0b1c4f4f;outline:0}

  .input-error {
  border-color: #DC2626 !important;
  background-color: #FEF2F2;
  animation: shake 0.3s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

/* Стили для всплывающего уведомления (Toast) */
.custom-toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  border-radius: 50px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 10000;
  font-weight: 600;
  font-size: 14px;
  pointer-events: none;
  white-space: nowrap;
}

/* Типы уведомлений */
.custom-toast.error {
  background: #FEE2E2; /* Красный фон */
  color: #DC2626;     /* Темно-красный текст */
  border: 1px solid #FCA5A5;
}

.custom-toast.success {
  background: #DCFCE7; /* Зеленый фон */
  color: #16A34A;      /* Темно-зеленый текст */
  border: 1px solid #86EFAC;
}

.toast-icon {
  font-size: 16px;
}

/* Анимация появления/исчезновения */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(-50%) translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(10px);
}
</style>