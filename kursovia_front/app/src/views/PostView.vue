<template>
  <div>
    <div v-if="loading" class="loader-page">Загрузка данных...</div>
    <div v-else>
      <main class="posts-page">
        <div class="container">
          <div class="main-layout">
            
            <!-- ЛЕВАЯ КОЛОНКА (24%) - СТИЛЬ STUDENT COURSE VIEW -->
            <aside class="sidebar">
              <div class="sidebar-content">
                <h1 class="page-title">Посты курса</h1>
                
                <div class="course-card-mini" v-if="response.name">
                  <div class="mini-cover">
                    <img :src="courseImages[response.img_id] || courseImages[1]" alt="Обложка">
                  </div>
                  <h3 class="course-name">{{ response.name }}</h3>
                </div>

                <div class="sidebar-actions">
                  <router-link :to="'/checkCourseMain/' + response.id" class="btn-sidebar">
                    Задания курса
                  </router-link>
                  <router-link to="/courses" class="btn-sidebar-outline">
                    ← Назад к списку
                  </router-link>
                </div>
              </div>
            </aside>

            <section class="content-area">
              <component 
                :is="currentComponent" 
                :course-data="response" 
                :user-id="user_id"
                @update-data="refreshData" 
              />
            </section>

          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import PostComponent from '@/components/post/PostComponent.vue';
import PostTeacherComponent from '@/components/post/PostTeacherComponent.vue';

export default {
  name: 'PostView',
  components: {
    PostComponent,
    PostTeacherComponent
  },
  data() {
    return {
      loading: true,
      courseImages: {
        1: require('@/assets/course_img1.png'), 
        2: require('@/assets/course_img2.png'),
        3: require('@/assets/course_img3.png'),
        4: require('@/assets/course_img4.png'),
        5: require('@/assets/course_img5.png'),
        6: require('@/assets/course_img6.png'),
        7: require('@/assets/course_img7.png'),
        8: require('@/assets/course_img8.png'),
        9: require('@/assets/course_img9.png'),
        10: require('@/assets/course_img10.png'),
      },
      response: {}, // Изменил с [] на {}, так как курс это объект
      user_id: null,
      courseId: this.$route.params.id,
      currentComponent: null,
    };
  },
  async mounted() {
    await this.checkRoleAndLoad();
  },
  methods: {
    async checkRoleAndLoad() {
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/course`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });
        if (!courseReq.ok) throw new Error("Нет курса");
        this.response = await courseReq.json();

        const userReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/users`, {
          method: "POST",
          headers: headers
        });
        if (!userReq.ok) throw new Error("Нет юзера");
        this.user_id = await userReq.json();

        if (this.user_id != this.response.user_id) {
          this.currentComponent = 'PostComponent';
        } else {
          this.currentComponent = 'PostTeacherComponent';
        }

      } catch (error) {
        console.error(error);
        alert("Ошибка доступа");
        this.$router.push('/courses');
      } finally {
        this.loading = false;
      }
    },
    
    refreshData() {
      console.log("Данные обновлены дочерним компонентом");
      // Можно добавить логику обновления, если нужно
    }
  }
};
</script>

<style scoped>
/* Общие стили страницы */
.posts-page {
  font-family: 'Inter', sans-serif;
  background: #F8FAFC;
  min-height: 100vh;
  padding: 40px 0;
}

.loader-page {
  text-align: center;
  padding: 100px;
  font-size: 20px;
  color: #64748B;
  font-weight: 500;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* СЕТКА 24% / 74% */
.main-layout {
  display: flex;
  gap: 2%;
}

/* ЛЕВАЯ КОЛОНКА (24%) - SIDEBAR */
.sidebar {
  width: 24%;
  flex-shrink: 0;
}

.sidebar-content {
  background: #fff;
  padding: 25px;
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  position: sticky;
  width: 100%;
  top: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.page-title {
  font-size: 24px;
  color: #0B1C4F;
  margin: 0;
  font-weight: 700;
}

/* Карточка курса */
.course-card-mini {
  background: #F8FAFC;
  border-radius: 15px;
  overflow: hidden;
  border: 1px solid #E2E8F0;
}

.mini-cover {
  width: 100%;
  padding-top: 60%; /* Соотношение сторон 16:10 */
  position: relative;
  background: #E2E8F0;
}

.mini-cover img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.course-name {
  font-size: 18px;
  color: #0B1C4F;
  margin: 15px;
  font-weight: 600;
  line-height: 1.4;
}

/* Кнопки навигации */
.sidebar-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-sidebar, .btn-sidebar-outline {
  display: block;
  padding: 12px;
  border-radius: 10px;
  text-align: center;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s ease;
}

.btn-sidebar {
  background: #EFF6FF;
  color: #0966F6;
}

.btn-sidebar:hover {
  background: #DBEAFE;
  transform: translateY(-1px);
}

.btn-sidebar-outline {
  background: transparent;
  border: 1px solid #CBD5E1;
  color: #64748B;
}

.btn-sidebar-outline:hover {
  border-color: #94A3B8;
  color: #334155;
  background: #F8FAFC;
}

/* ПРАВАЯ КОЛОНКА (74%) */
.content-area {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Адаптивность для мобильных */
@media (max-width: 768px) {
  .main-layout {
    flex-direction: column;
  }
  .sidebar, .content-area {
    width: 100%;
  }
  .sidebar-content {
    position: static;
    margin-bottom: 20px;
  }
}
</style>