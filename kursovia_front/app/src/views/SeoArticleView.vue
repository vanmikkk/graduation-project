<template>
  <main class="seo-page">
    <div class="container">
      <aside class="sidebar">
        <h1>Блог</h1>
        <div class="card nav-card">
          <a href="/courses" class="back-link">← Назад к курсам</a>
          <div class="line"></div>
          <h3>Навигация</h3>
          <ul>
            <li><a href="#benefits">Преимущества</a></li>
            <li><a href="#comparison">Сравнение</a></li>
            <li><a href="#start">Как начать</a></li>
          </ul>
          <div class="cta-block">
            <p>Готовы попробовать?</p>
          </div>
        </div>
      </aside>
      <div class="content-area">
        
        <article class="article-card">
          <h1>Надежная отечественная LMS: лучшая альтернатива Google Classroom для вузов и колледжей</h1>
          <div class="meta-info">
            <span>Обновлено: Март 2026</span>
            <span>Время чтения: 3 мин</span>
          </div>
          <div class="line"></div>
          <p class="lead-text">
            В условиях цифровизации образования перед преподавателями стоит сложный выбор платформ. Многие привыкли использовать зарубежные сервисы, но вопросы безопасности данных и стабильности доступа становятся критическими. Наша платформа — это <strong>современная LMS система для российского образования</strong>, разработанная с учетом всех требований ФЗ-152.
          </p>
          <figure class="img-block">
            <img src="../assets/webp/ris1.webp" alt="Интерфейс LMS системы для вуза со статусами сдачи заданий">
            <figcaption>Рис. 1. Наглядный контроль дедлайнов в личном кабинете преподавателя.</figcaption>
          </figure>
          <h2 id="benefits">Почему преподаватели выбирают наше решение?</h2>
          <p>
            Главная проблема мессенджеров и почты — хаос в файлах. Студенты присылают работы в разное время, версии теряются, а дедлайны соблюдаются не строго. Наш сервис позволяет решить эти задачи эффективно:
          </p>
          
          <ul class="features-list">
            <li><strong>Автоматизировать сбор работ:</strong> Все файлы студентов сохраняются в структурированном виде.</li>
            <li><strong>Контролировать сроки:</strong> Система автоматически подсвечивает просроченные задания.</li>
            <li><strong>Оценивать прозрачно:</strong> Преподаватель может оставить комментарий и выставить балл.</li>
          </ul>

          <h2 id="comparison">Сравнение с аналогами: в чем наши преимущества?</h2>
          <p>
            В отличие от тяжелых систем вроде Moodle, наш интерфейс интуитивно понятен. Мы гарантируем <strong>хранение данных на серверах в РФ</strong> и независимость от внешних блокировок.
          </p>

          <figure class="img-block">
            <img src="../assets/webp/ris2.webp" alt="Схема работы платформы для проверки студенческих работ">
            <figcaption>Рис. 2. Путь файла от студента до итоговой оценки.</figcaption>
          </figure>

          <h3 id="start">Как начать работу за 5 минут?</h3>
          <ol class="steps-list">
            <li>Создайте новый курс и получите уникальный код доступа.</li>
            <li>Пригласите студентов и публикуйте первое задание.</li>
          </ol>

          <div class="conclusion-box">
            <p>Попробуйте <strong>удобную платформу для проверки заданий</strong> уже сегодня.</p>
            <div class="action-buttons">
              <a href="/courses" class="btn-primary">Создать курс</a>
            </div>
          </div>

        </article>
      </div>
    </div>
  </main>
  <footer>
    <div class="container2">
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
  name: 'SeoArticleView',
  computed: {
    isAuthenticated() {
      return !!localStorage.getItem('token');
    }
  },
};
</script>

<style scoped>
  .container,.footer_inner,.meta-info,.nav,.sidebar{display:flex}h1,h3,p{margin-bottom:15px}.back-link,.btn-primary,.btn-secondary,.sidebar a{text-decoration:none;transition:.2s}.btn-primary:hover,.btn-secondary:hover{background:#0b1c4f;transform:translateY(-2px)}.footer_nav_block{display:flex;flex-direction:column;row-gap:7px}.footer_inner{gap:10px}footer{background:#0b1c4f;padding:50px 0}footer a,footer p{color:#fff}.seo-page{background:#fff;min-height:100vh;padding-bottom:50px;font-family:Inter,sans-serif}.container{max-width:1600px;margin:0 auto;padding:40px 20px;gap:2%;justify-content:center}.sidebar{width:24%;min-width:250px;flex-direction:column;gap:20px}.content-area{width:74%}.article-card,.nav-card{background:#f6f8fb;border-radius:30px;padding:40px;box-shadow:0 4px 15px rgba(0,0,0,.05)}.article-card{color:#333;line-height:1.6}h1{font-size:36px;color:#0b1c4f;font-weight:700;line-height:1.2}.meta-info{font-size:14px;color:#888;margin-bottom:25px;gap:20px}.back-link:hover,.lead-text,.sidebar h3,h2,h3{color:#0b1c4f}.lead-text{font-size:18px;font-weight:500;margin-bottom:30px}.cta-block p,h2,h3,strong{font-weight:600}h2{font-size:26px;margin-top:40px;margin-bottom:20px}h3{font-size:22px;margin-top:30px}.sidebar a:hover,strong{color:#0e6cf7}p{font-size:16px}.features-list,.steps-list{padding-left:20px;margin-bottom:20px}.features-list li,.steps-list li{margin-bottom:12px;font-size:16px}.steps-list li{list-style-type:decimal}.img-block{margin:30px 0;background:#fff;padding:10px;border-radius:20px;box-shadow:0 4px 10px rgba(0,0,0,.05)}.img-block img{width:100%;height:auto;border-radius:15px;display:block}.figcaption{text-align:center;font-size:13px;color:#666;margin-top:10px;font-style:italic}.back-link{color:#0966f6;font-size:14px;display:block;margin-bottom:10px}.line{width:100%;height:1px;background:#0b1c4f21;margin:15px 0}.conclusion-box,.cta-block{background:#fff;text-align:center}.sidebar h3{font-size:18px;margin-bottom:15px}.sidebar ul{list-style:none;padding:0;margin:0 0 20px}.sidebar li{margin-bottom:10px}.sidebar a{color:#555;font-size:14px}.cta-block{padding:15px;border-radius:20px}.cta-block p{font-size:14px;color:#0b1c4f;margin:0 0 10px}.conclusion-box{margin-top:50px;padding:30px;border-radius:25px;border:1px solid #eef2f7}.action-buttons{margin-top:25px;display:flex;justify-content:center;gap:20px;flex-wrap:wrap}.btn-primary,.btn-secondary{padding:12px 35px;border-radius:30px;font-weight:600;font-size:16px;display:inline-block}.btn-primary{background:#0e6cf7;color:#fff!important;border:1px solid #0e6cf7}.btn-primary:hover{border-color:#0b1c4f;box-shadow:0 4px 12px rgba(14,108,247,.3)}.btn-secondary{background:0 0;color:#0b1c4f;border:1px solid #0b1c4f}.btn-secondary:hover{color:#fff}@media (max-width:1600px){.container{max-width:1400px}.article-card,.nav-card{padding:35px}}@media (max-width:1440px){.container{max-width:1200px;padding:30px 20px}h1{font-size:32px}h2{font-size:24px}.article-card,.nav-card{padding:30px}}@media (max-width:990px){.container{max-width:900px;gap:30px}.sidebar{width:28%}.content-area{width:72%}h1{font-size:28px}.lead-text{font-size:16px}.action-buttons{flex-direction:row}}@media (max-width:767px){.container,.meta-info{flex-direction:column}.container{max-width:100%}.content-area,.sidebar{width:100%}.sidebar{order:2;margin-top:30px}.article-card,.nav-card{padding:25px;border-radius:20px}h1{font-size:24px}h2{font-size:22px}h3{font-size:20px}.meta-info{gap:5px;font-size:12px}.action-buttons{flex-direction:column;width:100%}.btn-primary,.btn-secondary{width:100%;text-align:center;box-sizing:border-box}}@media (max-width:490px){.container{padding:15px 10px}.article-card,.nav-card{padding:20px 15px;border-radius:15px}h1{font-size:22px;line-height:1.3}h2{font-size:20px;margin-top:30px}h3{font-size:18px}li,p{font-size:14px;line-height:1.5}.img-block{padding:5px;border-radius:10px}.figcaption{font-size:11px}.back-link,.cta-block p{font-size:13px}.btn-primary,.btn-secondary{padding:10px 20px;font-size:14px}}
  .container2{
    width: 80%;
    margin: 0 auto;
  }
</style>