<template>
  <div class="teacher-posts-page">
    
    <!-- Заголовок страницы -->
    <div class="page-header">
      <h1>Управление постами</h1>
      <p class="subtitle">Создавайте объявления и новости для вашего курса</p>
    </div>

    <!-- Форма создания нового поста -->
    <div class="create-post-card">
      <div class="card-header">
        <span class="icon-plus">+</span>
        <h2>Новый пост</h2>
      </div>
      <div class="card-body">
        <textarea 
          v-model="newPost" 
          placeholder="О чем хотите сообщить студентам?..." 
          class="post-input"
          rows="3"
        ></textarea>
        <div class="card-footer">
          <span class="hint">Нажмите кнопку, чтобы опубликовать</span>
          <button @click="addPost" :disabled="!newPost.trim()" class="btn-primary">
            Опубликовать пост
          </button>
        </div>
      </div>
    </div>

    <div class="posts-list">
      <div v-if="response.length === 0" class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>Пока нет ни одного поста</h3>
        <p>Используйте форму выше, чтобы создать первое объявление.</p>
      </div>

      <div v-for="post in response" :key="post.id" class="post-card">
        
        <div v-if="editingId !== post.id" class="post-content">
          <p class="post-text">{{ post.text }}</p>
          
          <div class="post-actions">
            <div class="action-buttons">
              <button @click.prevent="startEdit(post)" class="btn-icon btn-edit" title="Редактировать">
                <img src="../../assets/icon-edit.png" alt="✏️">
                <span>Изменить</span>
              </button>
              <button @click="deletePost(post.id)" class="btn-icon btn-delete" title="Удалить">
                <img src="../../assets/recycle-bin.png" alt="🗑️">
                <span>Удалить</span>
              </button>
            </div>
          </div>
        </div>
        <div v-else class="edit-mode">
          <div class="edit-header">
            <span class="badge">Редактирование</span>
          </div>
          <textarea 
            v-model="editText" 
            class="post-input edit-input"
            rows="4"
            autofocus
          ></textarea>
          <div class="edit-actions">
            <button @click.prevent="saveEdit(post.id)" class="btn-primary btn-sm">
              <img src="../../assets/icon-check.png" alt="✓" style="width: 16px; height: 16px; margin-right: 5px;">
              Сохранить
            </button>
            <button @click.prevent="cancelEdit" class="btn-secondary btn-sm">
              <img src="../../assets/icon-cancel.png" alt="✕" style="width: 16px; height: 16px; margin-right: 5px;">
              Отмена
            </button>
          </div>
        </div>

        <div class="comments-divider">
          <span>Комментарии к посту</span>
        </div>
        <div class="comments-area">
          <CommentTeacherComponent :post-id="post.id"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CommentTeacherComponent from '@/components/post/CommentTeacherComponent.vue';

export default {
  components: {
    CommentTeacherComponent
  },
  data() {
    return {
      courseId: this.$route.params.id,
      response: [],

      editingId: null, 
      editText: "",

      newPost: ""
    };
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
    async addPost(){
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/posts/create`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ 
            text: this.newPost, 
            course_id: this.courseId, 
        })
        });
        if (!courseReq.ok) throw new Error("Нет комментариев");

        const headers2 = new Headers();
        headers2.append("Content-Type", "application/json");
        headers2.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

        try {
          const courseReq2 = await fetch(`${process.env.VUE_APP_API_BASE_URL}/posts`, {
            method: "POST",
            headers: headers2,
            body: JSON.stringify({ course_id: this.courseId })
          });
          if (!courseReq2.ok) throw new Error("Нет постов");
          this.response = await courseReq2.json();

        } catch (error) {
          console.error(error);
          alert("Ошибка доступа");
          this.$router.push('/courses');
        } finally {
          this.loading = false;
        }


        // let result = await courseReq.json()
        // let newPost = result.post
        // this.response.unshift(newPost);
        // this.newPost = ""
      } catch (error) {
        console.error(error);
        alert("Ошибка доступа");
      } finally {
        this.loading = false;
      }
    },

    startEdit(post) {
      this.editingId = post.id;
      this.editText = post.text;
    },

    cancelEdit() {
      this.editingId = null;
      this.editText = "";
    },

    async saveEdit(id) {
      if (!this.editText.trim()) {
        alert("Комментарий не может быть пустым");
        return;
      }

      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const req = await fetch(`${process.env.VUE_APP_API_BASE_URL}/posts/update`, { 
          method: "PATCH",
          headers: headers,
          body: JSON.stringify({ 
            id: id,
            text: this.editText,
            course_id: this.courseId
          })
        });

        if (!req.ok) throw new Error("Ошибка обновления");
        
        const result = await req.json();
        const index = this.response.findIndex(c => c.id === id);
        if (index !== -1) {
          this.response[index].text = result.post?.text || this.editText;
        }

        this.cancelEdit(); 

      } catch (error) {
        console.error(error);
        alert("Не удалось сохранить изменения");
      }
    },


    async deletePost(id){
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/posts/delete`, {
          method: "DELETE",
          headers: headers,
          body: JSON.stringify({ 
            id: id,
            course_id: this.courseId, 
        })
        });
        if (!courseReq.ok) throw new Error("Нет комментариев");
        const index = this.response.findIndex(c => c.id === id);
    
        if (index !== -1) {
          this.response.splice(index, 1);
        }
      } catch (error) {
        console.error(error);
        alert("Ошибка доступа");
      } finally {
        this.loading = false;
      }
    },
  }
};
</script>

<style scoped>
.teacher-posts-page {
  width: 100%;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Inter', sans-serif;
  color: #334155;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  color: #0B1C4F;
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.subtitle {
  color: #64748B;
  font-size: 16px;
  margin: 0;
}

.create-post-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(14, 108, 247, 0.08);
  border: 1px solid #E0E7FF;
  margin-bottom: 40px;
  overflow: hidden;
}

.card-header {
  background: #EFF6FF;
  padding: 15px 25px;
  display: flex;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid #E0E7FF;
}

.icon-plus {
  width: 32px;
  height: 32px;
  background: #0966F6;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 300;
  line-height: 1;
}

.card-header h2 {
  font-size: 18px;
  color: #0B1C4F;
  margin: 0;
  font-weight: 600;
}

.card-body {
  padding: 25px;
}

.post-input {
  width: 100%;
  border: 1px solid #CBD5E1;
  border-radius: 12px;
  padding: 15px;
  font-size: 15px;
  font-family: inherit;
  resize: vertical;
  min-height: 80px;
  background: #F8FAFC;
  transition: all 0.2s;
  box-sizing: border-box;
  outline: none;
}

.post-input:focus {
  border-color: #0966F6;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(9, 102, 246, 0.1);
}

.edit-input {
  min-height: 100px;
  background: #FFFBEB;
  border-color: #FCD34D;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 15px;
}

.hint {
  font-size: 13px;
  color: #94A3B8;
  font-style: italic;
}

.btn-primary {
  background: #0966F6;
  color: #fff;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
}

.btn-primary:hover:not(:disabled) {
  background: #0759C9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(9, 102, 246, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: #fff;
  color: #64748B;
  border: 1px solid #CBD5E1;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
}

.btn-secondary:hover {
  background: #F1F5F9;
  border-color: #94A3B8;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 14px;
}

.posts-list {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.post-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  border: 1px solid #F1F5F9;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.post-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.post-content {
  padding: 25px;
}

.post-text {
  font-size: 16px;
  line-height: 1.6;
  color: #334155;
  margin: 0 0 20px 0;
  white-space: pre-wrap;
}

.post-actions {
  display: flex;
  justify-content: flex-end;
  border-top: 1px solid #F1F5F9;
  padding-top: 15px;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

.btn-icon {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  padding: 8px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
  color: #475569;
}

.btn-icon img {
  width: 18px;
  height: 18px;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.btn-icon:hover {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.btn-icon:hover img {
  opacity: 1;
}

.btn-edit:hover {
  border-color: #0966F6;
  color: #0966F6;
}

.btn-delete:hover {
  border-color: #EF4444;
  color: #EF4444;
  background: #FEF2F2;
}

.edit-mode {
  padding: 25px;
  background: #FFFBEB;
}

.edit-header {
  margin-bottom: 15px;
}

.badge {
  background: #FCD34D;
  color: #78350F;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.edit-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
  justify-content: flex-end;
}

.comments-divider {
  background: #F8FAFC;
  padding: 10px 25px;
  font-size: 13px;
  color: #64748B;
  font-weight: 600;
  border-top: 1px dashed #E2E8F0;
  border-bottom: 1px dashed #E2E8F0;
}

.comments-area {
  padding: 20px 25px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #F8FAFC;
  border-radius: 20px;
  border: 2px dashed #CBD5E1;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.5;
}

.empty-state h3 {
  color: #475569;
  margin: 0 0 10px 0;
  font-size: 20px;
}

.empty-state p {
  color: #94A3B8;
  margin: 0;
  font-size: 15px;
}

@media (max-width: 600px) {
  .card-footer {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
  
  .action-buttons {
    width: 100%;
    justify-content: space-between;
  }
  
  .btn-icon {
    flex: 1;
    justify-content: center;
  }
  
  .btn-icon span {
    display: none; 
  }
}
</style>