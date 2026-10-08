<template>
  <main class="personal-board-page" @click="closeContextMenu">
    <div class="container">
      
      <div class="board-header">
        <h1>Моя доска</h1>
        <button @click="loadBoard" class="btn-refresh" :disabled="loading">
          Обновить
        </button>
      </div>

      <div v-if="loading" class="loader">Загрузка доски...</div>

      <div v-else class="board-canvas">
        <!-- Передаем событие contextmenu в компонент колонки -->
        <BoardColumn
          v-for="column in columns"
          :key="column.id"
          :column-id="column.id"
          :name="column.name"
          :cards="column.cards"
          @add-card="openAddModal"
          @delete-card="deleteCard"
          @edit-card="openEditModal"
          @drag-end="handleDragEnd"
          @contextmenu.native.prevent="openColumnMenu($event, column)"
          style="cursor: context-menu;" 
        />
        
        <div class="add-column-placeholder">
          <button @click="openAddColumnModal" class="btn-add-column">
            + Новая колонка
          </button>
        </div>
      </div>
    </div>

    <!-- КОНТЕКСТНОЕ МЕНЮ ДЛЯ КОЛОНКИ -->
    <div 
      v-if="contextMenu.visible" 
      class="context-menu" 
      :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
    >
      <div class="context-item" @click="openRenameModal">Переименовать</div>
      <div class="context-item danger" @click="openDeleteConfirmModal">Удалить колонку</div>
    </div>

    <!-- Модалка: Создание/Редактирование задачи -->
    <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal-window">
        <h3>{{ isEditing ? 'Редактировать задачу' : 'Новая задача' }}</h3>
        <input v-model="form.name" placeholder="Название задачи" class="form-input" />
        <textarea v-model="form.description" placeholder="Описание" class="form-textarea"></textarea>
        <input type="date" v-model="form.date" class="form-input" />
        <div class="modal-actions">
          <button @click="saveCard" class="btn-save">Сохранить</button>
          <button @click="closeModal" class="btn-cancel">Отмена</button>
        </div>
      </div>
    </div>

    <!-- Модалка: Создание колонки -->
    <div v-if="isColumnModalOpen" class="modal-overlay" @click.self="closeColumnModal">
      <div class="modal-window">
        <h3>Новая колонка</h3>
        <input 
          v-model="newColumnName" 
          placeholder="Название (например: Идеи)" 
          class="form-input" 
          @keyup.enter="createColumn"
          ref="columnNameInput"
        />
        <div class="modal-actions">
          <button @click="createColumn" class="btn-save">Создать</button>
          <button @click="closeColumnModal" class="btn-cancel">Отмена</button>
        </div>
      </div>
    </div>

    <!-- Модалка: Переименование колонки -->
    <div v-if="isRenameModalOpen" class="modal-overlay" @click.self="closeRenameModal">
      <div class="modal-window">
        <h3>Переименовать колонку</h3>
        <input 
          v-model="renameColumnName" 
          placeholder="Новое название" 
          class="form-input" 
          @keyup.enter="renameColumn"
          ref="renameInput"
        />
        <div class="modal-actions">
          <button @click="renameColumn" class="btn-save">Сохранить</button>
          <button @click="closeRenameModal" class="btn-cancel">Отмена</button>
        </div>
      </div>
    </div>

    <!-- Модалка: Подтверждение удаления колонки -->
    <div v-if="isDeleteConfirmModalOpen" class="modal-overlay" @click.self="closeDeleteConfirmModal">
      <div class="modal-window modal-danger">
        <h3 class="text-danger">Удаление колонки</h3>
        <p>Вы уверены? Все задачи внутри этой колонки будут безвозвратно удалены.</p>
        <div class="modal-actions">
          <button @click="confirmDeleteColumn" class="btn-delete">Удалить</button>
          <button @click="closeDeleteConfirmModal" class="btn-cancel">Отмена</button>
        </div>
      </div>
    </div>

    <!-- Всплывающее уведомление (Toast) -->
    <transition name="fade-slide">
      <div v-if="toast.visible" class="custom-toast" :class="toast.type">
        <span class="toast-text">{{ toast.message }}</span>
      </div>
    </transition>
  </main>
</template>

<script>
import BoardColumn from '@/components/board/user/BoardColumn.vue';

export default {
  name: 'PersonalBoardView',
  components: { BoardColumn },
  data() {
    return {
      loading: false,
      boardId: null,
      columns: [],
      
      // Задачи
      isModalOpen: false,
      isEditing: false,
      form: { id: null, columnId: null, name: '', description: '', date: '' },
      
      // Колонки
      isColumnModalOpen: false,
      newColumnName: '',
      
      // Контекстное меню
      contextMenu: {
        visible: false,
        x: 0,
        y: 0,
        targetColumnId: null
      },
      
      // Переименование
      isRenameModalOpen: false,
      renameColumnName: '',
      
      // Удаление
      isDeleteConfirmModalOpen: false,
      
      // Уведомления
      toast: {
        visible: false,
        message: '',
        type: 'success' // 'success' или 'error'
      }
    };
  },
  async mounted() {
    await this.loadBoard();
    // Глобальный слушатель для закрытия меню при клике в любое место
    document.addEventListener('click', this.closeContextMenu);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.closeContextMenu);
  },
  methods: {
    showToast(message, type = 'success') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.visible = true;
      setTimeout(() => {
        this.toast.visible = false;
      }, 3000);
    },

    closeContextMenu() {
      this.contextMenu.visible = false;
    },

    openColumnMenu(event, column) {
      event.preventDefault();
      event.stopPropagation(); // Чтобы не сработал глобальный клик сразу
      
      this.contextMenu.targetColumnId = column.id;
      this.contextMenu.x = event.clientX;
      this.contextMenu.y = event.clientY;
      this.contextMenu.visible = true;
    },

    openRenameModal() {
      this.closeContextMenu();
      const col = this.columns.find(c => c.id === this.contextMenu.targetColumnId);
      if (!col) return;
      
      this.renameColumnName = col.name;
      this.isRenameModalOpen = true;
      
      this.$nextTick(() => {
        if(this.$refs.renameInput) this.$refs.renameInput.focus();
      });
    },

    closeRenameModal() {
      this.isRenameModalOpen = false;
      this.renameColumnName = '';
    },

    async renameColumn() {
      if (!this.renameColumnName.trim()) {
        this.showToast("Введите название колонки", "error");
        return;
      }

      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      headers.append("Content-Type", "application/json");

      try {
        const res = await fetch(`${process.env.VUE_APP_API_BASE_URL}/personal/column/${this.contextMenu.targetColumnId}`, {
          method: "PUT",
          headers,
          body: JSON.stringify({ name: this.renameColumnName })
        });

        if (!res.ok) throw new Error("Ошибка переименования");

        await this.loadBoard();
        this.closeRenameModal();
        this.showToast("Колонка переименована", "success");

      } catch (e) {
        this.showToast(e.message, "error");
      }
    },

    openDeleteConfirmModal() {
      this.closeContextMenu();
      this.isDeleteConfirmModalOpen = true;
    },

    closeDeleteConfirmModal() {
      this.isDeleteConfirmModalOpen = false;
    },

    async confirmDeleteColumn() {
      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const res = await fetch(`${process.env.VUE_APP_API_BASE_URL}/personal/column/${this.contextMenu.targetColumnId}`, {
          method: "DELETE",
          headers
        });

        if (!res.ok) throw new Error("Ошибка удаления");

        await this.loadBoard();
        this.closeDeleteConfirmModal();
        this.showToast("Колонка удалена", "success");

      } catch (e) {
        this.showToast(e.message, "error");
      }
    },

    // ... остальные ваши методы (openAddColumnModal, createColumn, loadBoard и т.д.) ...
    openAddColumnModal() {
      this.newColumnName = '';
      this.isColumnModalOpen = true;
      this.$nextTick(() => {
        if(this.$refs.columnNameInput) this.$refs.columnNameInput.focus();
      });
    },
    closeColumnModal() {
      this.isColumnModalOpen = false;
      this.newColumnName = '';
    },
    async createColumn() {
      if (!this.newColumnName.trim()) {
        this.showToast("Введите название колонки", "error");
        return;
      }
      if (!this.boardId) {
        this.showToast("Доска не загружена", "error");
        return;
      }

      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      headers.append("Content-Type", "application/json");

      try {
        const res = await fetch(`${process.env.VUE_APP_API_BASE_URL}/personal/column`, {
          method: "POST",
          headers,
          body: JSON.stringify({ board_id: this.boardId, name: this.newColumnName })
        });

        if (!res.ok) {
          const err = await res.json();
          throw new Error(err.message || "Ошибка создания");
        }

        await this.loadBoard();
        this.closeColumnModal();
        this.showToast("Колонка создана", "success");

      } catch (e) {
        this.showToast(e.message, "error");
      }
    },
    async loadBoard() {
      this.loading = true;
      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      headers.append("Content-Type", "application/json");

      try {
        const res = await fetch(`${process.env.VUE_APP_API_BASE_URL}/personal/board`, {
          method: "POST",
          headers,
          body: JSON.stringify({})
        });
        if (res.status === 401) {
          localStorage.removeItem("token"); 
          alert("Неверный токен авторизации, авторизуйтесь заново")
          this.$router.push("/login");
          return;                            
        }
        if (!res.ok) throw new Error("Ошибка загрузки");
        
        const data = await res.json();
        this.boardId = data.board_id;
        this.columns = data.columns;
      } catch (e) {
        console.error(e);
        this.showToast("Не удалось загрузить доску", "error");
      } finally {
        this.loading = false;
      }
    },
    async handleDragEnd() {
      const payload = { type: 'cards', items: [] };
      this.columns.forEach((col, colIndex) => {
        col.cards.forEach((card, cardIndex) => {
          payload.items.push({ id: card.id, column_id: col.id, sort: cardIndex });
        });
      });

      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      headers.append("Content-Type", "application/json");

      try {
        await fetch(`${process.env.VUE_APP_API_BASE_URL}/personal/update-order`, {
          method: "POST",
          headers,
          body: JSON.stringify(payload)
        });
      } catch (e) {
        console.error("Ошибка сохранения порядка", e);
        this.loadBoard(); 
      }
    },
    openAddModal(columnId) {
      this.isEditing = false;
      this.form = { id: null, columnId, name: '', description: '', date: '' };
      this.isModalOpen = true;
    },
    openEditModal(card) {
      this.isEditing = true;
      this.form = { id: card.id, columnId: card.column_id, name: card.name, description: card.description, date: card.date };
      this.isModalOpen = true;
    },
    closeModal() { this.isModalOpen = false; },
    async saveCard() {
      if (!this.form.name) {
        this.showToast("Введите название задачи", "error");
        return;
      }
      if (!this.form.description) {
        this.showToast("Введите описание задачи", "error");
        return;
      }
      if (!this.form.date) {
        this.showToast("Введите дату выполнения задачи", "error");
        return;
      }

      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      headers.append("Content-Type", "application/json");

      try {
        let url, method;
        if (this.isEditing) {
          url = `${process.env.VUE_APP_API_BASE_URL}/personal/card/${this.form.id}`;
          method = 'PUT';
        } else {
          url = `${process.env.VUE_APP_API_BASE_URL}/personal/card`;
          method = 'POST';
        }

        const res = await fetch(url, {
          method,
          headers,
          body: JSON.stringify({ column_id: this.form.columnId, name: this.form.name, description: this.form.description, date: this.form.date })
        });

        if (!res.ok) {
          const errData = await res.json().catch(() => ({}));
          throw new Error(errData.message || "Ошибка сохранения");
        }

        await this.loadBoard();
        this.closeModal();
        this.showToast(this.isEditing ? "Задача обновлена" : "Задача создана", "success");

      } catch (e) {
        this.showToast(e.message, "error");
      }
    },
    async deleteCard(id) {
      // Здесь можно тоже заменить alert на подтверждение, но оставим пока так или заменим на toast
      if (!confirm("Удалить задачу?")) return;
      
      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const res = await fetch(`${process.env.VUE_APP_API_BASE_URL}/personal/card/${id}`, {
          method: "DELETE",
          headers
        });
        if (!res.ok) throw new Error("Ошибка удаления");
        await this.loadBoard();
        this.showToast("Задача удалена", "success");
      } catch (e) {
        this.showToast(e.message, "error");
      }
    }
  }
};
</script>

<style scoped>
/* Ваши старые стили... */
.personal-board-page { font-family: 'Inter', sans-serif; background: #F8FAFC; min-height: 100vh; padding: 20px; position: relative; }
.container { max-width: 100%; overflow-x: auto; }
.board-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.board-header h1 { font-size: 28px; color: #0F172A; margin: 0; }
.btn-refresh { background: #fff; border: 1px solid #CBD5E1; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }
.board-canvas { display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; padding-bottom: 20px; }
.add-column-placeholder { min-width: 300px; }
.btn-add-column { width: 100%; padding: 12px; background: #E2E8F0; border: none; border-radius: 12px; color: #475569; font-weight: 600; cursor: pointer; transition: .1s; }
.btn-add-column:hover { border-color: #94A3B8; color: #334155; background: #E2E8F0; scale: 1.03; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-window { background: #fff; padding: 25px; border-radius: 12px; width: 400px; max-width: 90%; }
input { outline: none; }
.form-input, .form-textarea { width: 100%; margin-bottom: 12px; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; }
.form-textarea { height: 100px; resize: vertical; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; }
.btn-save { background: #0966F6; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
.btn-cancel { background: #fff; border: 1px solid #CBD5E1; padding: 8px 16px; border-radius: 6px; cursor: pointer; }

/* Стили для контекстного меню */
.context-menu {
  position: fixed;
  background: #fff;
  border: 1px solid #E2E8F0;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  border-radius: 8px;
  padding: 6px 0;
  z-index: 2000;
  min-width: 180px;
  animation: fadeIn 0.1s ease-out;
}

.context-item {
  padding: 10px 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: #334155;
  transition: background 0.1s;
}

.context-item:hover {
  background: #F1F5F9;
}

.context-item.danger {
  color: #DC2626;
}

.context-item.danger:hover {
  background: #FEF2F2;
}

.icon { font-size: 16px; }

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Стили для модального окна удаления */
.modal-danger h3 { color: #DC2626; margin-top: 0; }
.modal-danger p {
  margin-bottom: 5px;
}
.text-danger { color: #DC2626; }
.hint { font-size: 12px; color: #64748B; font-style: italic; margin-top: -10px; margin-bottom: 20px; }
.btn-delete { background: #DC2626; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
.btn-delete:hover { background: #B91C1C; }

/* Toast уведомления */
.custom-toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  border-radius: 50px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 3000;
  font-weight: 600;
  font-size: 14px;
  pointer-events: none;
  white-space: nowrap;
}
.custom-toast.success { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.custom-toast.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.toast-icon { font-size: 16px; }
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from { opacity: 0; transform: translateX(-50%) translateY(20px); }
.fade-slide-leave-to { opacity: 0; transform: translateX(-50%) translateY(10px); }

@media (max-width: 750px) {
  .board-canvas { justify-content: center; }
}
</style>