<template>
  <div class="board-column">
    <div class="column-header">
      <h3>{{ name }}</h3>
      <span class="count-badge">{{ cards.length }}</span>
    </div>

    <!-- Область для Drag-and-Drop -->
    <draggable 
      class="column-body"
      :list="cards" 
      group="tasks" 
      item-key="id"
      ghost-class="ghost-card"
      drag-class="dragging-card"
      @change="onDragChange"
    >
      <template #item="{ element }">
        <BoardCard 
          :id="element.id"
          :name="element.name"
          :description="element.description"
          :date="element.date"
          @delete="$emit('delete-card', element.id)"
          @edit="$emit('edit-card', element)"
        />
      </template>
      <template #footer>
        <button @click="$emit('add-card', columnId)" class="btn-add-card">
          + Добавить задачу
        </button>
      </template>
    </draggable>
  </div>
</template>

<script>
import draggable from 'vuedraggable';
import BoardCard from './BoardCard.vue';

export default {
  name: 'BoardColumn',
  components: { draggable, BoardCard },
  props: {
    columnId: [Number, String],
    name: String,
    cards: Array
  },
  emits: ['add-card', 'delete-card', 'edit-card', 'drag-end'],
  methods: {
    onDragChange(evt) {
      // Событие срабатывает при изменении списка (добавление, удаление, перемещение)
      // Мы просто сообщаем родителю, что произошло изменение, чтобы он отправил запрос на бэк
      this.$emit('drag-end');
    }
  }
};
</script>

<style scoped>
.board-column {
  background: #F1F5F9;
  border-radius: 12px;
  width: 300px;
  min-width: 300px;
  max-height: calc(100vh - 150px);
  display: flex;
  flex-direction: column;
}

.column-header {
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 700;
  color: #334155;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.count-badge {
  background: #CBD5E1;
  color: #475569;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 10px;
  font-weight: 700;
}

.column-body {
  padding: 0 12px 12px 12px;
  overflow-y: auto;
  flex: 1;
  min-height: 50px;
}

/* Стили для состояний Drag-and-Drop */
.ghost-card {
  opacity: 0.5;
  background: #E2E8F0;
  border: 2px dashed #94A3B8;
}

.dragging-card {
  opacity: 0.8;
  transform: rotate(3deg);
  box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.btn-add-card {
  width: 100%;
  background: transparent;
  border: 2px dashed #CBD5E1;
  color: #64748B;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 8px;
  transition: all 0.2s;
}

.btn-add-card:hover {
  border-color: #94A3B8;
  color: #334155;
  background: #E2E8F0;
}

/* Скроллбар */
.column-body::-webkit-scrollbar { width: 6px; }
.column-body::-webkit-scrollbar-track { background: transparent; }
.column-body::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }
</style>