<template>
  <div class="task-card" 
       :class="[ { 'is-overdue': is_overdue && status === 'todo' }, `status-${status}` ]"
       @click="$emit('preview', { title, description, deadline, courseName, status, is_overdue, link, score: null })">
    
    <!-- Цветной индикатор статуса слева -->
    <div class="status-indicator"></div>

    <div class="card-header">
      <span v-if="course_name" class="course-badge" :style="{ backgroundColor: courseColor }">
        {{ course_name }}
      </span>
      <span v-else class="course-badge" style="background:#94A3B8">Курс</span>
      
      <!-- Бейдж статуса текстом (вместо эмодзи) -->
      <span class="status-label" :class="status">
        {{ statusLabel }}
      </span>
    </div>

    <h4 class="task-title">{{ title || 'Без названия' }}</h4>
    
    <p v-if="description" class="task-desc">{{ truncate(description) }}</p>

    <div class="card-footer">
      <div class="deadline-info">
        <span class="deadline-label">Срок:</span>
        <span class="deadline-value" :class="{ 'text-red': is_overdue }">
          <span v-if="deadline">{{ formatDate(deadline) }}</span>
          <span v-else class="no-deadline">Нет срока</span>
        </span>
      </div>
      
      <a :href="link || '#'" class="go-btn">
        Открыть задание
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskCard',
  props: {
    title: String,
    description: String,
    deadline: String,
    course_name: String,
    course_img: [Number, String], 
    status: String,
    is_overdue: Boolean,
    link: String
  },
  computed: {
    statusLabel() {
      if (this.status === 'todo') return this.is_overdue ? 'Просрочено' : 'В работе';
      if (this.status === 'pending') return 'На проверке';
      if (this.status === 'done') return 'Выполнено';
      return '';
    },
    courseColor() {
      if (!this.course_name) return '#94A3B8';
      const colors = ['#0966F6', '#16A34A', '#D97706', '#DC2626', '#7C3AED', '#0891b2'];
      let hash = 0;
      for (let i = 0; i < this.course_name.length; i++) {
        hash = this.course_name.charCodeAt(i) + ((hash << 5) - hash);
      }
      return colors[Math.abs(hash) % colors.length];
    }
  },
  methods: {
    truncate(text) {
      if (!text) return '';
      return text.length > 60 ? text.substring(0, 60) + '...' : text;
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return dateString;
      return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
    }
  }
};
</script>

<style scoped>
.task-card {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  border: 1px solid #E2E8F0;
  transition: all 0.2s;
  cursor: pointer;
  position: relative;
  overflow: visible; /* ✅ ВАЖНО: убираем hidden, чтобы контент не обрезался */
  display: flex;
  flex-direction: column;
  height: auto; /* Явная высота auto */
  min-height: fit-content;
}

.status-indicator {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
}

.status-todo .status-indicator { background: #94A3B8; }
.status-todo.is-overdue .status-indicator { background: #DC2626; }
.status-pending .status-indicator { background: #D97706; }
.status-done .status-indicator { background: #16A34A; }

.task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(0,0,0,0.08);
  border-color: #CBD5E1;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  padding-left: 8px;
  
  /* ✅ МАГИЯ ОБРЕЗАНИЯ ТЕКСТА ВО FLEXBOX */
  min-width: 0; 
  gap: 8px;
}

.course-badge {
  font-size: 10px; /* Уменьшили шрифт для компактности */
  font-weight: 700;
  color: #fff;
  padding: 3px 6px;
  border-radius: 4px;
  text-transform: uppercase;
  
  /* Жесткое ограничение ширины бейджа */
  max-width: 70%;
  flex-shrink: 1; /* Разрешаем сжиматься */
  min-width: 0;   /* Разрешаем обрезку троеточием */
  
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  letter-spacing: 0.3px;
  line-height: 1.2;
}

.status-label {
  font-size: 9px;
  font-weight: 700;
  padding: 3px 6px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  white-space: nowrap;
  flex-shrink: 0; /* Статус НИКОГДА не сжимается */
}

.status-label.todo { background: #F1F5F9; color: #475569; }
.status-label.pending { background: #FFFBEB; color: #D97706; border: 1px solid #FEF3C7; }
.status-label.done { background: #F0FDF4; color: #16A34A; border: 1px solid #DCFCE7; }

.task-title {
  font-size: 15px;
  font-weight: 600;
  color: #0B1C4F;
  margin: 0 0 8px 0;
  line-height: 1.4;
  padding-left: 8px;
  
  /* ✅ ИСПРАВЛЕНИЕ ВЫСОТЫ */
  display: block; /* Убираем flex-box clamp */
  -webkit-line-clamp: unset; /* Сбрасываем ограничение в 2 строки */
  overflow: visible; /* Разрешаем показывать весь текст */
  word-break: break-word; /* Переносим длинные слова */
  min-height: auto; /* Высота подстраивается под контент */
}

/* Также убедитесь, что описание не мешает, если оно есть */
.task-desc {
  font-size: 13px;
  color: #64748B;
  margin: 0 0 12px 0;
  line-height: 1.4;
  padding-left: 8px;
  
  /* Если описание тоже обрезалось, можно оставить clamp или убрать его */
  display: -webkit-box;
  -webkit-line-clamp: 3; /* Увеличили до 3 строк для описания */
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-footer {
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid #F1F5F9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-left: 8px;
  min-width: 0;
  gap: 10px;
}

.deadline-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0; /* Позволяет дате сжиматься при необходимости */
}

.deadline-label {
  font-size: 10px;
  color: #94A3B8;
  text-transform: uppercase;
  font-weight: 600;
}

.deadline-value {
  font-size: 12px;
  font-weight: 600;
  color: #334155;
  white-space: nowrap;
}

.text-red { color: #DC2626 !important; }

.go-btn {
  font-size: 12px;
  font-weight: 600;
  color: #0966F6;
  text-decoration: none;
  background: #EFF6FF;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.2s;
  white-space: nowrap;
  flex-shrink: 0; /* Кнопка всегда целая */
}

.go-btn:hover {
  background: #DBEAFE;
  color: #1e40af;
}
</style>