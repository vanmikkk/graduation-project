<template>
  <div class="posts-page">
    <!-- Шапка с поиском -->
    <div class="posts-header">
      <div class="header-content">
        <h1>Лента новостей курса</h1>
        <div class="search-wrapper">
          <label for="date-filter" class="search-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Фильтр по дате:
          </label>
          <input 
            id="date-filter"
            type="date" 
            v-model="search"
            class="date-input"
          >
          <button v-if="search" @click="search = ''" class="reset-btn" title="Сбросить фильтр">
            ✕
          </button>
        </div>
      </div>
    </div>

    <div class="posts-list">
      <div v-if="search && searching.length === 0" class="empty-state">
        <p>Постов за выбранную дату не найдено.</p>
        <button @click="search = ''" class="reset-link">Показать все посты</button>
      </div>

      <div v-for="post in searching" :key="post.id" class="post-card">
        <div class="post-body">
          <p class="post-text">{{ post.text }}</p>
        </div>
        
        <div class="post-footer">
          <div class="post-meta">
            <span class="post-time">
              {{ formatCommentDate(post.created_at) }}
            </span>
            <span v-if="search" class="post-full-date">
              ({{ new Date(post.created_at).toLocaleDateString('ru-RU') }})
            </span>
          </div>
          
          <div class="post-actions">
            <span class="comments-label">Комментарии:</span>
          </div>
        </div>

        <div class="comments-section">
          <CommentComponent :post-id="post.id"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CommentComponent from '@/components/post/CommentComponent.vue';

export default {
  components: {
    CommentComponent
  },
  data() {
    return {
      courseId: this.$route.params.id,
      response: [],
      search: ""
    };
  },
  computed: {
    searching(){
      if(this.search == ""){
        return this.response
      } else {
        return this.response.filter(post => {
          const postDate = post.created_at ? post.created_at.substring(0, 10) : '';
          return postDate === this.search;
        });
      }
    }
  },
  async mounted() {
    const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/posts`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });
        if (!courseReq.ok) throw new Error("Нет постов");
        this.response = await courseReq.json();

      } catch (error) {
        console.error(error);
        alert("Ошибка доступа");
        this.$router.push('/courses');
      } finally {
        this.loading = false;
      }
  },
  methods: {
    formatCommentDate(dateString) {
      if (!dateString) return '';
  
      const date = new Date(dateString);
      date.setHours(date.getHours() + 5);
      const now = new Date();
      const diffMs = now - date; 
      
      const diffSeconds = Math.floor(diffMs / 1000);
      const diffMinutes = Math.floor(diffSeconds / 60);
      const diffHours = Math.floor(diffMinutes / 60);
      const diffDays = Math.floor(diffHours / 24);

      if (diffSeconds < 10) return 'Только что';
      if (diffMinutes < 1) return `${diffSeconds} сек. назад`;
      if (diffMinutes < 60) return `${diffMinutes} мин. назад`;
      if (diffHours < 24) return `${diffHours} ч. назад`;
      if (diffDays < 3) return `${diffDays} дн. назад`;

      return date.toLocaleDateString('ru-RU', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
      });
    },
  }
};
</script>

<style scoped>
.posts-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Inter', sans-serif;
}

.posts-header {
  margin-bottom: 30px;
  background: #fff;
  padding: 20px;
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

h1 {
  color: #0B1C4F;
  font-size: 28px;
  font-weight: 700;
  margin: 0;
}

.search-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F6F8FB;
  padding: 8px 15px;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  transition: all 0.2s;
}

.search-wrapper:focus-within {
  border-color: #0966F6;
  box-shadow: 0 0 0 3px rgba(9, 102, 246, 0.1);
}

.search-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #555;
  font-weight: 500;
  cursor: pointer;
}

.date-input {
  border: none;
  background: transparent;
  font-size: 14px;
  color: #0B1C4F;
  outline: none;
  font-family: inherit;
  cursor: pointer;
}

.date-input::-webkit-calendar-picker-indicator {
  cursor: pointer;
  opacity: 0.6;
  transition: opacity 0.2s;
}
.date-input::-webkit-calendar-picker-indicator:hover {
  opacity: 1;
}

.reset-btn {
  background: #FFE4E6;
  color: #E11D48;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.2s;
}

.reset-btn:hover {
  background: #FECDD3;
  transform: scale(1.1);
}

.posts-list {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.post-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  border: 1px solid #F1F5F9;
}

.post-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.post-body {
  padding: 25px;
}

.post-text {
  font-size: 16px;
  line-height: 1.6;
  color: #334155;
  margin: 0;
  white-space: pre-wrap; 
}

.post-footer {
  padding: 15px 25px;
  background: #F8FAFC;
  border-top: 1px solid #F1F5F9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.post-meta {
  display: flex;
  align-items: center;
  gap: 10px;
}

.post-time {
  font-size: 13px;
  color: #64748B;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 5px;
}

.post-full-date {
  font-size: 12px;
  color: #94A3B8;
}

.comments-label {
  font-size: 13px;
  color: #0966F6;
  font-weight: 600;
  background: #EFF6FF;
  padding: 6px 12px;
  border-radius: 20px;
}

.comments-section {
  padding: 20px 25px;
  background: #fff;
  border-top: 1px dashed #E2E8F0;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #F8FAFC;
  border-radius: 20px;
  color: #64748B;
}

.empty-state p {
  font-size: 18px;
  margin-bottom: 15px;
}

.reset-link {
  background: transparent;
  border: 2px solid #0966F6;
  color: #0966F6;
  padding: 10px 20px;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.reset-link:hover {
  background: #0966F6;
  color: #fff;
}

@media (max-width: 600px) {
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .search-wrapper {
    width: 100%;
    justify-content: space-between;
  }
  
  .post-footer {
    flex-direction: column;
    align-items: flex-start;
  }
  
  h1 {
    font-size: 24px;
  }
}
</style>