<template>
  <div class="board-card">
    <div class="card-header">
      <span v-if="date" class="card-date" :class="{ 'is-late': isLate }">
        {{ formatDate(date) }}
      </span>
    </div>
    
    <h4 class="card-title">{{ name }}</h4>
    
    <p v-if="description" class="card-desc">{{ truncate(description) }}</p>
    
    <div class="card-actions">
      <button @click.stop="$emit('delete', id)" class="btn-delete" title="Удалить">Удалить</button>
      <button @click.stop="$emit('edit', cardData)" class="btn-edit" title="Редактировать">Редактировать</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BoardCard',
  props: {
    id: [Number, String],
    name: String,
    description: String,
    date: String,
  },
  emits: ['delete', 'edit'],
  computed: {
    cardData() {
      return { id: this.id, name: this.name, description: this.description, date: this.date };
    },
    isLate() {
      if (!this.date) return false;
      return new Date(this.date) < new Date();
    }
  },
  methods: {
    truncate(text) {
      return text.length > 80 ? text.substring(0, 80) + '...' : text;
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const d = new Date(dateString);
      return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
    }
  }
};
</script>

<style scoped>
.board-card {
  background: #fff;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 10px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  border: 1px solid #E2E8F0;
  cursor: grab;
  transition: transform 0.2s, box-shadow 0.2s;
  
  /* ВАЖНО: Чтобы контент не вылезал */
  overflow: hidden; 
  display: flex;
  flex-direction: column;
}

.board-card:hover {
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  transform: translateY(-2px);
  border-color: #CBD5E1;
}

.board-card:active {
  cursor: grabbing;
}

.card-header {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 8px;
  flex-shrink: 0; /* Заголовок не сжимается */
}

.card-date {
  font-size: 11px;
  font-weight: 600;
  color: #64748B;
  background: #F1F5F9;
  padding: 2px 6px;
  border-radius: 4px;
  white-space: nowrap;
}

.card-date.is-late {
  color: #DC2626;
  background: #FEF2F2;
}

.card-title {
  font-size: 14px;
  font-weight: 600;
  color: #0F172A;
  margin: 0 0 6px 0;
  line-height: 1.4;
  
  /* Обрезка заголовка, если он слишком длинный */
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2; /* Максимум 2 строки */
  -webkit-box-orient: vertical;
  word-break: break-word; /* Перенос длинных слов */
}

.card-desc {
  font-size: 12px;
  color: #475569;
  margin: 0 0 10px 0;
  line-height: 1.4;
  
  /* Обрезка описания */
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3; /* Максимум 3 строки */
  -webkit-box-orient: vertical;
  
  /* Важно для переноса очень длинных слов без пробелов */
  word-wrap: break-word; 
  word-break: break-word;
  min-width: 0; /* Фикс для flex-контейнеров */
}

.card-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  opacity: 0;
  transition: opacity 0.2s;
  margin-top: auto; /* Прижимает кнопки к низу */
  flex-shrink: 0;
}

.board-card:hover .card-actions {
  opacity: 1;
}

.btn-delete, .btn-edit {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 14px;
  padding: 4px;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-delete:hover { background: #FEE2E2; }
.btn-edit:hover { background: #DBEAFE; }
</style>