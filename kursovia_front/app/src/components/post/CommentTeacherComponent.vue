<template>
  <div class="teacher-comments-container">
    
    <!-- Заголовок секции -->
    <div class="section-header">
      <h4>Комментарии студентов</h4>
      <span class="count-badge">{{ response.comments ? response.comments.length : 0 }}</span>
    </div>

    <div class="comments-list">
      <div v-if="visibleComments.length === 0" class="no-comments">
        Студенты еще не оставили комментариев.
      </div>

      <div v-for="comment in visibleComments" :key="comment.id" class="comment-item">
        
        <div class="comment-avatar" :title="comment.FIO">
          {{ getInitials(comment.FIO) }}
        </div>

        <div class="comment-content">
          <div class="comment-header">
            <div class="author-info">
              <h5 class="author-name">{{ comment.FIO }}</h5>
              <span v-if="response.uid === comment.user_id" class="badge-owner">Преподаватель</span>
            </div>
            <span class="comment-time">{{ formatCommentDate(comment.created_at) }}</span>
          </div>

          <p class="comment-text">{{ comment.text }}</p>

          <div class="moderation-panel">
            <button @click="deleteComment(comment.id)" class="action-btn delete" title="Удалить комментарий">
              Удалить
            </button>
          </div>

          <div v-if="editingId === comment.id" class="edit-overlay">
            <textarea 
              v-model="editText" 
              class="edit-input"
              rows="3"
              autofocus
            ></textarea>
            <div class="edit-controls">
              <button @click.prevent="saveEdit(comment.id)" class="btn-save">Сохранить</button>
              <button @click.prevent="cancelEdit" class="btn-cancel">Отмена</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="response.comments && response.comments.length > 2" class="show-more-container">
      <button @click.prevent="toggleComments" class="show-more-btn">
        {{ showAllComments ? 'Свернуть список' : `Показать все (${response.comments.length})` }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    postId: {
      type: Number,
      default: 0
    },
  },
  data() {
    return {
      courseId: this.$route.params.id,
      response: [],
      
      editingId: null, 
      editText: "",
      showAllComments: false 
    };
  },
  computed: {
    visibleComments() {
      if (!this.response || !this.response.comments) return [];
      const all = this.response.comments;
      if (this.showAllComments) {
        return all;
      }
      return all.slice(0, 2);
    }
  },
  async mounted() {
    const headers = new Headers();
    headers.append("Content-Type", "application/json");
    headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

    try {
      const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/comments`, {
        method: "POST",
        headers: headers,
        body: JSON.stringify({ 
          course_id: this.courseId, 
          post_id: this.postId, 
        })
      });
      if (!courseReq.ok) throw new Error("Нет комментариев");
      this.response = await courseReq.json();
    } catch (error) {
      console.error(error);
    } finally {
      this.loading = false;
    }
  },
  methods: {
    getInitials(name) {
      if (!name) return '?';
      const parts = name.split(' ');
      if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
      }
      return name.substring(0, 2).toUpperCase();
    },

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
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
      });
    },

    startEdit(comment) {
      this.editingId = comment.id;
      this.editText = comment.text;
    },

    cancelEdit() {
      this.editingId = null;
      this.editText = "";
    },

    async saveEdit(id) {
      if (!this.editText.trim()) {
        alert("Текст не может быть пустым");
        return;
      }
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const req = await fetch(`${process.env.VUE_APP_API_BASE_URL}/comments/update`, { 
          method: "PATCH",
          headers: headers,
          body: JSON.stringify({ id, text: this.editText, course_id: this.courseId })
        });
        if (!req.ok) throw new Error("Ошибка");
        
        const result = await req.json();
        const index = this.response.comments.findIndex(c => c.id === id);
        if (index !== -1) {
          this.response.comments[index].text = result.comment?.text || this.editText;
        }
        this.cancelEdit(); 
      } catch (error) {
        console.error(error);
        alert("Не удалось сохранить");
      }
    },

    async deleteComment(id){
      if(!confirm("Вы уверены, что хотите удалить этот комментарий? Это действие нельзя отменить.")) return;
      
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/comments/delete`, {
          method: "DELETE",
          headers: headers,
          body: JSON.stringify({ id, course_id: this.courseId })
        });
        if (!courseReq.ok) throw new Error("Ошибка");
        
        const index = this.response.comments.findIndex(c => c.id === id);
        if (index !== -1) {
          this.response.comments.splice(index, 1);
        }
      } catch (error) {
        console.error(error);
        alert("Не удалось удалить");
      }
    },

    toggleComments() {
      this.showAllComments = !this.showAllComments;
    },
  }
};
</script>

<style scoped>
.teacher-comments-container {
  font-family: 'Inter', sans-serif;
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #E2E8F0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #F1F5F9;
}

.section-header h4 {
  margin: 0;
  color: #0B1C4F;
  font-size: 16px;
  font-weight: 700;
}

.count-badge {
  background: #0966F6;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
  min-width: 20px;
  text-align: center;
}

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.no-comments {
  text-align: center;
  color: #94A3B8;
  font-size: 14px;
  padding: 30px 0;
  font-style: italic;
  background: #F8FAFC;
  border-radius: 8px;
}

.comment-item {
  display: flex;
  gap: 12px;
  padding: 15px;
  border-radius: 10px;
  background: #fff;
  border: 1px solid #F1F5F9;
  transition: box-shadow 0.2s;
  position: relative;
}

.comment-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border-color: #CBD5E1;
}

.comment-avatar {
  width: 40px;
  height: 40px;
  background: #E0E7FF;
  color: #4338CA;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 15px;
  flex-shrink: 0;
}

.comment-content {
  flex: 1;
  min-width: 0;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 8px;
  flex-wrap: wrap;
  gap: 8px;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.author-name {
  font-size: 15px;
  color: #0B1C4F;
  margin: 0;
  font-weight: 600;
}

.badge-owner {
  font-size: 10px;
  background: #DCFCE7;
  color: #166534;
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.comment-time {
  font-size: 12px;
  color: #94A3B8;
  white-space: nowrap;
}

.comment-text {
  font-size: 14px;
  color: #334155;
  line-height: 1.5;
  margin: 0 0 12px 0;
  white-space: pre-wrap;
  word-break: break-word;
}

.moderation-panel {
  display: flex;
  gap: 10px;
  padding-top: 10px;
  border-top: 1px dashed #E2E8F0;
}

.action-btn {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
  color: #475569;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}

.action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.action-btn.edit:hover {
  background: #EFF6FF;
  border-color: #0966F6;
  color: #0966F6;
}

.action-btn.delete:hover {
  background: #FEF2F2;
  border-color: #FCA5A5;
  color: #DC2626;
}

.edit-overlay {
  margin-top: 10px;
  background: #FFFBEB;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #FCD34D;
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.edit-input {
  width: 100%;
  border: 1px solid #FCD34D;
  border-radius: 6px;
  padding: 8px;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  background: #fff;
  margin-bottom: 8px;
  box-sizing: border-box;
}

.edit-controls {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn-save, .btn-cancel {
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  border: none;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-save {
  background: #0966F6;
  color: #fff;
}
.btn-save:hover { background: #0759C9; }

.btn-cancel {
  background: #fff;
  color: #64748B;
  border: 1px solid #CBD5E1;
}
.btn-cancel:hover { background: #F1F5F9; }

.show-more-container {
  text-align: center;
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid #F1F5F9;
}

.show-more-btn {
  background: transparent;
  border: none;
  color: #0966F6;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  padding: 8px 16px;
  border-radius: 20px;
  transition: background 0.2s;
}

.show-more-btn:hover {
  background: #EFF6FF;
}

@media (max-width: 480px) {
  .comment-item {
    flex-direction: column;
  }
  .comment-avatar {
    align-self: flex-start;
  }
  .moderation-panel {
    flex-wrap: wrap;
  }
  .action-btn {
    flex: 1;
    justify-content: center;
  }
}
</style>