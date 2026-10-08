import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import NotFound from '../views/NotFoundView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: { 
      title: 'ClassMan — Удобная платформа для управления обучением',
      description: 'Единая среда для преподавателей и студентов. Создавайте курсы, публикуйте задания и проверяйте работы онлайн. Просто, безопасно, эффективно.'
    }
  },
  {
    path: '/register',
    name: 'register',
    component: function () { return import('../views/RegisterView.vue') },
    meta: { 
      title: 'Регистрация в ClassMan — Создать аккаунт преподавателя или студента',
      description: 'Зарегистрируйтесь в системе управления обучением ClassMan. Доступ к курсам, заданиям и оценкам в один клик. Начните учиться или преподавать уже сегодня.'
    }
  },
  {
    path: '/login',
    name: 'login',
    component: function () { return import('../views/LoginView.vue') },
    meta: { 
      title: 'Вход в личный кабинет ClassMan',
      description: 'Авторизация в системе ClassMan. Доступ к вашим курсам, материалам и журналу успеваемости. Введите email и пароль для входа.'
    }
  },
  {
    path: '/courses',
    name: 'courses',
    component: function () { return import('../views/CourseView.vue') },
    meta: { 
      title: 'Мои курсы — Список доступных занятий в ClassMan',
      description: 'Управление вашими учебными курсами. Просмотр активных занятий, присоединение к новым группам по коду и доступ к материалам обучения.'
    }
  },
  // {
  //   path: '/checkCourse/:id',
  //   name: 'checkCourse',
  //   component: function () { return import('../views/CheckCourseView.vue') },
  //   meta: { 
  //     title: 'Страница курса — Задания и материалы | ClassMan',
  //     description: 'Рабочее пространство студента: список заданий, сроки сдачи, загрузка ответов и просмотр оценок. Все материалы курса в одном месте.'
  //   }
  // },
  // {
  //   path: '/checkCourseTeacher/:id',
  //   name: 'checkCourseTeacher',
  //   component: function () { return import('../views/CheckCourseTeacherView.vue') },
  //   meta: { 
  //     title: 'Кабинет преподавателя — Управление курсом | ClassMan',
  //     description: 'Панель управления учебным курсом для преподавателя. Создание заданий, редактирование материалов, мониторинг успеваемости группы.'
  //   }
  // },
  {
    path: '/checkSubmissionsTeacherView/:id/:assignment_id',
    name: 'checkSubmissionsTeacherView',
    component: function () { return import('../views/CheckSubmissionsTeacherView.vue') },
    meta: { 
      title: 'Проверка работ студентов — Оценки и комментарии | ClassMan',
      description: 'Инструмент для проверки сданных студенческих работ. Просмотр файлов, выставление баллов, написание рецензий и обратная связь.'
    }
  },
  {
    path: '/about',
    name: 'about',
    component: function () { return import('../views/AboutView.vue') },
    meta: { 
      title: 'О проекте ClassMan — Наши сотрудники',
      description: 'Узнайте больше о системе ClassMan. Наша миссия, преимущества перед аналогами, технологии безопасности и возможности для современного образования.'
    }
  },
  // {
  //   path: '/feedback',
  //   name: 'Feedback',
  //   component: () => import('../views/FeedbackView.vue'),
  //   meta: { 
  //     title: 'Обратная связь — Поддержка пользователей ClassMan',
  //     description: 'Есть вопросы или предложения? Напишите нам. Служба поддержки ClassMan готова помочь с любыми проблемами при работе с платформой.'
  //   }
  // },
  {
    path: '/blog/lms-dlya-vuzov',
    name: 'SeoArticle',
    component: () => import('../views/SeoArticleView.vue'),
    meta: { 
      title: 'Отечественная LMS для вузов — Аналог Google Classroom | Безопасно',
      description: 'Ищете надежную платформу для проверки заданий? Наша LMS система для колледжей хранит данные в РФ. Автоматический сбор работ, дедлайны, оценки.'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: { 
      title: 'Страница не найдена — Ошибка 404 | ClassMan',
      description: 'Запрашиваемая страница не найдена. Возможно, она была перемещена или удалена. Вернитесь на главную страницу ClassMan.'
    }
  },
  {
    path: '/checkCourseMain/:id',
    name: 'checkCourseMain',
    component: () => import('../views/CheckCourseMainView.vue'),
    meta: { 
      title: 'Страница курса — Задания и материалы | ClassMan',
      description: 'Рабочее пространство студента: список заданий, сроки сдачи, загрузка ответов и просмотр оценок. Все материалы курса в одном месте.'
    }
  },
  {
    path: '/posts/:id',
    name: 'posts',
    component: () => import('../views/PostView.vue'),
    meta: { 
      title: 'Страница постов — Обсуждение | ClassMan',
      description: 'Рабочее пространство студента: список заданий, сроки сдачи, загрузка ответов и просмотр оценок. Все материалы курса в одном месте.'
    }
  },
  {
    path: '/student/board',
    name: 'StudentBoard',
    component: () => import('@/views/StudentBoardView.vue'),
  },
  {
    path: '/my-board',
    name: 'PersonalBoard',
    component: () => import('../views/PersonalBoardView.vue'),
    meta: { 
      title: 'Моя доска',
      requiresAuth: true // Если у тебя есть глобальная проверка авторизации
    }
  },
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.afterEach((to) => {
  document.title = to.meta.title || 'ClassMan';
  
  let metaDescription = document.querySelector('meta[name="description"]');
  if (!metaDescription) {
    metaDescription = document.createElement('meta');
    metaDescription.name = 'description';
    document.head.appendChild(metaDescription);
  }
  metaDescription.content = to.meta.description || 'Описание по умолчанию';
});

export default router
