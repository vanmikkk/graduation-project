<template>
  <Teleport to="body">
    <div v-if="isOpen" class="modal-overlay" @click.self="close">
      <div class="modal-content" :class="`status-${task.status}`">
        
        <!-- Шапка модалки -->
        <div class="modal-header">
          <div class="header-left">
            <span v-if="task.courseName" class="course-badge-large" :style="{ backgroundColor: getCourseColor(task.courseName) }">
              {{ task.courseName }}
            </span>
            <span class="status-badge-large" :class="task.status">
              {{ getStatusLabel(task.status, task.isOverdue) }}
            </span>
          </div>
          <button @click="close" class="close-btn">✕</button>
        </div>

        <!-- Тело модалки -->
        <div class="modal-body">
          <h2 class="task-title-large">{{ task.title || 'Без названия' }}</h2>
          
          <div class="meta-info">
            <div class="meta-item">
              <span class="meta-label">Срок сдачи:</span>
              <span class="meta-value" :class="{ 'text-red': task.isOverdue }">
                {{ task.deadline ? formatDate(task.deadline) : 'Не установлен' }}
              </span>
            </div>
            <div class="meta-item" v-if="task.score !== undefined && task.score !== null">
              <span class="meta-label">Оценка:</span>
              <span class="meta-value score">{{ task.score }}</span>
            </div>
          </div>

          <div class="description-block">
            <h3>Описание задания</h3>
            <p class="description-text">{{ task.description || 'Описание отсутствует.' }}</p>
          </div>

          <!-- Блок действий -->
          <div class="modal-actions">
            <a :href="task.link" class="btn-primary">
              Перейти к заданию →
            </a>
            <button @click="close" class="btn-secondary">Закрыть</button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: 'TaskPreviewModal',
  props: {
    isOpen: Boolean,
    task: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['close'],
  methods: {
    close() {
      this.$emit('close');
    },
    getStatusLabel(status, isOverdue) {
      if (status === 'todo') return isOverdue ? 'ПРОСРОЧЕНО' : 'НУЖНО СДЕЛАТЬ';
      if (status === 'pending') return 'НА ПРОВЕРКЕ';
      if (status === 'done') return 'ВЫПОЛНЕНО';
      return '';
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return dateString;
      return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    },
    getCourseColor(name) {
      if (!name) return '#94A3B8';
      const colors = ['#0966F6', '#16A34A', '#D97706', '#DC2626', '#7C3AED', '#0891b2'];
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      return colors[Math.abs(hash) % colors.length];
    }
  },
  watch: {
    isOpen(val) {
      if (val) {
        document.body.style.overflow = 'hidden'; // Блокируем скролл страницы
      } else {
        document.body.style.overflow = '';
      }
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999999;
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: #fff;
  width: 90%;
  max-width: 600px;
  border-radius: 16px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.2);
  overflow: hidden;
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative;
  border-top: 6px solid transparent;
}

/* Цветные рамки сверху в зависимости от статуса */
.status-todo { border-top-color: #94A3B8; }
.status-todo.is-overdue { border-top-color: #DC2626; }
.status-pending { border-top-color: #D97706; }
.status-done { border-top-color: #16A34A; }

@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.modal-header {
  padding: 20px 25px;
  border-bottom: 1px solid #F1F5F9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #F8FAFC;
}

.header-left {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.course-badge-large {
  font-size: 12px;
  font-weight: 700;
  color: #fff;
  padding: 4px 10px;
  border-radius: 12px;
  text-transform: uppercase;
}

.status-badge-large {
  font-size: 11px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.status-badge-large.todo { background: #E2E8F0; color: #475569; }
.status-badge-large.pending { background: #FEF3C7; color: #D97706; }
.status-badge-large.done { background: #DCFCE7; color: #16A34A; }

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #94A3B8;
  cursor: pointer;
  transition: color 0.2s;
  line-height: 1;
}
.close-btn:hover { color: #334155; }

.modal-body {
  padding: 25px;
}

.task-title-large {
  font-size: 22px;
  font-weight: 700;
  color: #0B1C4F;
  margin: 0 0 20px 0;
  line-height: 1.3;
}

.meta-info {
  display: flex;
  gap: 20px;
  margin-bottom: 25px;
  flex-wrap: wrap;
  background: #F8FAFC;
  padding: 15px;
  border-radius: 12px;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.meta-label {
  font-size: 11px;
  color: #64748B;
  text-transform: uppercase;
  font-weight: 700;
}

.meta-value {
  font-size: 15px;
  font-weight: 600;
  color: #334155;
}

.meta-value.text-red { color: #DC2626; }
.meta-value.score { 
  color: #16A34A; 
  background: #DCFCE7; 
  padding: 2px 8px; 
  border-radius: 6px; 
  display: inline-block;
}

.description-block h3 {
  font-size: 14px;
  color: #475569;
  text-transform: uppercase;
  margin: 0 0 10px 0;
  font-weight: 700;
}

.description-text {
  font-size: 15px;
  line-height: 1.6;
  color: #334155;
  margin: 0;
  white-space: pre-wrap; /* Сохраняет переносы строк */
}

.modal-actions {
  margin-top: 30px;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-primary {
  background: #0966F6;
  color: #fff;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  transition: background 0.2s;
  border: none;
  cursor: pointer;
}
.btn-primary:hover { background: #0759C9; }

.btn-secondary {
  background: #fff;
  color: #475569;
  border: 1px solid #CBD5E1;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-secondary:hover { background: #F1F5F9; border-color: #94A3B8; }

@media (max-width: 600px) {
  .modal-content { width: 95%; max-height: 90vh; overflow-y: auto; }
  .modal-actions { flex-direction: column-reverse; }
  .btn-primary, .btn-secondary { width: 100%; text-align: center; box-sizing: border-box; }
}
</style>