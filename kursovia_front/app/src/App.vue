<template>
  <div class="container">
    <header>
      <router-link to="/"><img alt="" src="./assets/webp/ClassMan.webp"></router-link>
      <nav v-if="!isAuthenticated" class="nav">
        <router-link to="/login" class="nav_link">Войти</router-link>
        <router-link to="/register" class="border nav_link">Регистрация</router-link>
      </nav>
      <nav v-else class="nav">
        <a href="#" class="nav_link" @click="createCourse()">Создать курс</a>
        <a href="#" class="nav_link" @click="JoinCourse()">Присоединиться к курсу</a>
        <a href="/courses" class="nav_link">Курсы</a>
        <a href="/about" class="nav_link">О нас</a>
        <a href="/student/board" class="nav_link">Доска заданий</a>
        <a href="/my-board" class="nav_link">Моя доска</a>
        <a href="/blog/lms-dlya-vuzov" class="nav_link">Блог</a>
        <div class="ava_dropDown">
          <div class="ava" @click="showLogout()">
            <img src="./assets/webp/avatar.webp" alt="">
          </div>
          <div v-if="isLogoutVisible" class="logout-dropdown">
            <button @click="logout" class="logout-btn">Выйти</button>
          </div>
        </div>
      </nav>
      <div class="nav_mobile" @click="toggleMobileMenu" :class="{ 'active': isMobileMenuOpen }">
        <div class="line_mobile"></div>
        <div class="line_mobile"></div>
        <div class="line_mobile"></div>
      </div>
    </header>
  </div>
  <div class="mobile-menu" :class="{ 'open': isMobileMenuOpen }">
    <nav v-if="!isAuthenticated" class="nav_mob">
      <router-link to="/login" class="mobile-link" @click="toggleMobileMenu">Войти</router-link>
      <router-link to="/register" class="mobile-link highlight" @click="toggleMobileMenu">Регистрация</router-link>
    </nav>
    <nav v-else class="nav_mob">
      <a href="#" class="mobile-link" @click.prevent="createCourse(); toggleMobileMenu()">Создать курс</a>
      <a href="#" class="mobile-link" @click.prevent="JoinCourse(); toggleMobileMenu()">Присоединиться к курсу</a>
      <a href="/courses" class="mobile-link" @click="toggleMobileMenu()">Курсы</a>
      <a href="/about" class="mobile-link" @click="toggleMobileMenu()">О нас</a>
      <a href="/student/board" class="mobile-link" @click="toggleMobileMenu()">Доска заданий</a>
      <a href="/my-board" class="mobile-link" @click="toggleMobileMenu()">Моя доска</a>
      <a href="/blog/lms-dlya-vuzov" class="mobile-link" @click="toggleMobileMenu()">Блог</a>
      
      <div class="mobile-logout-block">
        <button @click="logout" class="mobile-link logout-btn">Выйти</button>
      </div>
    </nav>
  </div>
  <router-view/>
  <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModalJoinCourse">
    <div class="modal-content">
      <div class="form-group">
        <input class="file-input-wrapper" type="text" placeholder="Код" v-model="access_code" />
      </div>
      <button @click="submitJoinForm" class="submit-btn">Присоединиться</button>
    </div>
  </div>
  <div v-if="isModalCreateOpen" class="modal-overlay" @click.self="closeModalCreateCourse">
    <div class="modal-content">
      <div class="form-group">
        <input class="file-input-wrapper" type="text" placeholder="Название" v-model="name" />
      </div>
      <button @click="submitCreateForm" class="submit-btn">Создать</button>
    </div>
  </div>
</template>


<script>
export default{
  data(){
    return {
      isMobileMenuOpen: false,
      isLogoutVisible: false,
      isModalOpen: false,
      access_code: "",

      isModalCreateOpen: false,
      name: ""
    }
  },
  computed: {
    isAuthenticated() {
      return !!localStorage.getItem('token');
    }
  },
  methods: {
    showLogout(){
      this.isLogoutVisible = !this.isLogoutVisible;
    },
    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
      if (this.isMobileMenuOpen) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    },
    async logout() {
      try{
        let headers = new Headers()
        headers.append("Content-Type", "application/json")
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
        let request = await fetch(`${process.env.VUE_APP_API_BASE_URL}/logout`, {
          method: "POST",
          headers: headers,
        })
        let response = await request.json();
        if(!request.ok){
          console.log(response)
          alert("Неудалось выйти")
        } 
        window.location.href = '/login'
      } catch (error){
        console.log(error);
        alert("Произошла ошибка соединения с сервером.");
      }
      localStorage.removeItem('token');
      localStorage.removeItem('email');
      
      this.isLogoutVisible = false;
      this.isMobileMenuOpen = false; 
      document.body.style.overflow = '';
    },
    createCourse(){
      this.isModalCreateOpen = true;
    },
    closeModalCreateCourse() {
      this.isModalCreateOpen = false;
    },
    async submitCreateForm(){
      try{
        let headers = new Headers()
        headers.append("Content-Type", "application/json")
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
        let request = await fetch(`${process.env.VUE_APP_API_BASE_URL}/courses`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({
            name: this.name
          })
        })
        let response = await request.json();
        if(!request.ok){
          console.log(response)
          alert("Неудалось создать курс")
        } else {
          alert("Курс создан")
        }
        window.location.href = '/courses'
      } catch (error){
        console.log(error);
        alert("Произошла ошибка соединения с сервером.");
      }
    },


    JoinCourse(){
      this.isModalOpen = true;
    },
    closeModalJoinCourse() {
      this.isModalOpen = false;
    },
    async submitJoinForm(){
      try{
        let headers = new Headers()
        headers.append("Content-Type", "application/json")
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
        let request = await fetch(`${process.env.VUE_APP_API_BASE_URL}/userincourses`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({
            access_code: this.access_code
          })
        })
        let response = await request.json();
        if(!request.ok){
          console.log(response)
          alert("Неудалось присоединиться к курсу")
        } else {
          alert("Вы присединились к курсу")
        }
        window.location.href = '/courses'
      } catch (error){
        console.log(error);
        alert("Произошла ошибка соединения с сервером.");
      }
    }
  }
}
</script>

<style>
  *{
    margin: 0;
    padding: 0;
    box-sizing: border-box;  
  }

  header{
    padding-top: 30px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #F8FAFC;
  }
  
  .nav{
    display: flex;
    align-items: center;
  }

  .nav_mobile{
    display: none;
  }

  .nav_link{
    color: #0966F6;
    text-decoration: none;
    margin-left: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
  }

  .border {
    border: 2px solid #0b1c4f44;
    padding: 10px 30px;
    font-size: 18px;
    border-radius: 20px;
    z-index: 1;
    overflow: hidden;
  }
  
  .nav_link:hover {
    color: #0B1C4F;
  }

  .ava_dropDown {
    margin-left: 30px;
    width: max-content;
    position: relative;
    display: inline-block;
  }

  .ava {
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s;
  }

  .ava:hover {
    transform: scale(1.05);
  }

  .ava img {
    object-fit: cover;
    display: block;
  }

  .logout-dropdown {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    
    background: white;
    padding: 7px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    z-index: 100;
    margin-top: 8px;
    
    animation: fadeIn 0.2s ease-in-out;
  }

  .logout-btn {
    background: transparent;
    border: none;
    color: #ff4d4f;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    padding: 2px 7px;
    border-radius: 8px;
    transition: background 0.2s;
    white-space: nowrap;
  }

  .logout-btn:hover {
    background: #fff1f0;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px) translateX(-50%); }
    to { opacity: 1; transform: translateY(0) translateX(-50%); }
  }

  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6); /* Полупрозрачный черный */
    display: flex;
    justify-content: center; /* Центр по горизонтали */
    align-items: center;     /* Центр по вертикали */
    z-index: 9999;           /* Поверх всех элементов */
    backdrop-filter: blur(2px); /* Легкое размытие фона (опционально) */
  }

  /* Белое окно формы */
  .modal-content {
    background: #ffffff;
    width: 100%;
    max-width: 400px; /* Ширина как на макете */
    padding: 40px 30px;
    border-radius: 25px; /* Сильное скругление углов */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    gap: 20px;
    position: relative;
    
    /* Анимация появления */
    animation: slideUp 0.3s ease-out;
  }

  /* Стиль для группы с файлом */
  .form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .file-input-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #dbeafe; /* Светло-голубая рамка */
    border-radius: 30px;
    padding: 8px 15px;
    background: #f8fafc;
    cursor: pointer;
    transition: border-color 0.2s;
  }

  .file-input-wrapper:hover {
    border-color: #0E6CF7;
  }

  .file-label {
    font-size: 14px;
    color: #64748b;
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 10px;
  }

  .file-btn {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 12px;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
  }

  .file-btn:hover {
    background: #f1f5f9;
  }

  .file-name {
    font-size: 12px;
    color: #0E6CF7;
    margin-top: 5px;
    margin-left: 10px;
  }

  /* Текстовое поле */
  .comment-area {
    width: 100%;
    min-height: 120px;
    border: 1px solid #dbeafe;
    border-radius: 15px;
    padding: 15px;
    font-family: inherit;
    font-size: 14px;
    resize: none; /* Запрет изменения размера */
    outline: none;
    box-sizing: border-box; /* Чтобы padding не ломал ширину */
    transition: border-color 0.2s;
    background: #fff;
  }

  .comment-area:focus {
    border-color: #0E6CF7;
    box-shadow: 0 0 0 3px rgba(14, 108, 247, 0.1);
  }

  .comment-area::placeholder {
    color: #94a3b8;
  }

  /* Кнопка "Опубликовать" */
  .submit-btn {
    align-self: center; /* Центрируем кнопку */
    background: #fff;
    border: 1px solid #0E6CF7;
    color: #0B1C4F;
    padding: 10px 35px;
    border-radius: 30px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 10px;
  }

  .submit-btn:hover {
    background: #0E6CF7;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(14, 108, 247, 0.3);
  }

  .nav_mob{
    display: none;
  }

  

  /* Анимация всплытия */
  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 1250px) {
    .nav{
      display: none;
    }

    .nav_mob{
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .nav_mobile{
      display: flex;
      flex-direction: column;
      gap: 10px;
      width: 40px;
    }
    .line_mobile{
      width: 100%;
      height: 2px;
      background: #0966F6;
    }
    /* Стили для кнопки гамбургера (анимация в крестик) */
    .nav_mobile {
      cursor: pointer;
      z-index: 101; /* Чтобы была поверх меню */
    }

    .nav_mobile.active .line_mobile:nth-child(1) {
      transform: translateY(18px) rotate(45deg);
    }
    .nav_mobile.active .line_mobile:nth-child(2) {
      opacity: 0;
    }
    .nav_mobile.active .line_mobile:nth-child(3) {
      transform: translateY(-6px) rotate(-45deg);
    }

    /* Контейнер выпадающего меню */
    .mobile-menu {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      background: #fff;
      z-index: 100;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 20px;
      
      /* Анимация появления */
      opacity: 0;
      visibility: hidden;
      transform: translateY(-20px);
      transition: all 0.3s ease-in-out;
    }

    .mobile-menu.open {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    /* Ссылки внутри мобильного меню */
    .mobile-link {
      font-size: 24px;
      color: #0B1C4F !important;
      text-decoration: none;
      font-weight: 600;
      transition: 0.2s;
      border: none;
      background: none;
      cursor: pointer;
      font-family: inherit;
    }

    .mobile-link.highlight {
      color: #0966F6;
      border: 2px solid #0b1c4f44;
      padding: 10px 30px;
      border-radius: 20px;
    }

    .mobile-link:hover {
      color: #0966F6;
    }

    .mobile-logout-block {
      margin-top: 20px;
    }

    .logout-btn {
      color: #ff4d4f !important;
    }
    .logout-btn:hover {
      color: #d9363e !important;
    }
  }

  
</style>


