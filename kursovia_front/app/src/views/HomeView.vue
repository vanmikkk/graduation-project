<template>
  <main>
    <div class="container">
      <div class="main_inner">
        <h1>Твой старт в изучении <br>всех наук мира</h1>
        <h5>Учеба может быть интересной!</h5>
        <div class="container">
          <div class="main_content">
            <div class="email" v-if="!isAuthenticated">
              <input type="text" placeholder="Напишите вашу почту" v-model="email">
              <div class="link" @click="toReg()"><a href="#">Начать</a><div class="img"><img src="../assets/webp/Arrow1.webp" alt="стрелка"></div></div>
            </div>
            <div class="toCourses" v-else>
              <div class="link"><a href="/courses">Перейти к курсам</a></div>
            </div>
            <div class="pluses">
              <div class="plus_block">
                <h3>Онлайн формат</h3>
                <p>Доступ к курсам, заданиям и материалам из любой точки мира в любое удобное время. Учитесь в своем темпе, без привязки к расписанию аудиторий.</p>
                <div class="img">
                  <img src="../assets/webp/plus1.webp" alt="Плюс 1">
                </div>
              </div>
              <div class="plus_block">
                <h3>Всё в одном месте</h3>
                <p>Единое пространство для лекций, домашних работ, оценок и общения. Забудьте о потерях файлов в чатах и почте — вся история обучения структурирована и всегда под рукой.</p>
                <div class="img plus2">
                  <img src="../assets/webp/plus2.webp" alt="Плюс 2">
                </div>
              </div>
              <div class="plus_block">
                <h3>Онлайн формат</h3>
                <p>Доступ к курсам, заданиям и материалам из любой точки мира в любое удобное время. Учитесь в своем темпе, без привязки к расписанию аудиторий.</p>
                <div class="img">
                  <img src="../assets/webp/plus3.webp" alt="Плюс 3">
                </div>
              </div>
              <div class="plus_block big_plus">
                <h3>Твой рост начинается с успешного обучения</h3>
                <div class="line"></div>
                <p>Забудьте о хаосе в чатах и потерянных файлах на почте. Мы создали среду, где каждый учебный процесс — от создания курса до сдачи финального проекта — происходит прозрачно и организованно. Загружайте работы, получайте оценки и следите за своим прогрессом в режиме реального времени.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- <div class="trust-section">
          <h2>Почему нам доверяют</h2>
          <div class="trust-grid">
            <div class="trust-card">
              <div class="trust-icon">🌐</div>
              <h4>5+ лет опыта</h4>
              <p>Работаем в сфере образовательных технологий с 2020 года. Более 10 000 успешных выпускников.</p>
            </div>
            <div class="trust-card">
              <div class="trust-icon">🔒</div>
              <h4>Безопасность данных</h4>
              <p>Все данные хранятся на защищенных серверах в РФ. Соответствуем требованиям ФЗ-152 о персональных данных.</p>
            </div>
            <div class="trust-card">
              <div class="trust-icon">⚡</div>
              <h4>99.9% uptime</h4>
              <p>Гарантируем стабильную работу платформы. Ваши материалы и работы всегда доступны в нужный момент.</p>
            </div>
            <div class="trust-card">
              <div class="trust-icon"></div>
              <h4>Проверенное качество</h4>
              <p>Все курсы проходят многоступенчатую проверку методистами. Только актуальные и проверенные материалы.</p>
            </div>
          </div>
        </div> -->

        <!-- <div class="contact-section">
          <h2>Свяжитесь с нами</h2>
          <div class="contact-grid">
            <div class="contact-card">
              <div class="contact-icon">📞</div>
              <h4>Телефон</h4>
              <a href="tel:+79990000000">+7 (999) 000-00-00</a>
              <p>Пн-Пт с 9:00 до 18:00</p>
            </div>
            <div class="contact-card">
              <div class="contact-icon">✉️</div>
              <h4>Почта</h4>
              <a href="mailto:support@eduplatform.ru">support@eduplatform.ru</a>
              <p>Отвечаем в течение 24 часов</p>
            </div>
            <div class="contact-card">
              <div class="contact-icon">🌐</div>
              <h4>Социальные сети</h4>
              <div class="social-links">
                <a href="https://vk.com" target="_blank" class="social-link vk">ВКонтакте</a>
                <a href="https://t.me" target="_blank" class="social-link tg">Telegram</a>
              </div>
              <p>Новости и обновления</p>
            </div>
          </div>
        </div> -->
      </div>
    </div>
  </main>
  <footer>
    <div class="container">
      <div class="footer_inner">
        <nav v-if="!isAuthenticated" class="nav">
          <router-link to="/login" class="nav_link">Войти</router-link>
          <router-link to="/register" class="border nav_link">Регистрация</router-link>
        </nav>
        <nav v-else class="nav">
          <div class="footer_nav_block">
            <a href="#" class="nav_link" @click="createCourse()">Создать курс</a>
            <a href="#" class="nav_link" @click="JoinCourse()">Присоединиться к курсу</a>
          </div>
          <div class="footer_nav_block">
            <a href="/courses" class="nav_link">Курсы</a>
            <a href="/about" class="nav_link">О нас</a>
          </div>
          <div class="footer_nav_block">
            <a href="/blog/lms-dlya-vuzov" class="nav_link">Блог</a>
          </div>
        </nav>
      </div>
    </div>
  </footer>
</template>

<script>
  export default {
    data(){
      return {
        email: ""
      }
    },
    computed: {
      isAuthenticated() {
        return !!localStorage.getItem('token');
      }
    },
    methods: {
      toReg(){
        if(this.email == ""){
          alert("Заполните поле почты") 
          return
        } else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)){
          alert("Введите корректную почту") 
        } else {
          localStorage.setItem("email", this.email)
          window.location.href = "/register"
        }
      }
    }
  }
</script>

<style scoped>
  .link,h1{background:linear-gradient(90deg,#0b1c4f 0,#0e6cf7 100%)}.email input,.link{border-radius:30px}.email,.img,.img img,.toCourses{width:100%}.trust-section,h1,h5{text-align:center}.big_plus .line,.social-link:hover{background:rgba(255,255,255,.3)}.footer_nav_block{display:flex;flex-direction:column;row-gap:7px}.main_content,.toCourses{justify-content:center;display:flex}.nav{display:flex}.footer_inner{display:flex;gap:10px}.toCourses{align-items:center}main{margin:150px 0}h1{line-height:90%;-webkit-background-clip:text;background-clip:text;color:transparent;margin:50px 0 0;font-size:64px;padding-bottom:15px}h5{color:#0b1c4f}.main_content{flex-direction:column}.email{display:flex;justify-content:center;align-items:center}.link{display:flex;gap:5px;align-items:center;padding:10px 70px;cursor:pointer;transition:.1s}.link:hover{transform:scale(1.01)}a{color:#fff;text-decoration:none}.img{transform:translateY(2px)}.email input{width:35%;padding:10px;border:2px solid #0b1c4f4f;outline:0}.contact-card,.plus_block,.trust-card{border-radius:30px}.pluses{margin-top:40px;display:flex;justify-content:space-between;flex-wrap:wrap;row-gap:20px}.plus_block{background:#f6f8fb;width:32%;display:flex;flex-direction:column;justify-content:space-between}.plus_block h3{margin-top:25px!important;margin-bottom:0!important;color:#0b1c4f}.plus_block h3,.plus_block p{margin:0 30px 15px}.plus_block p{color:#828386}.plus2{padding:0 30px 15px}.big_plus{width:100%;background:linear-gradient(90deg,#0b68f5 0,#7cb0fc 100%)}.big_plus h3,.big_plus p,footer p{color:#fff}.big_plus .line{height:1px;margin:10px 30px}footer{background:#0b1c4f;padding:50px 0}.trust-section{margin-top:80px}.contact-section h2,.trust-section h2{font-size:42px;color:#0b1c4f;margin-bottom:40px;text-align:center}.contact-grid,.trust-grid{display:flex;justify-content:space-between;flex-wrap:wrap;gap:20px}.trust-card{background:#f6f8fb;padding:30px;width:23%;text-align:left;transition:transform .2s}.trust-card:hover{transform:translateY(-5px)}.contact-icon,.trust-icon{font-size:48px;margin-bottom:15px}.trust-card h4{color:#0b1c4f;font-size:20px;margin-bottom:10px}.trust-card p{color:#828386;font-size:14px;line-height:1.5}.contact-section{margin-top:80px;margin-bottom:50px;text-align:center}.contact-card{background:linear-gradient(135deg,#0b1c4f 0,#0e6cf7 100%);padding:40px 30px;width:32%;text-align:center;color:#fff;transition:transform .2s}.contact-card:hover{transform:scale(1.02)}.contact-card h4{font-size:20px;margin-bottom:10px}.contact-card a{color:#fff;font-size:18px;text-decoration:underline;display:block;margin-bottom:5px}.contact-card p{color:rgba(255,255,255,.7);font-size:14px}.social-links{display:flex;justify-content:center;gap:15px;margin:15px 0}.social-link{background:rgba(255,255,255,.2);padding:8px 20px;border-radius:20px;font-size:14px;transition:background .2s}.social-link.vk:hover{background:#07f}.social-link.tg:hover{background:#229ed9}@media (max-width:1440px){.plus_block{width:49%}.big_plus{width:100%}.pluses{justify-content:center;gap:20px}}@media (max-width:1050px){.trust-card{width:31%}}@media (max-width:780px){.plus_block{width:100%}.email input{width:49%}h1{font-size:48px}}@media (max-width:491px){.email input{width:100%}h1{font-size:48px}.email{flex-wrap:wrap}.link{margin-top:20px}}
</style>