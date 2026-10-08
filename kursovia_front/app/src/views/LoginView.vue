<template>
  <main>
    <h1>Авторизация</h1>
    <div class="main_inner">
      <div class="form">
        <div class="bg"><img src="../assets/reglogbg.png" alt=""></div>
        
        <!-- Поле Email -->
        <input type="text" v-model="form.email" placeholder="Почта">
        
        <!-- Поле Пароль -->
        <input type="password" v-model="form.password" placeholder="Пароль">
        
        <!-- Ссылка на восстановление -->
        <div class="forgot-password" @click="openResetModal">
          Забыли пароль?
        </div>

        <!-- Кнопка входа -->
        <div class="reg" @click="login()">Войти</div>
        
        <!-- Ссылка на регистрацию -->
        <a href="/register" class="login">Зарегистрироваться</a>
      </div>
    </div>

    <!-- МОДАЛЬНОЕ ОКНО ВОССТАНОВЛЕНИЯ ПАРОЛЯ -->
    <div v-if="isResetModalOpen" class="modal-overlay" @click.self="closeResetModal">
      <div class="modal-content">
        <h3>Восстановление пароля</h3>
        <p class="modal-desc">Введите email, указанный при регистрации. Мы отправим туда ссылку для сброса.</p>
        
        <input 
          type="email" 
          v-model="resetEmail" 
          placeholder="Ваш email" 
          class="modal-input"
        >
        
        <div class="modal-actions">
          <button class="btn-cancel" @click="closeResetModal">Отмена</button>
          <button class="btn-confirm" @click="resetPassword" :disabled="isLoadingReset">
            {{ isLoadingReset ? 'Отправка...' : 'Восстановить' }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
export default {
  data(){
    return {
      form:{
        email: "",
        password: "",
      },
      // Данные для модалки
      isResetModalOpen: false,
      resetEmail: "",
      isLoadingReset: false
    }
  },

  methods: {
    // Открытие модалки с автозаполнением email
    openResetModal() {
      this.resetEmail = this.form.email; // Берем почту из основного поля, если она там есть
      this.isResetModalOpen = true;
    },

    closeResetModal() {
      this.isResetModalOpen = false;
      this.resetEmail = "";
    },

    // Логин (ваш существующий код)
    login(){
      if(this.form.email == ""){
        alert("Заполните поле почты") 
        return
      } else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)){
        alert("Введите корректную почту") 
        return
      } 

      if(this.form.password == ""){
        alert("Заполните поле пароля") 
        return
      } 

      let headers = new Headers()
      headers.append("Content-Type", "application/json")
      
      fetch(`${process.env.VUE_APP_API_BASE_URL}/login`, {
        method: "POST",
        headers: headers,
        body: JSON.stringify(this.form),
        credentials: 'include'
      })
      .then(response => {
        if(!response.ok){
          throw response;
        } else {
          return response.json()
        }
      })
      .then(data => {
        localStorage.setItem("token", data.token)
        window.location.href = "/courses";
      })
      .catch(error => {
        if (error.status === 422) {
          alert("Не верный логин или пароль");
        } else {
          console.error("Ошибка:", error);
          alert("Произошла ошибка при авторизации. Попробуйте позже.");
        }
      });
    },

    // ✅ НОВЫЙ МЕТОД: Отправка запроса на восстановление
    async resetPassword() {
      if (!this.resetEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.resetEmail)) {
        alert("Пожалуйста, введите корректный email адрес");
        return;
      }

      this.isLoadingReset = true;

      try {
        const headers = new Headers();
        headers.append("Content-Type", "application/json");

        const response = await fetch(`${process.env.VUE_APP_API_BASE_URL}/password`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ email: this.resetEmail })
        });

        const result = await response.json();

        if (response.ok) {
          // ✅ Простой алерт об успехе
          alert(result.message || "Новый пароль отправлен на вашу почту!");
          this.closeResetModal();
        } else {
          alert(result.message || "Ошибка при восстановлении пароля.");
        }
      } catch (error) {
        console.error("Ошибка восстановления:", error);
        alert("Произошла ошибка соединения с сервером.");
      } finally {
        this.isLoadingReset = false;
      }
    }
  }
}
</script>

<style scoped>
/* Ваши старые стили остаются без изменений */
.bg,.form{border-radius:30px}
main{margin-bottom:50px}
.main_inner{display:flex;justify-content:center}
.form{display:flex;flex-direction:column;background:#f6f8fb;padding:35px 30px;gap:10px;width:25%;align-items:center;position:relative}
.bg,.bg img{position:absolute;left:0;width:100%}
.bg{top:0;height:100%;overflow:hidden}
.bg img{bottom:0;height:60%;object-fit:cover;object-position:bottom center;display:block}
.form input{width:100%;border:1px solid #0e6bf73b;border-radius:30px;outline:0;padding:5px 20px;z-index:10}
.reg{margin-top:10px;border:1px solid #0e6bf73b;border-radius:30px;padding:7px 20px}
.login,.reg{z-index:10;cursor:pointer;color:#0b1c4f;text-decoration:none;transition:.1s}
.login:hover,.reg:hover{transform:scale(1.02)}
h1{line-height:90%;background:linear-gradient(90deg,#0b1c4f 0,#0e6cf7 100%);-webkit-background-clip:text;background-clip:text;color:transparent;margin:150px 0 0;text-align:center;font-size:64px;padding-bottom:30px}
.email input{width:35%;border-radius:30px;padding:10px;border:2px solid #0b1c4f4f;outline:0}

/* ✅ НОВЫЕ СТИЛИ ДЛЯ ВОССТАНОВЛЕНИЯ ПАРОЛЯ */
/* Стили для ссылки "Забыли пароль?" */
.forgot-password {
    font-size: 13px;
    color: #0e6cf7;
    text-decoration: underline;
    cursor: pointer; /* Обязательно для кликабельности */
    margin-top: -5px; /* Чуть подтягиваем к полю пароля */
    margin-bottom: 10px;
    
    /* ✅ ВЫРАВНИВАНИЕ ПО ЛЕВОМУ КРАЮ */
    align-self: center; 
    
    /* ✅ ПОВЕРХ ФОНА И ДРУГИХ ЭЛЕМЕНТОВ */
    position: relative; 
    z-index: 10; 
    
    transition: color 0.2s;
}

.forgot-password:hover {
    color: #0b1c4f;
}

/* Стили модального окна */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(3px);
}

.modal-content {
  background: #fff;
  padding: 30px;
  border-radius: 20px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  text-align: center;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.modal-content h3 {
  margin: 0 0 10px 0;
  color: #0b1c4f;
  font-size: 20px;
}

.modal-desc {
  font-size: 14px;
  color: #64748b;
  margin: 0 0 20px 0;
  line-height: 1.4;
}

.modal-input {
  width: 100%;
  padding: 12px 20px;
  border: 1px solid #0e6bf73b;
  border-radius: 30px;
  outline: none;
  font-size: 15px;
  margin-bottom: 20px;
  box-sizing: border-box;
}

.modal-input:focus {
  border-color: #0e6cf7;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.btn-cancel, .btn-confirm {
  padding: 10px 20px;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  font-size: 14px;
}

.btn-cancel {
  background: #f1f5f9;
  color: #64748b;
}

.btn-cancel:hover {
  background: #e2e8f0;
}

.btn-confirm {
  background: #0e6cf7;
  color: #fff;
}

.btn-confirm:hover:not(:disabled) {
  background: #0b1c4f;
  transform: scale(1.02);
}

.btn-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>