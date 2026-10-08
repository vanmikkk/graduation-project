<template>
  <div>
    <div v-if="loading" class="loader">Загрузка...</div>
    <div v-else>
      <component 
        :is="currentComponent" 
        :course-data="response" 
        :user-id="user_id"
        @update-data="refreshData" 
      />
    </div>
  </div>
</template>

<script>
import TeacherComponent from '@/components/TeacherComponent.vue';
import StudentComponent from '@/components/StudentComponent.vue';

export default {
  name: 'CheckCourseView',
  components: {
    TeacherComponent,
    StudentComponent
  },
  data() {
    return {
      loading: true, 
      currentComponent: null, 
      
      response: {},
      user_id: null,
      courseId: this.$route.params.id
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
        if (courseReq.status === 401) {
          localStorage.removeItem("token"); 
          alert("Неверный токен авторизации, авторизуйтесь заново")
          this.$router.push("/login");
          return;                            
        }
        if (!courseReq.ok) throw new Error("Нет курса");
        this.response = await courseReq.json();

        const userReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/users`, {
          method: "POST",
          headers: headers
        });
        if (!userReq.ok) throw new Error("Нет юзера");
        this.user_id = await userReq.json();

        if (this.user_id != this.response.user_id) {
          this.currentComponent = 'StudentComponent';
        } else {
          this.currentComponent = 'TeacherComponent';
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
    }
  }
};
</script>

<style scoped>
.loader { text-align: center; padding: 50px; font-size: 20px; color: #555; }
</style>