<template>
  <div class="comments-container">
    <div class="add-comment-box">
      <div class="input-wrapper">
        <textarea 
          v-model="newComment" 
          placeholder="Напишите комментарий..." 
          class="comment-input"
          rows="2"
        ></textarea>
        <button 
          @click="addComment" 
          :disabled="!newComment.trim()" 
          class="send-btn"
          title="Отправить"
        >
          ➤
        </button>
      </div>
    </div>

    <div class="comments-list">
      <div v-if="visibleComments.length === 0" class="no-comments">
        Пока нет комментариев. Будьте первым!
      </div>

      <div v-for="comment in visibleComments" :key="comment.id" class="comment-item">
        
        <div class="comment-avatar">
          {{ getInitials(comment.FIO) }}
        </div>

        <div class="comment-content">
          <div class="comment-header">
            <h5 class="author-name">
              {{ response.uid === comment.user_id ? "Вы" : comment.FIO }}
              <span v-if="response.uid === comment.user_id" class="badge-author">(автор)</span>
            </h5>
            <span class="comment-time">{{ formatCommentDate(comment.created_at) }}</span>
          </div>

          <div v-if="editingId !== comment.id" class="comment-text-block">
            <p class="comment-text">{{ comment.text }}</p>
            
            <div v-if="response.uid === comment.user_id" class="comment-actions">
              <button @click.prevent="startEdit(comment)" class="action-btn edit" title="Редактировать">
                Изменить
              </button>
              <button @click="deleteComment(comment.id)" class="action-btn delete" title="Удалить">
                Удалить
              </button>
            </div>
          </div>

          <div v-else class="edit-mode">
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
        {{ showAllComments ? 'Свернуть' : `Показать еще (${response.comments.length - 2})` }}
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
      newComment: "",
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

    async addComment(){
      if (!this.newComment.trim()) return;
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/comments/create`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ 
            text: this.newComment, 
            post_id: this.postId, 
            course_id: this.courseId, 
          })
        });
        if (!courseReq.ok) throw new Error("Ошибка");
        let result = await courseReq.json();
        let newCom = result.comment;
        this.response.comments.unshift(newCom);
        this.newComment = "";
        if (!this.showAllComments && this.response.comments.length > 2) {
           // Можно оставить свернутым или раскрыть: this.showAllComments = true;
        }
      } catch (error) {
        console.error(error);
        alert("Не удалось отправить комментарий");
      }
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
      if(!confirm("Удалить комментарий?")) return;
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
.comments-container {
  margin-top: 20px;
  font-family: 'Inter', sans-serif;
}

.add-comment-box {
  margin-bottom: 25px;
  background: #F8FAFC;
  padding: 15px;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
}

.input-wrapper {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.comment-input {
  flex: 1;
  border: 1px solid #CBD5E1;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  min-height: 40px;
  background: #fff;
  transition: border-color 0.2s;
  outline: none;
}

.comment-input:focus {
  border-color: #0966F6;
  box-shadow: 0 0 0 2px rgba(9, 102, 246, 0.1);
}

.send-btn {
  background: #0966F6;
  color: #fff;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 18px;
  transition: all 0.2s;
  flex-shrink: 0;
}

.send-btn:hover:not(:disabled) {
  background: #0759C9;
  transform: translateY(-1px);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.hint {
  font-size: 11px;
  color: #94A3B8;
  margin-top: 6px;
  text-align: right;
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
  padding: 20px;
  font-style: italic;
}

.comment-item {
  display: flex;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid #F1F5F9;
}

.comment-item:last-child {
  border-bottom: none;
}

.comment-avatar {
  width: 36px;
  height: 36px;
  background: #E0E7FF;
  color: #4338CA;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}

.comment-content {
  flex: 1;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 6px;
  flex-wrap: wrap;
  gap: 8px;
}

.author-name {
  font-size: 14px;
  color: #0B1C4F;
  margin: 0;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
}

.badge-author {
  font-size: 10px;
  background: #EFF6FF;
  color: #0966F6;
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 600;
  text-transform: uppercase;
}

.comment-time {
  font-size: 12px;
  color: #94A3B8;
}

.comment-text-block {
  position: relative;
}

.comment-text {
  font-size: 14px;
  color: #334155;
  line-height: 1.5;
  margin: 0 0 8px 0;
  white-space: pre-wrap;
  word-break: break-word;
}

.comment-actions {
  display: flex;
  gap: 8px;
  opacity: 0; 
  transition: opacity 0.2s;
}

.comment-item:hover .comment-actions {
  opacity: 1;
}

.action-btn {
  background: transparent;
  border: none;
  font-size: 12px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background 0.2s;
  color: #64748B;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.action-btn:hover {
  background: #F1F5F9;
}

.action-btn.edit:hover {
  color: #0966F6;
  background: #EFF6FF;
}

.action-btn.delete:hover {
  color: #EF4444;
  background: #FEF2F2;
}

.edit-mode {
  background: #FFFBEB;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #FCD34D;
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
  padding-top: 10px;
  border-top: 1px dashed #E2E8F0;
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
  .input-wrapper {
    flex-direction: column;
  }
  .send-btn {
    width: 100%;
    height: 40px;
  }
  .comment-actions {
    opacity: 1;
    justify-content: flex-end;
  }
}
</style>