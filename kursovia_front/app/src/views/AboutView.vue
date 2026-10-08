<template>
  <main class="team-page">
    <div class="container">
      <div class="page-header">
        <h1>Наша команда</h1>
        <h5>Люди, которые делают обучение доступным и эффективным</h5>
        <p class="subtitle">Мы собрали лучших специалистов в сфере образования и IT, чтобы создать идеальную платформу для ваших знаний.</p>
      </div>
      <div class="team-grid">
        <div class="team-card">
          <div class="card-photo">
            <img src="../assets/webp/worker1.webp" alt="Фото сотрудника">
          </div>
          <div class="card-info">
            <h3>Иван Александрович</h3>
            <p class="position">Основатель и CEO</p>
            <div class="expert-block">
              <p class="expert-text">«Образование должно быть таким же удобным, как любимый мессенджер. Мы строим будущее обучения сегодня.»</p>
            </div>
            <div class="experience">
              <span>10+ лет в ClassMan</span>
            </div>
            <div class="contacts">
              <a href="mailto:ivan@eduplatform.ru">ivan@eduplatform.ru</a>
              <a href="https://t.me/username" target="_blank" class="social-link-small">Telegram</a>
            </div>
          </div>
        </div>
        <div class="team-card">
          <div class="card-photo">
            <img src="../assets/webp/worker2.webp" alt="Фото сотрудника">
          </div>
          <div class="card-info">
            <h3>Анна Сергеевна</h3>
            <p class="position">Ведущий методист</p>
            <div class="expert-block">
              <p class="expert-text">«Качество курса определяется не картинкой, а структурой подачи материала. Мы проверяем каждый урок.»</p>
            </div>
            <div class="experience">
              <span>Кандидат пед. наук, 8 лет опыта</span>
            </div>
            <div class="contacts">
              <a href="mailto:anna@eduplatform.ru">anna@eduplatform.ru</a>
              <a href="https://vk.com/id" target="_blank" class="social-link-small">ВКонтакте</a>
            </div>
          </div>
        </div>
        <div class="team-card">
          <div class="card-photo">
            <img src="../assets/webp/worker3.webp" alt="Фото сотрудника">
          </div>
          <div class="card-info">
            <h3>Дмитрий Олегович</h3>
            <p class="position">Технический директор (CTO)</p>
            <div class="expert-block">
              <p class="expert-text">«Безопасность данных и скорость работы — наш приоритет. Ваша успеваемость под надежной защитой.»</p>
            </div>
            <div class="experience">
              <span>Разработчик Laravel & Vue.js</span>
            </div>
            <div class="contacts">
              <a href="mailto:dmitry@eduplatform.ru">dmitry@eduplatform.ru</a>
              <a href="https://t.me/username" target="_blank" class="social-link-small">Telegram</a>
            </div>
          </div>
        </div>
      </div>
      <div class="join-team-cta">
        <h3>Хотите работать с нами?</h3>
        <p>Мы всегда в поиске талантливых преподавателей и разработчиков.</p>
        <!-- <a href="/contacts" class="cta-button">Связаться с HR</a> -->
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
            <a href="/student/board" class="nav_link">Доска заданий</a>
            <a href="/blog/lms-dlya-vuzov" class="nav_link">Блог</a>
          </div>
          <div class="footer_nav_block">
            <a href="/my-board" class="nav_link">Моя доска</a>
          </div>
        </nav>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'TeamPage',
  computed: {
    isAuthenticated() {
      return !!localStorage.getItem('token');
    }
  },
}
</script>

<style scoped>
.contacts a:hover,.position{color:#0e6cf7}.footer_nav_block{display:flex;flex-direction:column;row-gap:7px}.card-info,.team-card{flex-direction:column}.nav{display:flex}.footer_inner{display:flex;gap:10px}footer{background:#0b1c4f;padding:50px 0}footer a,footer p{color:#fff}.team-page{padding:60px 0;min-height:100vh;background:#F8FAFC}.container{max-width:1200px;margin:0 auto;padding:0 20px}.page-header{text-align:center;margin-bottom:60px}.page-header h1{font-size:64px;line-height:90%;background:linear-gradient(90deg,#0b1c4f 0,#0e6cf7 100%);-webkit-background-clip:text;background-clip:text;color:transparent;margin:0 0 15px}.page-header h5{color:#0b1c4f;font-size:24px;margin:0 0 10px}.page-header .subtitle{color:#828386;font-size:18px;max-width:700px;margin:0 auto}.team-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;margin-bottom:80px}.team-card{background:#f6f8fb;border-radius:30px;overflow:hidden;transition:transform .3s,box-shadow .3s;display:flex}.team-card:hover{transform:translateY(-10px);box-shadow:0 20px 40px rgba(11,28,79,.1)}.card-photo{width:100%;height:300px;overflow:hidden;background:#e0e5ec}.card-photo img{width:100%;height:100%;object-fit:cover;object-position:center -50px;transition:transform .5s}.team-card:hover .card-photo img{transform:scale(1.05)}.card-info{padding:30px;display:flex;flex-grow:1}.card-info h3{color:#0b1c4f;font-size:24px;margin:0 0 5px;font-weight:700}.position{font-weight:600;font-size:16px;margin:0 0 20px;text-transform:uppercase;letter-spacing:.5px}.expert-block{background:#fff;padding:15px;border-radius:15px;margin-bottom:15px;border-left:4px solid #0e6cf7}.expert-text{color:#555;font-size:14px;line-height:1.5;margin:0;font-style:italic}.expert-icon{font-size:20px;margin-right:5px}.experience{margin-bottom:20px}.experience span{background:rgba(14,108,247,.1);color:#0b1c4f;padding:5px 12px;border-radius:20px;font-size:13px;font-weight:600;display:inline-block}.contacts{margin-top:auto;display:flex;flex-direction:column;gap:10px;border-top:1px solid rgba(11,28,79,.1);padding-top:20px}.contacts a{color:#0b1c4f;text-decoration:none;font-size:15px;transition:color .2s}.social-link-small{display:inline-block;background:#0b1c4f;color:#fff!important;text-align:center;padding:8px 0;border-radius:20px;font-weight:600;transition:background .2s}.social-link-small:hover{background:#0e6cf7}.join-team-cta{text-align:center;background:linear-gradient(135deg,#0b1c4f 0,#0e6cf7 100%);border-radius:30px;padding:50px;color:#fff}.join-team-cta h3{font-size:32px;margin:0 0 10px}.join-team-cta p{color:rgba(255,255,255,.8);margin-bottom:30px;font-size:18px}.cta-button{display:inline-block;background:#fff;color:#0b1c4f!important;padding:15px 40px;border-radius:30px;font-weight:700;text-decoration:none;transition:transform .2s,box-shadow .2s}.cta-button:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,.2)}@media (max-width:768px){.page-header h1{font-size:42px}.page-header h5{font-size:20px}.team-grid{grid-template-columns:1fr}.card-photo{height:250px}.join-team-cta{padding:30px}}
</style>