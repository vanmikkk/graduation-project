<template>
  <main class="courses-page">
    <div class="container">
      
      <div class="page-header">
        <h1>Мои курсы</h1>
        <p class="subtitle">Управляйте своими учебными материалами и прогрессом</p>
      </div>

      <div class="tabs-container">
        <button 
          :class="['tab-btn', { active: activeTab === 'subscribed' }]" 
          @click="switchTab('subscribed')"
        >
          Подписки
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'owned' }]" 
          @click="switchTab('owned')"
        >
          Мои курсы
        </button>
      </div>

      <div class="courses-grid">
        
        <div v-if="response.length === 0" class="empty-state">
          <div class="empty-icon">
            {{ activeTab === 'subscribed' ? '🎓' : '📝' }}
          </div>
          <h3>
            {{ activeTab === 'subscribed' 
              ? 'Вы пока не подписаны ни на один курс' 
              : 'У вас еще нет созданных курсов' }}
          </h3>
          <p>
            {{ activeTab === 'subscribed' 
              ? 'Используйте код приглашения, чтобы присоединиться к курсу преподавателя.' 
              : 'Создайте свой первый курс, чтобы начать добавлять задания и студентов.' }}
          </p>
        </div>

        <router-link 
          :to="`/checkCourseMain/${course.id}`" 
          class="course-card" 
          v-for="course in response" 
          :key="course.id"
        >
          <div class="card-image-wrapper">
            <img :src="courseImages[course.img_id] || courseImages[1]" alt="Обложка курса" class="card-image">
            <div class="card-overlay">
              <span class="open-hint">Открыть курс →</span>
            </div>
          </div>
          
          <div class="card-content">
            <h3 class="course-title">{{ course.name }}</h3>
            <p class="course-meta">
              <span v-if="activeTab === 'owned'" class="badge-owner">Автор</span>
              <span v-else class="badge-student">Студент</span>
            </p>
          </div>
        </router-link>

      </div>
    </div>
  </main>
</template>

<script>
export default {
  data() {
    return {
      response: [],
      activeTab: 'subscribed', 
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
    };
  },
  async mounted() {
    await this.loadSubscribedCourses();
  },
  methods: {
    async switchTab(tab) {
      this.activeTab = tab;
      if (tab === 'subscribed') {
        await this.loadSubscribedCourses();
      } else {
        await this.loadOwnedCourses();
      }
    },
    async loadSubscribedCourses() {
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      
      try {
        const request = await fetch(`${process.env.VUE_APP_API_BASE_URL}/coursebyuser`, {
          method: "GET",
          headers: headers
        });
        if (request.status === 401) {
          localStorage.removeItem("token"); 
          alert("Неверный токен авторизации, авторизуйтесь заново")
          this.$router.push("/login");
          return;                            
        }

        if (!request.ok) throw new Error("Ошибка загрузки");
        this.response = await request.json();
      } catch (error) {
        console.error(error);
        alert("Не удалось загрузить курсы");
      }
    },
    async loadOwnedCourses() {
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      
      try {
        const request = await fetch(`${process.env.VUE_APP_API_BASE_URL}/courses/owner`, {
          method: "GET",
          headers: headers,
        });
        if (request.status === 401) {
          localStorage.removeItem("token"); 
          alert("Неверный токен авторизации, авторизуйтесь заново")
          this.$router.push("/login");
          return;                            
        }
        if (!request.ok) throw new Error("Ошибка загрузки");
        this.response = await request.json();
      } catch (error) {
        console.error(error);
        alert("Не удалось загрузить курсы");
      }
    }
  }
};
</script>

<style scoped>
.courses-page {
  font-family: 'Inter', sans-serif;
  background: #F8FAFC;
  min-height: 100vh;
  padding-bottom: 50px;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 36px;
  font-weight: 800;
  color: #0B1C4F;
  margin: 0 0 10px 0;
  letter-spacing: -0.5px;
}

.subtitle {
  font-size: 16px;
  color: #64748B;
  margin: 0;
}

.tabs-container {
  display: flex;
  gap: 10px;
  margin-bottom: 40px;
  background: #fff;
  padding: 6px;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.04);
  width: fit-content;
}

.tab-btn {
  border: none;
  background: transparent;
  padding: 12px 24px;
  font-size: 15px;
  font-weight: 600;
  color: #64748B;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.tab-btn:hover {
  color: #0B1C4F;
  background: #F1F5F9;
}

.tab-btn.active {
  background: #0966F6;
  color: #fff;
  box-shadow: 0 4px 12px rgba(9, 102, 246, 0.25);
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 30px;
}

.course-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  border: 1px solid #F1F5F9;
}

.course-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.1);
}

.card-image-wrapper {
  position: relative;
  width: 100%;
  padding-top: 60%; 
  overflow: hidden;
  background: #E2E8F0;
}

.card-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.course-card:hover .card-image {
  transform: scale(1.05);
}

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(11, 28, 79, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.course-card:hover .card-overlay {
  opacity: 1;
}

.open-hint {
  color: #fff;
  font-weight: 700;
  font-size: 16px;
  transform: translateY(10px);
  transition: transform 0.3s ease;
}

.course-card:hover .open-hint {
  transform: translateY(0);
}

.card-content {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.course-title {
  font-size: 18px;
  font-weight: 700;
  color: #0B1C4F;
  margin: 0 0 15px 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-meta {
  margin-top: auto;
}

.badge-owner, .badge-student {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 4px 10px;
  border-radius: 20px;
  display: inline-block;
}

.badge-owner {
  background: #EFF6FF;
  color: #0966F6;
}

.badge-student {
  background: #F0FDF4;
  color: #16A34A;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 80px 20px;
  background: #fff;
  border-radius: 20px;
  border: 2px dashed #CBD5E1;
  color: #64748B;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
  opacity: 0.6;
}

.empty-state h3 {
  font-size: 22px;
  color: #334155;
  margin: 0 0 10px 0;
}

.empty-state p {
  font-size: 15px;
  margin: 0 0 30px 0;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

.btn-primary {
  display: inline-block;
  background: #0966F6;
  color: #fff;
  padding: 12px 30px;
  border-radius: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(9, 102, 246, 0.2);
}

.btn-primary:hover {
  background: #0759C9;
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(9, 102, 246, 0.3);
}

@media (max-width: 768px) {
  .page-header h1 {
    font-size: 28px;
  }
  
  .tabs-container {
    width: 100%;
    justify-content: center;
  }
  
  .tab-btn {
    flex: 1;
    justify-content: center;
    padding: 10px 15px;
    font-size: 14px;
  }
  
  .courses-grid {
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
  }
}

@media (max-width: 480px) {
  .courses-grid {
    grid-template-columns: 1fr;
  }
}
</style>